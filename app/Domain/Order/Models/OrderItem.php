<?php

namespace App\Domain\Order\Models;

use App\Domain\Menu\Models\Product;
use App\Support\Enums\OrderItemStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'name',
        'quantity',
        'unit_price',
        'discount_amount',
        'total_price',
        'cost_price',
        'tax_id',
        'tax_rate',
        'tax_type',
        'tax_amount',
        'course',
        'status',
        'comment',
        'sent_to_kitchen_at',
        'ready_at',
        'cancelled_reason',
        'cancelled_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderItemStatus::class,
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'course' => 'integer',
            'sent_to_kitchen_at' => 'datetime',
            'ready_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function ($item) {
            $item->calculateTotal();
            $item->calculateTax();
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
     * Get tax snapshot.
     */
    public function tax(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Menu\Models\Tax::class);
    }

    /**
     * Calculate total for this item.
     */
    public function calculateTotal(): void
    {
        $modifiersTotal = $this->modifiers()->sum('price_adjustment');
        $baseTotal = ($this->unit_price + $modifiersTotal) * $this->quantity;
        $this->total_price = $baseTotal - ($this->discount_amount ?? 0);
    }

    /**
     * Compute tax_amount from snapshot rate and type.
     *
     * VAT (НДС) is treated as included in unit_price → extracted from total_price.
     * Turnover (С оборота) is added on top of total_price.
     * None / no rate → zero.
     */
    public function calculateTax(): void
    {
        $rate = (float) ($this->tax_rate ?? 0);
        if ($rate <= 0 || $this->tax_type === 'none' || !$this->tax_type) {
            $this->tax_amount = 0;

            return;
        }

        $base = (float) $this->total_price;
        $this->tax_amount = match ($this->tax_type) {
            'vat' => round($base * $rate / (100 + $rate), 2),
            'turnover' => round($base * $rate / 100, 2),
            default => 0,
        };
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
            'ready_at' => now(),
        ]);
    }

    /**
     * Mark as served.
     */
    public function markServed(): void
    {
        $this->update([
            'status' => OrderItemStatus::SERVED,
        ]);
    }

    /**
     * Cancel item.
     */
    public function cancel(?string $reason = null, ?int $cancelledById = null): void
    {
        $this->update([
            'status' => OrderItemStatus::CANCELLED,
            'cancelled_reason' => $reason,
            'cancelled_by' => $cancelledById,
        ]);
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

    protected static function newFactory(): \Database\Factories\OrderItemFactory
    {
        return \Database\Factories\OrderItemFactory::new();
    }
}
