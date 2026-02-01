<?php

namespace App\Support\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToBranch
{
    /**
     * Get the branch that owns the model.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Organization\Models\Branch::class);
    }

    /**
     * Scope a query to a specific branch.
     */
    public function scopeForBranch(Builder $query, int $branchId): Builder
    {
        return $query->where($this->getTable() . '.branch_id', $branchId);
    }

    /**
     * Scope a query to branches accessible by the current user.
     */
    public function scopeAccessibleBranches(Builder $query): Builder
    {
        if (!auth()->check()) {
            return $query;
        }

        $user = auth()->user();

        // Если у пользователя есть доступ ко всем филиалам (роль без branch_id)
        $hasAllBranchesAccess = $user->roles()
            ->wherePivotNull('branch_id')
            ->exists();

        if ($hasAllBranchesAccess) {
            return $query;
        }

        // Иначе фильтруем по конкретным филиалам
        $branchIds = $user->roles()
            ->wherePivotNotNull('branch_id')
            ->pluck('user_roles.branch_id')
            ->unique();

        return $query->whereIn($this->getTable() . '.branch_id', $branchIds);
    }
}
