<?php

declare(strict_types=1);

namespace App\Domain\Warehouse\Models;

use App\Domain\Menu\Models\PreparationMethod;
use App\Domain\Menu\Models\Product;
use App\Domain\Menu\Models\Unit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechCardItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'tech_card_id',
        'ingredient_id',
        'semi_finished_id',
        'unit_id',
        'preparation_method_id',
        'quantity',
        'loss_percent',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'loss_percent' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function techCard(): BelongsTo
    {
        return $this->belongsTo(TechCard::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function semiFinished(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'semi_finished_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function preparationMethod(): BelongsTo
    {
        return $this->belongsTo(PreparationMethod::class);
    }

    /**
     * Брутто = нетто * (1 + loss%/100).
     */
    public function getGrossQuantityAttribute(): float
    {
        return (float) $this->quantity * (1 + (float) $this->loss_percent / 100);
    }

    /**
     * Себестоимость строки = брутто * current_cost.
     * Для полуфабрикатов используется их product.cost_price.
     */
    public function getCostAmountAttribute(): float
    {
        $unitCost = (float) (
            $this->ingredient?->current_cost
            ?? $this->semiFinished?->cost_price
            ?? 0
        );

        return $this->gross_quantity * $unitCost;
    }
}
