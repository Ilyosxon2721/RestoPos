<?php

declare(strict_types=1);

namespace App\Domain\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBatch extends Model
{
    protected $fillable = [
        'warehouse_id',
        'ingredient_id',
        'supply_item_id',
        'initial_quantity',
        'remaining_quantity',
        'cost_price',
        'expiry_date',
        'batch_number',
    ];

    protected function casts(): array
    {
        return [
            'initial_quantity' => 'decimal:3',
            'remaining_quantity' => 'decimal:3',
            'cost_price' => 'decimal:4',
            'expiry_date' => 'date',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
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
