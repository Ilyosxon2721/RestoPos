<?php

declare(strict_types=1);

namespace App\Domain\Warehouse\Models;

use App\Domain\Menu\Models\Product;
use App\Domain\Menu\Models\Unit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TechCard extends Model
{
    protected $fillable = [
        'product_id',
        'output_quantity',
        'output_unit_id',
        'description',
        'cooking_instructions',
        'version',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'output_quantity' => 'decimal:3',
            'version' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function outputUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'output_unit_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TechCardItem::class)->orderBy('sort_order');
    }

    /**
     * Aggregate cost: sum of (gross_quantity * ingredient.current_cost).
     * Falls back to 0 if items not loaded.
     */
    public function getTotalCostAttribute(): float
    {
        return (float) $this->items->sum(fn (TechCardItem $item) => $item->cost_amount);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
