<?php

declare(strict_types=1);

namespace App\Domain\Infrastructure\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Organization\Models\Organization;
use App\Support\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use BelongsToOrganization;

    protected $table = 'activity_logs';

    protected $fillable = [
        'organization_id',
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related entity.
     */
    public function entity(): ?Model
    {
        if (!$this->entity_type || !$this->entity_id) {
            return null;
        }

        return $this->entity_type::find($this->entity_id);
    }

    public function getChangesAttribute(): array
    {
        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];

        return array_diff_assoc($new, $old);
    }

    public function scopeForEntity($query, Model $entity)
    {
        return $query
            ->where('entity_type', get_class($entity))
            ->where('entity_id', $entity->id);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public static function log(
        string $action,
        ?Model $subject = null,
        array $oldValues = [],
        array $newValues = []
    ): self {
        $user = auth()->user();

        return self::create([
            'organization_id' => $user?->organization_id,
            'user_id' => $user?->id,
            'entity_type' => $subject ? get_class($subject) : null,
            'entity_id' => $subject?->id,
            'action' => $action,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public static function logCreate(Model $model): self
    {
        return self::log('created', $model, [], $model->toArray());
    }

    public static function logUpdate(Model $model, array $oldValues): self
    {
        return self::log('updated', $model, $oldValues, $model->toArray());
    }

    public static function logDelete(Model $model): self
    {
        return self::log('deleted', $model, $model->toArray(), []);
    }
}
