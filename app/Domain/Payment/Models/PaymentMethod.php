<?php

declare(strict_types=1);

namespace App\Domain\Payment\Models;

use App\Support\Enums\PaymentMethodType;
use App\Support\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'name',
        'type',
        'is_fiscal',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'type' => PaymentMethodType::class,
        'is_fiscal' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
