<?php

namespace App\Domain\Warehouse\Models;

use App\Support\Traits\HasUuid;
use App\Support\Traits\BelongsToOrganization;
use App\Support\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasUuid, BelongsToOrganization, BelongsToBranch;

    protected $fillable = [
        'organization_id',
        'branch_id',
        'name',
        'type',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function stock(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
