<?php

namespace App\Domain\Payment\Models;

use App\Support\Traits\BelongsToBranch;
use App\Support\Enums\CashShiftStatus;
use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashShift extends Model
{
    use BelongsToBranch;

    public $timestamps = false;

    protected $fillable = [
        'branch_id',
        'terminal_id',
        'opened_by',
        'closed_by',
        'opened_at',
        'closed_at',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'cash_difference',
        'total_sales',
        'total_refunds',
        'total_cash_payments',
        'total_card_payments',
        'total_orders',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => CashShiftStatus::class,
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'opening_cash' => 'decimal:2',
            'closing_cash' => 'decimal:2',
            'expected_cash' => 'decimal:2',
            'cash_difference' => 'decimal:2',
            'total_sales' => 'decimal:2',
            'total_refunds' => 'decimal:2',
            'total_cash_payments' => 'decimal:2',
            'total_card_payments' => 'decimal:2',
            'total_orders' => 'integer',
        ];
    }

    /**
     * Get the user who opened the shift.
     */
    public function openedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    /**
     * Get the user who closed the shift.
     */
    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
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

        $this->total_orders = $orders->count();
        $this->total_sales = $orders->sum('total_amount');

        // Рассчитываем по способам оплаты через связь payment_method
        $payments = Payment::whereIn('order_id', $orders->pluck('id'))
            ->where('status', 'completed')
            ->get();

        $this->total_cash_payments = $payments->filter(function ($payment) {
            return $payment->paymentMethod && $payment->paymentMethod->type === 'cash';
        })->sum('amount');

        $this->total_card_payments = $payments->filter(function ($payment) {
            return $payment->paymentMethod && $payment->paymentMethod->type === 'card';
        })->sum('amount');

        // Рассчитываем ожидаемую сумму наличных
        $cashIn = $this->cashOperations()->where('type', 'deposit')->sum('amount');
        $cashOut = $this->cashOperations()->where('type', 'withdrawal')->sum('amount');

        $this->expected_cash = $this->opening_cash + $this->total_cash_payments + $cashIn - $cashOut;

        $this->save();
    }

    /**
     * Close the shift.
     */
    public function close(float $closingCash, int $closedById, ?string $notes = null): void
    {
        $this->calculateTotals();

        $this->update([
            'status' => CashShiftStatus::CLOSED,
            'closed_at' => now(),
            'closed_by' => $closedById,
            'closing_cash' => $closingCash,
            'cash_difference' => $closingCash - $this->expected_cash,
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
            ->latest('opened_at')
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
