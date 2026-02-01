<?php

namespace App\Domain\Staff\Models;

use App\Support\Traits\HasUuid;
use App\Support\Traits\BelongsToOrganization;
use App\Support\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeShift extends Model
{
    use HasUuid, BelongsToOrganization, BelongsToBranch;

    protected $fillable = [
        'organization_id',
        'branch_id',
        'employee_id',
        'clock_in',
        'clock_out',
        'worked_minutes',
        'break_minutes',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
            'worked_minutes' => 'integer',
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
     * Calculate worked minutes.
     */
    public function calculateWorkedMinutes(): void
    {
        if ($this->clock_in && $this->clock_out) {
            $totalMinutes = $this->clock_in->diffInMinutes($this->clock_out);
            $this->update([
                'worked_minutes' => $totalMinutes - ($this->break_minutes ?? 0),
            ]);
        }
    }

    /**
     * Get formatted duration.
     */
    public function getDurationAttribute(): string
    {
        if (!$this->worked_minutes) {
            return '0ч 0м';
        }

        $hours = floor($this->worked_minutes / 60);
        $minutes = $this->worked_minutes % 60;

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
