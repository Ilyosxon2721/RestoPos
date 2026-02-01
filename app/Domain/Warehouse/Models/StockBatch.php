<?php

namespace App\Domain\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBatch extends Model
{
    protected $fillable = [
        'stock_id',
        'supply_item_id',
        'quantity',
        'remaining_quantity',
        'cost_price',
        'expiry_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'remaining_quantity' => 'decimal:3',
        'cost_price' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function supplyItem(): BelongsTo
    {
        return $this->belongsTo(SupplyItem::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function scopeAvailable($query)
    {
        return $query->where('remaining_quantity', '>', 0);
    }

    public function scopeFifoOrder($query)
    {
        return $query->orderBy('expiry_date')->orderBy('created_at');
    }
}
