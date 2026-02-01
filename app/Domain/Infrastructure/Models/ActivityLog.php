<?php

namespace App\Domain\Infrastructure\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Organization\Models\Organization;
use App\Support\Traits\BelongsToOrganization;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $table = 'activity_logs';

    protected $fillable = [
        'organization_id',
        'user_id',
        'subject_type',
        'subject_id',
        'action',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function getChangesAttribute(): array
    {
        return $this->properties['changes'] ?? [];
    }

    public function getOldValuesAttribute(): array
    {
        return $this->properties['old'] ?? [];
    }

    public function getNewValuesAttribute(): array
    {
        return $this->properties['new'] ?? [];
    }

    public function scopeForSubject($query, Model $subject)
    {
        return $query
            ->where('subject_type', get_class($subject))
            ->where('subject_id', $subject->id);
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
        Model $subject = null,
        string $description = null,
        array $properties = []
    ): self {
        $user = auth()->user();

        return self::create([
            'organization_id' => $user?->organization_id,
            'user_id' => $user?->id,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'action' => $action,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public static function logCreate(Model $model, string $description = null): self
    {
        return self::log('created', $model, $description, [
            'new' => $model->toArray(),
        ]);
    }

    public static function logUpdate(Model $model, array $oldValues, string $description = null): self
    {
        return self::log('updated', $model, $description, [
            'old' => $oldValues,
            'new' => $model->toArray(),
            'changes' => $model->getChanges(),
        ]);
    }

    public static function logDelete(Model $model, string $description = null): self
    {
        return self::log('deleted', $model, $description, [
            'old' => $model->toArray(),
        ]);
    }
}
