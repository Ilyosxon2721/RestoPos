<?php

namespace App\Domain\Payment\Models;

use App\Support\Traits\HasUuid;
use App\Support\Traits\BelongsToOrganization;
use App\Support\Traits\BelongsToBranch;
use App\Support\Enums\CashShiftStatus;
use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashShift extends Model
{
    use HasUuid, BelongsToOrganization, BelongsToBranch;

    protected $fillable = [
        'organization_id',
        'branch_id',
        'terminal_id',
        'user_id',
        'closed_by_id',
        'shift_number',
        'status',
        'opened_at',
        'closed_at',
        'opening_cash',
        'expected_cash',
        'actual_cash',
        'difference',
        'total_sales',
        'total_cash',
        'total_card',
        'total_other',
        'total_refunds',
        'orders_count',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => CashShiftStatus::class,
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'opening_cash' => 'decimal:2',
            'expected_cash' => 'decimal:2',
            'actual_cash' => 'decimal:2',
            'difference' => 'decimal:2',
            'total_sales' => 'decimal:2',
            'total_cash' => 'decimal:2',
            'total_card' => 'decimal:2',
            'total_other' => 'decimal:2',
            'total_refunds' => 'decimal:2',
            'orders_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($shift) {
            if (empty($shift->shift_number)) {
                $shift->shift_number = static::generateShiftNumber($shift->branch_id);
            }
            if (empty($shift->opened_at)) {
                $shift->opened_at = now();
            }
        });
    }

    /**
     * Generate shift number.
     */
    public static function generateShiftNumber(int $branchId): string
    {
        $today = now()->format('Ymd');
        $count = static::where('branch_id', $branchId)
            ->whereDate('created_at', today())
            ->count();

        return 'S' . $today . '-' . str_pad($count + 1, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Get the cashier who opened the shift.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who closed the shift.
     */
    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_id');
    }

    /**
     * Get terminal.
     */
    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }

    /**
     * Get orders for this shift.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(\App\Domain\Order\Models\Order::class);
    }

    /**
     * Get cash operations.
     */
    public function cashOperations(): HasMany
    {
        return $this->hasMany(CashOperation::class);
    }

    /**
     * Check if shift is open.
     */
    public function isOpen(): bool
    {
        return $this->status === CashShiftStatus::OPEN;
    }

    /**
     * Calculate totals from orders.
     */
    public function calculateTotals(): void
    {
        $orders = $this->orders()->completed()->get();

        $this->orders_count = $orders->count();
        $this->total_sales = $orders->sum('total');
        $this->total_refunds = $orders->sum('refund_amount');

        // Calculate by payment method
        $payments = Payment::whereIn('order_id', $orders->pluck('id'))
            ->where('status', 'completed')
            ->get();

        $this->total_cash = $payments->where('method', 'cash')->sum('amount');
        $this->total_card = $payments->where('method', 'card')->sum('amount');
        $this->total_other = $payments->whereNotIn('method', ['cash', 'card'])->sum('amount');

        // Calculate expected cash
        $cashIn = $this->cashOperations()->where('type', 'in')->sum('amount');
        $cashOut = $this->cashOperations()->where('type', 'out')->sum('amount');

        $this->expected_cash = $this->opening_cash + $this->total_cash + $cashIn - $cashOut - $this->total_refunds;

        $this->save();
    }

    /**
     * Close the shift.
     */
    public function close(float $actualCash, int $closedById, ?string $notes = null): void
    {
        $this->calculateTotals();

        $this->update([
            'status' => CashShiftStatus::CLOSED,
            'closed_at' => now(),
            'closed_by_id' => $closedById,
            'actual_cash' => $actualCash,
            'difference' => $actualCash - $this->expected_cash,
            'notes' => $notes,
        ]);
    }

    /**
     * Get current open shift for branch.
     */
    public static function getCurrentForBranch(int $branchId): ?self
    {
        return static::where('branch_id', $branchId)
            ->where('status', CashShiftStatus::OPEN)
            ->latest()
            ->first();
    }

    /**
     * Scope for open shifts.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', CashShiftStatus::OPEN);
    }

    /**
     * Scope for today's shifts.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('opened_at', today());
    }
}
