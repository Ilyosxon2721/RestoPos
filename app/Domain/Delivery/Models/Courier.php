<?php

namespace App\Domain\Delivery\Models;

use App\Domain\Organization\Models\Branch;
use App\Domain\Staff\Models\Employee;
use App\Support\Enums\CourierStatus;
use App\Support\Traits\BelongsToBranch;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Courier extends Model
{
    use HasUuid, BelongsToBranch, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'employee_id',
        'name',
        'phone',
        'vehicle_type',
        'vehicle_number',
        'status',
        'current_lat',
        'current_lng',
        'last_location_at',
        'is_active',
    ];

    protected $casts = [
        'status' => CourierStatus::class,
        'current_lat' => 'decimal:8',
        'current_lng' => 'decimal:8',
        'last_location_at' => 'datetime',
        'is_active' => 'boolean',
    ];

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
            'current_lat' => $lat,
            'current_lng' => $lng,
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
