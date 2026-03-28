<?php

declare(strict_types=1);

namespace App\Domain\Menu\Models;

use App\Support\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workshop extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'name',
        'printer_id',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get products for this workshop.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get printer for this workshop.
     */
    public function printer()
    {
        return $this->belongsTo(\App\Domain\Organization\Models\Printer::class);
    }

    /**
     * Scope for active workshops.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordered by sort.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
