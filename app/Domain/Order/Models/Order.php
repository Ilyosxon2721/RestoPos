<?php

namespace App\Domain\Order\Models;

use App\Support\Traits\HasUuid;
use App\Support\Traits\BelongsToBranch;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\OrderType;
use App\Support\Enums\OrderSource;
use App\Support\Enums\PaymentStatus;
use App\Domain\Floor\Models\Table;
use App\Domain\Staff\Models\Employee;
use App\Domain\Customer\Models\Customer;
use App\Domain\Payment\Models\CashShift;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, HasUuid, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'table_id',
        'waiter_id',
        'customer_id',
        'cash_shift_id',
        'order_number',
        'type',
        'source',
        'status',
        'payment_status',
        'guests_count',
        'subtotal',
        'discount_amount',
        'discount_percent',
        'discount_reason',
        'service_charge',
        'tax_amount',
        'total_amount',
        'notes',
        'opened_at',
        'accepted_at',
        'ready_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => OrderType::class,
            'source' => OrderSource::class,
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'guests_count' => 'integer',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'service_charge' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'opened_at' => 'datetime',
            'accepted_at' => 'datetime',
            'ready_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber($order->branch_id);
            }
            if (empty($order->opened_at)) {
                $order->opened_at = now();
            }
        });
    }

    /**
     * Generate unique order number for branch.
     */
    public static function generateOrderNumber(int $branchId): string
    {
        $today = now()->format('Ymd');
        $count = static::where('branch_id', $branchId)
            ->whereDate('created_at', today())
            ->count();

        return $today . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the table.
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Get the waiter (employee).
     */
    public function waiter(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'waiter_id');
    }

    /**
     * Get the customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the cash shift.
     */
    public function cashShift(): BelongsTo
    {
        return $this->belongsTo(CashShift::class);
    }

    /**
     * Get order items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get payments.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(\App\Domain\Payment\Models\Payment::class);
    }

    /**
     * Calculate totals.
     */
    public function calculateTotals(): void
    {
        $subtotal = $this->items()->sum('total_price');

        $discountAmount = $this->discount_percent
            ? $subtotal * ($this->discount_percent / 100)
            : ($this->discount_amount ?? 0);

        $afterDiscount = $subtotal - $discountAmount;
        $serviceCharge = $this->service_charge ?? 0;
        $taxAmount = $this->tax_amount ?? 0;
        $total = $afterDiscount + $serviceCharge + $taxAmount;

        $this->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'total_amount' => $total,
        ]);
    }

    /**
     * Get remaining amount to pay.
     * Рассчитывается из суммы завершённых платежей.
     */
    public function getRemainingAmount(): float
    {
        $paidAmount = $this->payments()
            ->where('status', 'completed')
            ->sum('amount');

        return max(0, $this->total_amount - $paidAmount);
    }

    /**
     * Check if order is paid.
     */
    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::PAID;
    }

    /**
     * Check if order is open.
     */
    public function isOpen(): bool
    {
        return !in_array($this->status, [OrderStatus::COMPLETED, OrderStatus::CANCELLED]);
    }

    /**
     * Check if order can be modified.
     */
    public function canModify(): bool
    {
        return $this->isOpen() && !$this->isPaid();
    }

    /**
     * Update payment status based on payments sum.
     */
    public function updatePaymentStatus(): void
    {
        $paidAmount = $this->payments()
            ->where('status', 'completed')
            ->sum('amount');

        if ($paidAmount <= 0) {
            $this->payment_status = PaymentStatus::UNPAID;
        } elseif ($paidAmount < $this->total_amount) {
            $this->payment_status = PaymentStatus::PARTIAL;
        } else {
            $this->payment_status = PaymentStatus::PAID;
        }

        $this->save();
    }

    /**
     * Transition order status.
     */
    public function transitionTo(OrderStatus $newStatus): bool
    {
        if (!$this->status->canTransitionTo($newStatus)) {
            return false;
        }

        $this->status = $newStatus;

        if ($newStatus === OrderStatus::COMPLETED || $newStatus === OrderStatus::CANCELLED) {
            $this->closed_at = now();
        }

        return $this->save();
    }

    /**
     * Scope for open orders.
     */
    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', [OrderStatus::COMPLETED, OrderStatus::CANCELLED]);
    }

    /**
     * Scope for completed orders.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', OrderStatus::COMPLETED);
    }

    /**
     * Scope for today's orders.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope by type.
     */
    public function scopeOfType($query, OrderType $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by table.
     */
    public function scopeForTable($query, int $tableId)
    {
        return $query->where('table_id', $tableId);
    }

    protected static function newFactory(): \Database\Factories\OrderFactory
    {
        return \Database\Factories\OrderFactory::new();
    }

}
