<?php

declare(strict_types=1);

namespace App\Domain\Warehouse\Models;

use App\Domain\Menu\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechCardItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'tech_card_id',
        'ingredient_id',
        'semi_finished_id',
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

    public function getGrossQuantityAttribute(): float
    {
        return (float) $this->quantity * (1 + (float) $this->loss_percent / 100);
    }
}
