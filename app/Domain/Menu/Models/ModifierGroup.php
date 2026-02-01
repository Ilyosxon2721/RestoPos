<?php

namespace App\Domain\Menu\Models;

use App\Support\Traits\HasUuid;
use App\Support\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class ModifierGroup extends Model
{
    use HasUuid, BelongsToOrganization, HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = [
        'organization_id',
        'name',
        'min_selections',
        'max_selections',
        'is_multiple',
        'is_active',
    ];

    protected $casts = [
        'min_selections' => 'integer',
        'max_selections' => 'integer',
        'is_multiple' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get modifiers in this group.
     */
    public function modifiers(): HasMany
    {
        return $this->hasMany(Modifier::class)->orderBy('sort_order');
    }

    /**
     * Get products that use this modifier group.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_modifier_groups')
            ->withPivot('is_required', 'sort_order');
    }

    /**
     * Check if selection is required.
     */
    public function isRequired(): bool
    {
        return $this->min_selections > 0;
    }

    /**
     * Validate selections count.
     */
    public function validateSelectionsCount(int $count): bool
    {
        if ($count < $this->min_selections) {
            return false;
        }

        if ($this->max_selections > 0 && $count > $this->max_selections) {
            return false;
        }

        return true;
    }

    /**
     * Scope for active groups.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
