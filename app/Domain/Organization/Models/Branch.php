<?php

namespace App\Domain\Organization\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'address',
        'city',
        'phone',
        'email',
        'timezone',
        'currency_code',
        'working_hours',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'working_hours' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the organization that owns the branch.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the halls for the branch.
     */
    public function halls(): HasMany
    {
        return $this->hasMany(\App\Domain\Floor\Models\Hall::class);
    }

    /**
     * Get the employees for the branch.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(\App\Domain\Staff\Models\Employee::class);
    }

    /**
     * Get the warehouses for the branch.
     */
    public function warehouses(): HasMany
    {
        return $this->hasMany(\App\Domain\Warehouse\Models\Warehouse::class);
    }

    /**
     * Get the workshops for the branch.
     */
    public function workshops(): HasMany
    {
        return $this->hasMany(\App\Domain\Menu\Models\Workshop::class);
    }

    /**
     * Check if branch is currently open.
     */
    public function isOpen(): bool
    {
        if (empty($this->working_hours)) {
            return true;
        }

        $now = now()->setTimezone($this->timezone);
        $dayOfWeek = strtolower($now->format('D')); // mon, tue, etc.

        if (!isset($this->working_hours[$dayOfWeek])) {
            return false;
        }

        $hours = $this->working_hours[$dayOfWeek];
        if (empty($hours) || count($hours) < 2) {
            return false;
        }

        $openTime = $now->copy()->setTimeFromTimeString($hours[0]);
        $closeTime = $now->copy()->setTimeFromTimeString($hours[1]);

        // Handle overnight hours (close time is next day)
        if ($closeTime->lessThan($openTime)) {
            $closeTime->addDay();
        }

        return $now->between($openTime, $closeTime);
    }

    /**
     * Get a setting value.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        // First check branch settings, then fall back to organization settings
        $value = data_get($this->settings, $key);

        if ($value === null) {
            return $this->organization->getSetting($key, $default);
        }

        return $value;
    }

    protected static function newFactory(): \Database\Factories\BranchFactory
    {
        return \Database\Factories\BranchFactory::new();
    }
}
