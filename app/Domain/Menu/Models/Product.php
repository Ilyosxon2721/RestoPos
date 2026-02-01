<?php

namespace App\Domain\Menu\Models;

use App\Support\Traits\HasUuid;
use App\Support\Traits\BelongsToOrganization;
use App\Support\Enums\ProductType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasUuid, BelongsToOrganization, HasTranslations, SoftDeletes;

    public array $translatable = ['name', 'description'];

    protected $fillable = [
        'organization_id',
        'category_id',
        'workshop_id',
        'unit_id',
        'name',
        'sku',
        'barcode',
        'description',
        'image',
        'type',
        'cost_price',
        'sort_order',
        'prep_time',
        'is_active',
        'is_popular',
        'is_new',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProductType::class,
            'cost_price' => 'decimal:2',
            'sort_order' => 'integer',
            'prep_time' => 'integer',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'is_new' => 'boolean',
        ];
    }

    /**
     * Get product category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get product workshop.
     */
    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    /**
     * Get product unit.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get product prices per branch.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    /**
     * Get modifier groups for this product.
     */
    public function modifierGroups(): BelongsToMany
    {
        return $this->belongsToMany(ModifierGroup::class, 'product_modifier_groups')
            ->withPivot('is_required', 'sort_order')
            ->orderByPivot('sort_order');
    }

    /**
     * Get tech card for this product.
     */
    public function techCard(): HasOne
    {
        return $this->hasOne(\App\Domain\Recipe\Models\TechCard::class);
    }

    /**
     * Get price for specific branch.
     */
    public function getPriceForBranch(int $branchId): ?ProductPrice
    {
        return $this->prices()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get selling price for branch.
     */
    public function getSellingPrice(int $branchId): float
    {
        $price = $this->getPriceForBranch($branchId);
        return $price?->price ?? 0;
    }

    /**
     * Get margin for branch.
     */
    public function getMargin(int $branchId): float
    {
        $sellingPrice = $this->getSellingPrice($branchId);
        $costPrice = $this->cost_price ?? 0;

        if ($sellingPrice <= 0) {
            return 0;
        }

        return (($sellingPrice - $costPrice) / $sellingPrice) * 100;
    }

    /**
     * Scope for active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for popular products.
     */
    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    /**
     * Scope by category.
     */
    public function scopeInCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope by workshop.
     */
    public function scopeInWorkshop($query, int $workshopId)
    {
        return $query->where('workshop_id', $workshopId);
    }

    /**
     * Scope by type.
     */
    public function scopeOfType($query, ProductType $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope ordered by sort.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Search products by name, SKU, or barcode.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('barcode', $search);
        });
    }
}
