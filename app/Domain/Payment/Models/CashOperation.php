<?php

namespace App\Domain\Payment\Models;

use App\Support\Traits\HasUuid;
use App\Support\Traits\BelongsToOrganization;
use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashOperation extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'cash_shift_id',
        'user_id',
        'type',
        'amount',
        'reason',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the cash shift.
     */
    public function cashShift(): BelongsTo
    {
        return $this->belongsTo(CashShift::class);
    }

    /**
     * Get the user who made the operation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if it's a cash in operation.
     */
    public function isCashIn(): bool
    {
        return $this->type === 'in';
    }

    /**
     * Check if it's a cash out operation.
     */
    public function isCashOut(): bool
    {
        return $this->type === 'out';
    }

    /**
     * Scope for cash in operations.
     */
    public function scopeCashIn($query)
    {
        return $query->where('type', 'in');
    }

    /**
     * Scope for cash out operations.
     */
    public function scopeCashOut($query)
    {
        return $query->where('type', 'out');
    }
}
