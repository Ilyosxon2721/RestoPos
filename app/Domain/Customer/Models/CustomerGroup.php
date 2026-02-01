<?php

namespace App\Domain\Customer\Models;

use App\Support\Traits\HasUuid;
use App\Support\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerGroup extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'name',
        'discount_percent',
        'bonus_percent',
        'min_spent',
        'color',
        'is_default',
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'bonus_percent' => 'decimal:2',
        'min_spent' => 'decimal:2',
        'is_default' => 'boolean',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
