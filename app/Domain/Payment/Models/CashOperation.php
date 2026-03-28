<?php

declare(strict_types=1);

namespace App\Domain\Payment\Models;

use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashOperation extends Model
{
    protected $fillable = [
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
     * Check if it's a deposit operation.
     */
    public function isCashIn(): bool
    {
        return $this->type === 'deposit';
    }

    /**
     * Check if it's a withdrawal operation.
     */
    public function isCashOut(): bool
    {
        return $this->type === 'withdrawal';
    }

    /**
     * Scope for deposit operations.
     */
    public function scopeCashIn($query)
    {
        return $query->where('type', 'deposit');
    }

    /**
     * Scope for withdrawal operations.
     */
    public function scopeCashOut($query)
    {
        return $query->where('type', 'withdrawal');
    }
}
