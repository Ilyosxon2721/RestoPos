<?php

declare(strict_types=1);

namespace App\Domain\Floor\Models;

use App\Support\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hall extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'name',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get tables in this hall.
     */
    public function tables(): HasMany
    {
        return $this->hasMany(Table::class)->orderBy('sort_order');
    }

    /**
     * Get active tables.
     */
    public function activeTables(): HasMany
    {
        return $this->tables()->where('is_active', true);
    }

    /**
     * Get free tables count.
     */
    public function getFreeTablesCount(): int
    {
        return $this->activeTables()->where('status', 'free')->count();
    }

    /**
     * Get occupied tables count.
     */
    public function getOccupiedTablesCount(): int
    {
        return $this->activeTables()->where('status', 'occupied')->count();
    }

    /**
     * Get total capacity.
     */
    public function getTotalCapacity(): int
    {
        return $this->activeTables()->sum('capacity');
    }

    /**
     * Scope for active halls.
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

    protected static function newFactory(): \Database\Factories\HallFactory
    {
        return \Database\Factories\HallFactory::new();
    }

}
