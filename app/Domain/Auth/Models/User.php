<?php

namespace App\Domain\Auth\Models;

use App\Domain\Organization\Models\Organization;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuid, Notifiable, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'email',
        'phone',
        'password',
        'pin_code',
        'first_name',
        'last_name',
        'avatar',
        'locale',
        'email_verified_at',
        'two_factor_secret',
        'two_factor_enabled',
        'last_login_at',
        'last_login_ip',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'pin_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the organization that owns the user.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the roles for the user (without organization scope, since system roles have null organization_id).
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withoutGlobalScope('organization')
            ->withPivot('branch_id');
    }

    /**
     * Get the employee profile for the user.
     */
    public function employee(): HasOne
    {
        return $this->hasOne(\App\Domain\Staff\Models\Employee::class);
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get the user's initials.
     */
    public function getInitialsAttribute(): string
    {
        $first = mb_substr($this->first_name, 0, 1);
        $last = $this->last_name ? mb_substr($this->last_name, 0, 1) : '';

        return mb_strtoupper($first.$last);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleSlug, ?int $branchId = null): bool
    {
        $query = $this->roles()->where('slug', $roleSlug);

        if ($branchId !== null) {
            $query->where(function ($q) use ($branchId) {
                $q->whereNull('user_roles.branch_id')
                    ->orWhere('user_roles.branch_id', $branchId);
            });
        }

        return $query->exists();
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permissionSlug, ?int $branchId = null): bool
    {
        $rolesQuery = $this->roles();

        if ($branchId !== null) {
            $rolesQuery->where(function ($q) use ($branchId) {
                $q->whereNull('user_roles.branch_id')
                    ->orWhere('user_roles.branch_id', $branchId);
            });
        }

        $roleIds = $rolesQuery->pluck('roles.id');

        return Permission::whereHas('roles', function ($q) use ($roleIds) {
            $q->whereIn('roles.id', $roleIds);
        })->where('slug', $permissionSlug)->exists();
    }

    /**
     * Get all permissions for the user.
     */
    public function getAllPermissions(?int $branchId = null): array
    {
        $rolesQuery = $this->roles();

        if ($branchId !== null) {
            $rolesQuery->where(function ($q) use ($branchId) {
                $q->whereNull('user_roles.branch_id')
                    ->orWhere('user_roles.branch_id', $branchId);
            });
        }

        $roleIds = $rolesQuery->pluck('roles.id');

        return Permission::whereHas('roles', function ($q) use ($roleIds) {
            $q->whereIn('roles.id', $roleIds);
        })->pluck('slug')->unique()->values()->toArray();
    }

    /**
     * Check if user can access a specific branch.
     */
    public function canAccessBranch(int $branchId): bool
    {
        // Check if user has any role with null branch_id (access to all branches)
        $hasAllAccess = $this->roles()
            ->wherePivotNull('branch_id')
            ->exists();

        if ($hasAllAccess) {
            return true;
        }

        // Check if user has a role for this specific branch
        return $this->roles()
            ->wherePivot('branch_id', $branchId)
            ->exists();
    }

    /**
     * Get accessible branch IDs for the user.
     */
    public function getAccessibleBranchIds(): array
    {
        // Check if user has any role with null branch_id (access to all branches)
        $hasAllAccess = $this->roles()
            ->wherePivotNull('branch_id')
            ->exists();

        if ($hasAllAccess) {
            return $this->organization->branches()->pluck('id')->toArray();
        }

        return $this->roles()
            ->wherePivotNotNull('branch_id')
            ->pluck('user_roles.branch_id')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Update last login info.
     */
    public function updateLastLogin(?string $ip = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }
}
