<?php

namespace App\Domain\Order\Models;

use App\Support\Traits\HasUuid;
use App\Support\Enums\OrderItemStatus;
use App\Domain\Menu\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    use HasUuid;

    protected $fillable = [
        'order_id',
        'product_id',
        'name',
        'quantity',
        'unit_price',
        'discount_amount',
        'total',
        'status',
        'notes',
        'sent_to_kitchen_at',
        'prepared_at',
        'served_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderItemStatus::class,
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'sent_to_kitchen_at' => 'datetime',
            'prepared_at' => 'datetime',
            'served_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function ($item) {
            $item->calculateTotal();
        });

        static::saved(function ($item) {
            $item->order->calculateTotals();
        });

        static::deleted(function ($item) {
            $item->order->calculateTotals();
        });
    }

    /**
     * Get the order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get modifiers for this item.
     */
    public function modifiers(): HasMany
    {
        return $this->hasMany(OrderItemModifier::class);
    }

    /**
     * Calculate total for this item.
     */
    public function calculateTotal(): void
    {
        $modifiersTotal = $this->modifiers()->sum('price');
        $baseTotal = ($this->unit_price + $modifiersTotal) * $this->quantity;
        $this->total = $baseTotal - ($this->discount_amount ?? 0);
    }

    /**
     * Send to kitchen.
     */
    public function sendToKitchen(): void
    {
        $this->update([
            'status' => OrderItemStatus::SENT,
            'sent_to_kitchen_at' => now(),
        ]);
    }

    /**
     * Mark as preparing.
     */
    public function markPreparing(): void
    {
        $this->update(['status' => OrderItemStatus::PREPARING]);
    }

    /**
     * Mark as ready.
     */
    public function markReady(): void
    {
        $this->update([
            'status' => OrderItemStatus::READY,
            'prepared_at' => now(),
        ]);
    }

    /**
     * Mark as served.
     */
    public function markServed(): void
    {
        $this->update([
            'status' => OrderItemStatus::SERVED,
            'served_at' => now(),
        ]);
    }

    /**
     * Cancel item.
     */
    public function cancel(): void
    {
        $this->update(['status' => OrderItemStatus::CANCELLED]);
    }

    /**
     * Check if item can be cancelled.
     */
    public function canCancel(): bool
    {
        return in_array($this->status, [
            OrderItemStatus::PENDING,
            OrderItemStatus::SENT,
        ]);
    }

    /**
     * Get display name with modifiers.
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->name;

        if ($this->modifiers->isNotEmpty()) {
            $modifierNames = $this->modifiers->pluck('name')->join(', ');
            $name .= " ({$modifierNames})";
        }

        return $name;
    }

    /**
     * Scope for pending items.
     */
    public function scopePending($query)
    {
        return $query->where('status', OrderItemStatus::PENDING);
    }

    /**
     * Scope for items sent to kitchen.
     */
    public function scopeInKitchen($query)
    {
        return $query->whereIn('status', [
            OrderItemStatus::SENT,
            OrderItemStatus::PREPARING,
        ]);
    }

    /**
     * Scope for ready items.
     */
    public function scopeReady($query)
    {
        return $query->where('status', OrderItemStatus::READY);
    }
}
