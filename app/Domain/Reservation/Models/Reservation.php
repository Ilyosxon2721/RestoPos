<?php

namespace App\Domain\Reservation\Models;

use App\Support\Traits\HasUuid;
use App\Support\Traits\BelongsToOrganization;
use App\Support\Traits\BelongsToBranch;
use App\Support\Enums\ReservationStatus;
use App\Domain\Floor\Models\Table;
use App\Domain\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasUuid, BelongsToOrganization, BelongsToBranch;

    protected $fillable = [
        'organization_id',
        'branch_id',
        'table_id',
        'customer_id',
        'guest_name',
        'guest_phone',
        'guest_count',
        'reserved_at',
        'duration',
        'status',
        'notes',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'guest_count' => 'integer',
            'reserved_at' => 'datetime',
            'duration' => 'integer',
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
     * Get end time.
     */
    public function getEndTimeAttribute(): ?\DateTime
    {
        if (!$this->reserved_at || !$this->duration) {
            return null;
        }

        return $this->reserved_at->copy()->addMinutes($this->duration);
    }

    /**
     * Check if reservation is for today.
     */
    public function isToday(): bool
    {
        return $this->reserved_at?->isToday() ?? false;
    }

    /**
     * Check if reservation is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->reserved_at > now();
    }

    /**
     * Check if reservation is active (guest should be seated now).
     */
    public function isActive(): bool
    {
        if (!$this->reserved_at || !$this->duration) {
            return false;
        }

        $now = now();
        $start = $this->reserved_at;
        $end = $this->end_time;

        return $now >= $start && $now <= $end;
    }

    /**
     * Confirm reservation.
     */
    public function confirm(): void
    {
        $this->update(['status' => ReservationStatus::CONFIRMED]);
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
    public function cancel(): void
    {
        $this->update(['status' => ReservationStatus::CANCELLED]);
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
        return $query->whereDate('reserved_at', today());
    }

    /**
     * Scope for upcoming reservations.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('reserved_at', '>', now());
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
        return $query->whereBetween('reserved_at', [$start, $end]);
    }
}
