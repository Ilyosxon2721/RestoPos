<?php

declare(strict_types=1);

namespace App\Domain\Payment\Models;

use App\Support\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Terminal extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'name',
        'device_id',
        'type',
        'settings',
        'last_seen_at',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'last_seen_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get cash shifts for this terminal.
     */
    public function cashShifts(): HasMany
    {
        return $this->hasMany(CashShift::class);
    }

    /**
     * Get current open shift.
     */
    public function getCurrentShift(): ?CashShift
    {
        return $this->cashShifts()->open()->latest()->first();
    }

    /**
     * Scope for active terminals.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
