<?php

declare(strict_types=1);

namespace App\Domain\Reservation\Models;

use App\Support\Traits\HasUuid;
use App\Support\Traits\BelongsToBranch;
use App\Support\Enums\ReservationStatus;
use App\Domain\Floor\Models\Table;
use App\Domain\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory, HasUuid, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'table_id',
        'customer_id',
        'guest_name',
        'guest_phone',
        'guest_email',
        'guests_count',
        'reservation_date',
        'reservation_time',
        'duration_minutes',
        'deposit_amount',
        'deposit_paid',
        'status',
        'source',
        'special_requests',
        'internal_notes',
        'confirmed_at',
        'cancelled_at',
        'cancellation_reason',
        'reminder_sent',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'guests_count' => 'integer',
            'reservation_date' => 'date',
            'reservation_time' => 'string',
            'duration_minutes' => 'integer',
            'deposit_amount' => 'decimal:2',
            'deposit_paid' => 'boolean',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'reminder_sent' => 'boolean',
        ];
    }

    /**
     * Get the table.
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Get the customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the reservation datetime combining date and time.
     */
    public function getReservedAtAttribute(): ?\Carbon\Carbon
    {
        if (!$this->reservation_date || !$this->reservation_time) {
            return null;
        }

        return $this->reservation_date->copy()->setTimeFromTimeString($this->reservation_time);
    }

    /**
     * Get end time.
     */
    public function getEndTimeAttribute(): ?\DateTime
    {
        $reservedAt = $this->reserved_at;
        if (!$reservedAt || !$this->duration_minutes) {
            return null;
        }

        return $reservedAt->copy()->addMinutes($this->duration_minutes);
    }

    /**
     * Check if reservation is for today.
     */
    public function isToday(): bool
    {
        return $this->reservation_date?->isToday() ?? false;
    }

    /**
     * Check if reservation is upcoming.
     */
    public function isUpcoming(): bool
    {
        $reservedAt = $this->reserved_at;
        return $reservedAt !== null && $reservedAt > now();
    }

    /**
     * Check if reservation is active (guest should be seated now).
     */
    public function isActive(): bool
    {
        $reservedAt = $this->reserved_at;
        if (!$reservedAt || !$this->duration_minutes) {
            return false;
        }

        $now = now();
        $end = $this->end_time;

        return $now >= $reservedAt && $now <= $end;
    }

    /**
     * Confirm reservation.
     */
    public function confirm(): void
    {
        $this->update([
            'status' => ReservationStatus::CONFIRMED,
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Mark guest as seated.
     */
    public function seat(): void
    {
        $this->update(['status' => ReservationStatus::SEATED]);
        $this->table?->occupy();
    }

    /**
     * Complete reservation.
     */
    public function complete(): void
    {
        $this->update(['status' => ReservationStatus::COMPLETED]);
        $this->table?->release();
    }

    /**
     * Cancel reservation.
     */
    public function cancel(?string $reason = null): void
    {
        $this->update([
            'status' => ReservationStatus::CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
        if ($this->table?->isReserved()) {
            $this->table->release();
        }
    }

    /**
     * Mark as no-show.
     */
    public function markNoShow(): void
    {
        $this->update(['status' => ReservationStatus::NO_SHOW]);
        if ($this->table?->isReserved()) {
            $this->table->release();
        }
    }

    /**
     * Scope for today's reservations.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('reservation_date', today());
    }

    /**
     * Scope for upcoming reservations.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('reservation_date', '>=', today());
    }

    /**
     * Scope by status.
     */
    public function scopeStatus($query, ReservationStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for date range.
     */
    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('reservation_date', [$start, $end]);
    }

    protected static function newFactory(): \Database\Factories\ReservationFactory
    {
        return \Database\Factories\ReservationFactory::new();
    }

}
