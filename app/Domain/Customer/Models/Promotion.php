<?php

declare(strict_types=1);

namespace App\Domain\Customer\Models;

use App\Support\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'type',
        'discount_type',
        'discount_value',
        'conditions',
        'min_order_amount',
        'max_discount_amount',
        'applicable_to',
        'applicable_ids',
        'start_date',
        'end_date',
        'active_days',
        'active_hours_from',
        'active_hours_to',
        'usage_limit',
        'usage_count',
        'usage_limit_per_customer',
        'promo_code',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'conditions' => 'array',
        'applicable_ids' => 'array',
        'active_days' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'usage_limit_per_customer' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Проверяет, активна ли акция в данный момент.
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }
}
