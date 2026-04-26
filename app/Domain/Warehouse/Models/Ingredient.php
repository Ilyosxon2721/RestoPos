<?php

declare(strict_types=1);

namespace App\Domain\Warehouse\Models;

use App\Support\Traits\BelongsToOrganization;
use App\Domain\Menu\Models\Unit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use HasFactory, BelongsToOrganization, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'external_source',
        'external_id',
        'category_id',
        'unit_id',
        'name',
        'sku',
        'barcode',
        'min_stock',
        'current_cost',
        'loss_percent',
        'shelf_life_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_stock' => 'decimal:3',
            'current_cost' => 'decimal:4',
            'loss_percent' => 'decimal:2',
            'shelf_life_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function stock(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected static function newFactory(): \Database\Factories\IngredientFactory
    {
        return \Database\Factories\IngredientFactory::new();
    }

}
