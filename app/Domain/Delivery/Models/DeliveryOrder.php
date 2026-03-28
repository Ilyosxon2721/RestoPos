<?php

declare(strict_types=1);

namespace App\Domain\Delivery\Models;

use App\Domain\Order\Models\Order;
use App\Support\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryOrder extends Model
{
    protected $fillable = [
        'order_id',
        'courier_id',
        'delivery_zone_id',
        'address',
        'address_details',
        'latitude',
        'longitude',
        'contact_name',
        'contact_phone',
        'delivery_fee',
        'scheduled_at',
        'estimated_delivery_at',
        'status',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
        'delivery_notes',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => DeliveryStatus::class,
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'delivery_fee' => 'decimal:2',
            'scheduled_at' => 'datetime',
            'estimated_delivery_at' => 'datetime',
            'assigned_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    public function assignCourier(Courier $courier): void
    {
        $this->update([
            'courier_id' => $courier->id,
            'status' => DeliveryStatus::ASSIGNED,
            'assigned_at' => now(),
        ]);

        $courier->setBusy();
    }

    public function markPickedUp(): void
    {
        $this->update([
            'status' => DeliveryStatus::PICKED_UP,
            'picked_up_at' => now(),
        ]);
    }

    public function markDelivered(): void
    {
        $this->update([
            'status' => DeliveryStatus::DELIVERED,
            'delivered_at' => now(),
        ]);

        if ($this->courier) {
            $this->courier->setAvailable();
        }
    }

    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => DeliveryStatus::FAILED,
            'failure_reason' => $reason,
        ]);

        if ($this->courier) {
            $this->courier->setAvailable();
        }
    }

    public function getFullAddressAttribute(): string
    {
        $parts = [$this->address];

        if ($this->address_details) {
            $parts[] = $this->address_details;
        }

        return implode(', ', $parts);
    }

    public function scopePending($query)
    {
        return $query->where('status', DeliveryStatus::PENDING);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [
            DeliveryStatus::DELIVERED,
            DeliveryStatus::FAILED,
        ]);
    }

    public function scopeForCourier($query, $courierId)
    {
        return $query->where('courier_id', $courierId);
    }
}
