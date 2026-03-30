<?php

declare(strict_types=1);

namespace App\Domain\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'inventory_id',
        'ingredient_id',
        'expected_quantity',
        'actual_quantity',
        'cost_price',
    ];

    protected function casts(): array
    {
        return [
            'expected_quantity' => 'decimal:3',
            'actual_quantity' => 'decimal:3',
            'cost_price' => 'decimal:4',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function getDifferenceAttribute(): float
    {
        if ($this->actual_quantity === null) {
            return 0;
        }
        return (float) $this->actual_quantity - (float) $this->expected_quantity;
    }
}
