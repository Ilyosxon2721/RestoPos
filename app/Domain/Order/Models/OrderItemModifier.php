<?php

namespace App\Domain\Order\Models;

use App\Domain\Menu\Models\Modifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemModifier extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_item_id',
        'modifier_id',
        'name',
        'price_adjustment',
        'quantity',
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Get the order item.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the modifier.
     */
    public function modifier(): BelongsTo
    {
        return $this->belongsTo(Modifier::class);
    }

    /**
     * Get total price.
     */
    public function getTotalAttribute(): float
    {
        return $this->price_adjustment * $this->quantity;
    }
}
