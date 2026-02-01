<?php

namespace App\Domain\Staff\Models;

use App\Support\Traits\HasUuid;
use App\Support\Traits\BelongsToOrganization;
use App\Support\Traits\BelongsToBranch;
use App\Support\Enums\SalaryType;
use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasUuid, BelongsToOrganization, BelongsToBranch, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'branch_id',
        'user_id',
        'position',
        'salary_type',
        'salary_amount',
        'sales_percent',
        'hired_at',
        'fired_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'salary_type' => SalaryType::class,
            'salary_amount' => 'decimal:2',
            'sales_percent' => 'decimal:2',
            'hired_at' => 'date',
            'fired_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user for this employee.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get employee shifts.
     */
    public function shifts(): HasMany
    {
        return $this->hasMany(EmployeeShift::class);
    }

    /**
     * Get salary calculations.
     */
    public function salaryCalculations(): HasMany
    {
        return $this->hasMany(SalaryCalculation::class);
    }

    /**
     * Check if employee is currently working.
     */
    public function isWorking(): bool
    {
        return $this->is_active && $this->fired_at === null;
    }

    /**
     * Get current active shift.
     */
    public function getCurrentShift(): ?EmployeeShift
    {
        return $this->shifts()
            ->whereNull('clock_out')
            ->latest('clock_in')
            ->first();
    }

    /**
     * Clock in for work.
     */
    public function clockIn(): EmployeeShift
    {
        return $this->shifts()->create([
            'organization_id' => $this->organization_id,
            'branch_id' => $this->branch_id,
            'clock_in' => now(),
        ]);
    }

    /**
     * Clock out from work.
     */
    public function clockOut(): ?EmployeeShift
    {
        $shift = $this->getCurrentShift();

        if ($shift) {
            $shift->update([
                'clock_out' => now(),
            ]);
            $shift->calculateWorkedMinutes();
        }

        return $shift;
    }
}
