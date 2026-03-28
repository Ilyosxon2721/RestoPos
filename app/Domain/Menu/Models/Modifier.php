<?php

declare(strict_types=1);

namespace App\Domain\Menu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class Modifier extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = [
        'modifier_group_id',
        'name',
        'price_adjustment',
        'sort_order',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
        'sort_order' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the modifier group.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ModifierGroup::class, 'modifier_group_id');
    }

    /**
     * Scope for active modifiers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default modifiers.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope ordered by sort.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
