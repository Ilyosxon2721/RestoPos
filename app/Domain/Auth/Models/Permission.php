<?php

declare(strict_types=1);

namespace App\Domain\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'module',
        'description',
    ];

    /**
     * Get roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    /**
     * Scope by permission module.
     */
    public function scopeInModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Get all permission modules.
     */
    public static function getModules(): array
    {
        return static::distinct('module')->pluck('module')->toArray();
    }

    /**
     * Get permissions grouped by their module.
     */
    public static function getAllGrouped(): array
    {
        return static::all()->groupBy('module')->toArray();
    }
}
