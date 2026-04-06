<?php

namespace App\Domain\Menu\Models;

use App\Support\Traits\HasUuid;
use App\Support\Traits\BelongsToOrganization;
use App\Support\Enums\ProductType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasFactory, HasUuid, BelongsToOrganization, HasTranslations, SoftDeletes;

    public array $translatable = ['name', 'description'];

    protected $fillable = [
        'organization_id',
        'category_id',
        'workshop_id',
        'unit_id',
        'type',
        'name',
        'name_uz',
        'name_en',
        'slug',
        'sku',
        'barcode',
        'description',
        'description_uz',
        'description_en',
        'image',
        'price',
        'cost_price',
        'calories',
        'proteins',
        'fats',
        'carbohydrates',
        'weight',
        'cooking_time',
        'is_weighable',
        'is_visible',
        'is_available',
        'in_stop_list',
        'stop_list_reason',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProductType::class,
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'calories' => 'integer',
            'proteins' => 'decimal:2',
            'fats' => 'decimal:2',
            'carbohydrates' => 'decimal:2',
            'weight' => 'decimal:3',
            'cooking_time' => 'integer',
            'is_weighable' => 'boolean',
            'is_visible' => 'boolean',
            'is_available' => 'boolean',
            'in_stop_list' => 'boolean',
            'sort_order' => 'integer',
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
        return $this->hasOne(\App\Domain\Warehouse\Models\TechCard::class);
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
        return $query->where('is_available', true);
    }

    /**
     * Scope for visible products.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
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

    protected static function newFactory(): \Database\Factories\ProductFactory
    {
        return \Database\Factories\ProductFactory::new();
    }

}
