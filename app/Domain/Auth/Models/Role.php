<?php

namespace App\Domain\Auth\Models;

use App\Support\Traits\BelongsToOrganization;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use BelongsToOrganization, HasUuid;

    protected $fillable = [
        'organization_id',
        'name',
        'slug',
        'description',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * Get users with this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot('branch_id');
    }

    /**
     * Get permissions for this role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /**
     * Check if role has a specific permission.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }

    /**
     * Assign permission to role.
     */
    public function givePermission(Permission|string $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        $this->permissions()->syncWithoutDetaching($permission);
    }

    /**
     * Remove permission from role.
     */
    public function revokePermission(Permission|string $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        $this->permissions()->detach($permission);
    }

    /**
     * Sync permissions for role.
     */
    public function syncPermissions(array $permissions): void
    {
        $permissionIds = collect($permissions)->map(function ($permission) {
            if ($permission instanceof Permission) {
                return $permission->id;
            }

            return Permission::where('slug', $permission)->firstOrFail()->id;
        });

        $this->permissions()->sync($permissionIds);
    }

    /**
     * Scope for system roles.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope for custom roles.
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }
}
