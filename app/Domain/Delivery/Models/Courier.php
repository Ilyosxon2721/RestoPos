<?php

declare(strict_types=1);

namespace App\Domain\Delivery\Models;

use App\Domain\Organization\Models\Branch;
use App\Domain\Staff\Models\Employee;
use App\Support\Enums\CourierStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Courier extends Model
{
    protected $fillable = [
        'employee_id',
        'branch_id',
        'name',
        'phone',
        'vehicle_type',
        'vehicle_number',
        'status',
        'current_location_lat',
        'current_location_lng',
        'last_location_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'status' => CourierStatus::class,
            'current_location_lat' => 'decimal:8',
            'current_location_lng' => 'decimal:8',
            'last_location_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class);
    }

    public function activeDeliveries(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class)
            ->whereNotIn('status', ['delivered', 'cancelled']);
    }

    public function updateLocation(float $lat, float $lng): void
    {
        $this->update([
            'current_location_lat' => $lat,
            'current_location_lng' => $lng,
            'last_location_at' => now(),
        ]);
    }

    public function setAvailable(): void
    {
        $this->update(['status' => CourierStatus::Available]);
    }

    public function setBusy(): void
    {
        $this->update(['status' => CourierStatus::Busy]);
    }

    public function setOffline(): void
    {
        $this->update(['status' => CourierStatus::Offline]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', CourierStatus::Available);
    }

    public function scopeOnline($query)
    {
        return $query->whereIn('status', [CourierStatus::Available, CourierStatus::Busy]);
    }
}
