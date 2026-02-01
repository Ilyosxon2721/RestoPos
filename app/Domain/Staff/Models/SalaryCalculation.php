<?php

namespace App\Domain\Staff\Models;

use App\Support\Traits\HasUuid;
use App\Support\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryCalculation extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'employee_id',
        'period_start',
        'period_end',
        'worked_hours',
        'hourly_amount',
        'fixed_amount',
        'sales_amount',
        'sales_percent_amount',
        'bonus',
        'penalty',
        'total_amount',
        'is_paid',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'worked_hours' => 'decimal:2',
            'hourly_amount' => 'decimal:2',
            'fixed_amount' => 'decimal:2',
            'sales_amount' => 'decimal:2',
            'sales_percent_amount' => 'decimal:2',
            'bonus' => 'decimal:2',
            'penalty' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'is_paid' => 'boolean',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the employee.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Calculate total amount.
     */
    public function calculateTotal(): void
    {
        $total = ($this->hourly_amount ?? 0)
            + ($this->fixed_amount ?? 0)
            + ($this->sales_percent_amount ?? 0)
            + ($this->bonus ?? 0)
            - ($this->penalty ?? 0);

        $this->update(['total_amount' => $total]);
    }

    /**
     * Mark as paid.
     */
    public function markAsPaid(): void
    {
        $this->update([
            'is_paid' => true,
            'paid_at' => now(),
        ]);
    }
}
