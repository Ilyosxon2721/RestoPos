<?php

namespace App\Domain\Delivery\Models;

use App\Domain\Customer\Models\Customer;
use App\Domain\Order\Models\Order;
use App\Domain\Organization\Models\Branch;
use App\Support\Enums\DeliveryStatus;
use App\Support\Traits\BelongsToBranch;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryOrder extends Model
{
    use HasUuid, BelongsToBranch, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'order_id',
        'customer_id',
        'courier_id',
        'delivery_zone_id',
        'status',
        'address',
        'address_details',
        'lat',
        'lng',
        'contact_name',
        'contact_phone',
        'scheduled_at',
        'picked_up_at',
        'delivered_at',
        'delivery_price',
        'distance_km',
        'estimated_time_minutes',
        'actual_time_minutes',
        'notes',
        'rating',
        'rating_comment',
    ];

    protected $casts = [
        'status' => DeliveryStatus::class,
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'scheduled_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'delivery_price' => 'decimal:2',
        'distance_km' => 'decimal:2',
        'estimated_time_minutes' => 'integer',
        'actual_time_minutes' => 'integer',
        'rating' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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
        $pickedUpAt = $this->picked_up_at ?? now();
        $actualTime = now()->diffInMinutes($pickedUpAt);

        $this->update([
            'status' => DeliveryStatus::DELIVERED,
            'delivered_at' => now(),
            'actual_time_minutes' => $actualTime,
        ]);

        if ($this->courier) {
            $this->courier->setAvailable();
        }
    }

    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => DeliveryStatus::FAILED,
            'notes' => $reason ? $this->notes . "\nОтмена: " . $reason : $this->notes,
        ]);

        if ($this->courier) {
            $this->courier->setAvailable();
        }
    }

    public function setRating(int $rating, string $comment = null): void
    {
        $this->update([
            'rating' => $rating,
            'rating_comment' => $comment,
        ]);
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
