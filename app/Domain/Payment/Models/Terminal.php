<?php

namespace App\Domain\Payment\Models;

use App\Support\Traits\HasUuid;
use App\Support\Traits\BelongsToOrganization;
use App\Support\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Terminal extends Model
{
    use HasUuid, BelongsToOrganization, BelongsToBranch;

    protected $fillable = [
        'organization_id',
        'branch_id',
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
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
