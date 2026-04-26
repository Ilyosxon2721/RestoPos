<?php

declare(strict_types=1);

namespace App\Domain\Staff\Models;

use App\Support\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeShift extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'employee_id',
        'branch_id',
        'clock_in',
        'clock_out',
        'break_minutes',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
            'break_minutes' => 'integer',
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
     * Calculate worked minutes from clock_in/clock_out.
     */
    public function getWorkedMinutes(): int
    {
        if ($this->clock_in && $this->clock_out) {
            $totalMinutes = $this->clock_in->diffInMinutes($this->clock_out);

            return (int) ($totalMinutes - ($this->break_minutes ?? 0));
        }

        return 0;
    }

    /**
     * Get formatted duration.
     */
    public function getDurationAttribute(): string
    {
        $workedMinutes = $this->getWorkedMinutes();

        if ($workedMinutes <= 0) {
            return '0ч 0м';
        }

        $hours = floor($workedMinutes / 60);
        $minutes = $workedMinutes % 60;

        return "{$hours}ч {$minutes}м";
    }

    /**
     * Check if shift is active.
     */
    public function isActive(): bool
    {
        return $this->clock_out === null;
    }
}
