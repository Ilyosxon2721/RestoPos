<?php

declare(strict_types=1);

namespace App\Domain\Warehouse\Models;

use App\Domain\Menu\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TechCard extends Model
{
    protected $fillable = [
        'product_id',
        'output_quantity',
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

    public function items(): HasMany
    {
        return $this->hasMany(TechCardItem::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
