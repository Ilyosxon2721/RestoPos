<?php

namespace App\Domain\Menu\Models;

use App\Support\Traits\HasUuid;
use App\Support\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'name',
        'short_name',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Scope for default unit.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
