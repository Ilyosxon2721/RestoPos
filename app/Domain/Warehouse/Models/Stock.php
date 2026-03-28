<?php

declare(strict_types=1);

namespace App\Domain\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stock extends Model
{
    // Таблица stock не имеет created_at, только updated_at
    const CREATED_AT = null;

    protected $table = 'stock';

    protected $fillable = [
        'warehouse_id',
        'ingredient_id',
        'quantity',
        'reserved_quantity',
        'average_cost',
        'last_supply_date',
        'last_supply_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'reserved_quantity' => 'decimal:3',
        'average_cost' => 'decimal:4',
        'last_supply_date' => 'datetime',
        'last_supply_price' => 'decimal:4',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(StockBatch::class, 'warehouse_id', 'warehouse_id')
            ->whereColumn('stock_batches.ingredient_id', 'stock.ingredient_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'warehouse_id', 'warehouse_id')
            ->whereColumn('stock_movements.ingredient_id', 'stock.ingredient_id');
    }

    /**
     * Доступное количество (за вычетом резерва).
     */
    public function getAvailableQuantity(): float
    {
        return (float) $this->quantity - (float) $this->reserved_quantity;
    }

    /**
     * Проверить, низкий ли остаток (количество <= 0).
     */
    public function isLow(): bool
    {
        return (float) $this->quantity <= 0;
    }

    public function scopeLowStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }
}
