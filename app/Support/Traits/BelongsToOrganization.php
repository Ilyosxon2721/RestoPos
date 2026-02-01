<?php

namespace App\Support\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToOrganization
{
    /**
     * Boot the trait.
     */
    protected static function bootBelongsToOrganization(): void
    {
        static::creating(function ($model) {
            if (empty($model->organization_id) && auth()->check()) {
                $model->organization_id = auth()->user()->organization_id;
            }
        });

        // Применяем глобальный scope только если пользователь авторизован
        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check() && !app()->runningInConsole()) {
                $builder->where($builder->getModel()->getTable() . '.organization_id', auth()->user()->organization_id);
            }
        });
    }

    /**
     * Get the organization that owns the model.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Organization\Models\Organization::class);
    }

    /**
     * Scope a query to a specific organization.
     */
    public function scopeForOrganization(Builder $query, int $organizationId): Builder
    {
        return $query->withoutGlobalScope('organization')
            ->where($this->getTable() . '.organization_id', $organizationId);
    }

    /**
     * Scope a query without organization filter.
     */
    public function scopeWithoutOrganizationScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('organization');
    }
}
