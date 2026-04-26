<?php

declare(strict_types=1);

namespace App\Domain\Staff\Models;

use App\Domain\Auth\Models\User;
use App\Support\Enums\SalaryType;
use App\Support\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use BelongsToBranch, HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'position',
        'hire_date',
        'birth_date',
        'passport_series',
        'passport_number',
        'address',
        'emergency_contact',
        'emergency_phone',
        'salary_type',
        'hourly_rate',
        'monthly_salary',
        'sales_percent',
    ];

    protected function casts(): array
    {
        return [
            'salary_type' => SalaryType::class,
            'hourly_rate' => 'decimal:2',
            'monthly_salary' => 'decimal:2',
            'sales_percent' => 'decimal:2',
            'hire_date' => 'date',
            'birth_date' => 'date',
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
        return $this->hire_date !== null;
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

    protected static function newFactory(): \Database\Factories\EmployeeFactory
    {
        return \Database\Factories\EmployeeFactory::new();
    }
}
