<?php

declare(strict_types=1);

namespace App\Domain\Customer\Models;

use App\Support\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerGroup extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'name',
        'discount_percent',
        'bonus_earn_percent',
        'min_spent_to_join',
        'color',
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'bonus_earn_percent' => 'decimal:2',
        'min_spent_to_join' => 'decimal:2',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
