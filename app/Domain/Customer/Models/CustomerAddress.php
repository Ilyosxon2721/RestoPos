<?php

declare(strict_types=1);

namespace App\Domain\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    protected $fillable = [
        'customer_id',
        'label',
        'address',
        'apartment',
        'entrance',
        'floor',
        'intercom',
        'comment',
        'latitude',
        'longitude',
        'is_default',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_default' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Полный адрес с деталями.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = [$this->address];

        if ($this->apartment) {
            $parts[] = "кв. {$this->apartment}";
        }
        if ($this->entrance) {
            $parts[] = "подъезд {$this->entrance}";
        }
        if ($this->floor) {
            $parts[] = "этаж {$this->floor}";
        }

        return implode(', ', $parts);
    }
}
