<?php

declare(strict_types=1);

namespace App\Domain\Staff\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryCalculation extends Model
{
    protected $fillable = [
        'employee_id',
        'period_start',
        'period_end',
        'hours_worked',
        'base_salary',
        'sales_bonus',
        'tips',
        'bonuses',
        'penalties',
        'total_amount',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'hours_worked' => 'decimal:2',
            'base_salary' => 'decimal:2',
            'sales_bonus' => 'decimal:2',
            'tips' => 'decimal:2',
            'bonuses' => 'decimal:2',
            'penalties' => 'decimal:2',
            'total_amount' => 'decimal:2',
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
        $total = ($this->base_salary ?? 0)
            + ($this->sales_bonus ?? 0)
            + ($this->tips ?? 0)
            + ($this->bonuses ?? 0)
            - ($this->penalties ?? 0);

        $this->update(['total_amount' => $total]);
    }

    /**
     * Mark as paid.
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
}
