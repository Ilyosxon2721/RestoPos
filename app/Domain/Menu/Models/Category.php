<?php

declare(strict_types=1);

namespace App\Domain\Menu\Models;

use App\Support\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use BelongsToOrganization, HasFactory, HasTranslations, SoftDeletes;

    public array $translatable = ['name', 'description'];

    protected $fillable = [
        'organization_id',
        'external_source',
        'external_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'color',
        'sort_order',
        'is_visible',
        'available_from',
        'available_to',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_visible' => 'boolean',
        'available_from' => 'datetime:H:i',
        'available_to' => 'datetime:H:i',
    ];

    /**
     * Get parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get products in this category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all descendants (recursive).
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestor IDs.
     */
    public function getAncestorIds(): array
    {
        $ids = [];
        $category = $this;

        while ($category->parent_id) {
            $ids[] = $category->parent_id;
            $category = $category->parent;
        }

        return $ids;
    }

    /**
     * Check if category has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Scope for root categories (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope for active (visible) categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope ordered by sort.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    protected static function newFactory(): \Database\Factories\CategoryFactory
    {
        return \Database\Factories\CategoryFactory::new();
    }
}
