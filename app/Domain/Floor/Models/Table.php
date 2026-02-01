<?php

namespace App\Domain\Floor\Models;

use App\Support\Traits\HasUuid;
use App\Support\Traits\BelongsToOrganization;
use App\Support\Traits\BelongsToBranch;
use App\Support\Enums\TableStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Table extends Model
{
    use HasUuid, BelongsToOrganization, BelongsToBranch;

    protected $fillable = [
        'organization_id',
        'branch_id',
        'hall_id',
        'name',
        'number',
        'capacity',
        'min_capacity',
        'status',
        'pos_x',
        'pos_y',
        'width',
        'height',
        'shape',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'status' => TableStatus::class,
            'capacity' => 'integer',
            'min_capacity' => 'integer',
            'pos_x' => 'integer',
            'pos_y' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the hall.
     */
    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    /**
     * Get orders for this table.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(\App\Domain\Order\Models\Order::class);
    }

    /**
     * Get current active order.
     */
    public function currentOrder(): HasOne
    {
        return $this->hasOne(\App\Domain\Order\Models\Order::class)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest();
    }

    /**
     * Get reservations for this table.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(\App\Domain\Reservation\Models\Reservation::class);
    }

    /**
     * Check if table is free.
     */
    public function isFree(): bool
    {
        return $this->status === TableStatus::FREE;
    }

    /**
     * Check if table is occupied.
     */
    public function isOccupied(): bool
    {
        return $this->status === TableStatus::OCCUPIED;
    }

    /**
     * Check if table is reserved.
     */
    public function isReserved(): bool
    {
        return $this->status === TableStatus::RESERVED;
    }

    /**
     * Mark table as occupied.
     */
    public function occupy(): void
    {
        $this->update(['status' => TableStatus::OCCUPIED]);
    }

    /**
     * Mark table as free.
     */
    public function release(): void
    {
        $this->update(['status' => TableStatus::FREE]);
    }

    /**
     * Mark table as reserved.
     */
    public function reserve(): void
    {
        $this->update(['status' => TableStatus::RESERVED]);
    }

    /**
     * Get display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: "Стол {$this->number}";
    }

    /**
     * Scope for active tables.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for free tables.
     */
    public function scopeFree($query)
    {
        return $query->where('status', TableStatus::FREE);
    }

    /**
     * Scope for occupied tables.
     */
    public function scopeOccupied($query)
    {
        return $query->where('status', TableStatus::OCCUPIED);
    }

    /**
     * Scope by hall.
     */
    public function scopeInHall($query, int $hallId)
    {
        return $query->where('hall_id', $hallId);
    }

    /**
     * Scope ordered by sort.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('number');
    }
}
