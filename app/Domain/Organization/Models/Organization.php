<?php

namespace App\Domain\Organization\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'name',
        'legal_name',
        'inn',
        'logo',
        'subdomain',
        'subscription_plan',
        'subscription_expires_at',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'subscription_expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the branches for the organization.
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * Get the users for the organization.
     */
    public function users(): HasMany
    {
        return $this->hasMany(\App\Domain\Auth\Models\User::class);
    }

    /**
     * Check if subscription is active.
     */
    public function hasActiveSubscription(): bool
    {
        if ($this->subscription_plan === 'trial') {
            return $this->subscription_expires_at === null || $this->subscription_expires_at->isFuture();
        }

        return $this->subscription_expires_at && $this->subscription_expires_at->isFuture();
    }

    /**
     * Get a setting value.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Set a setting value.
     */
    public function setSetting(string $key, mixed $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
        $this->save();
    }

    protected static function newFactory(): \Database\Factories\OrganizationFactory
    {
        return \Database\Factories\OrganizationFactory::new();
    }

}
