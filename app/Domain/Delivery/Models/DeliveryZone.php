<?php

namespace App\Domain\Delivery\Models;

use App\Domain\Organization\Models\Branch;
use App\Support\Traits\BelongsToBranch;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryZone extends Model
{
    use HasUuid, BelongsToBranch, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'name',
        'polygon',
        'min_order_amount',
        'delivery_price',
        'free_delivery_from',
        'estimated_time_minutes',
        'is_active',
    ];

    protected $casts = [
        'polygon' => 'array',
        'min_order_amount' => 'decimal:2',
        'delivery_price' => 'decimal:2',
        'free_delivery_from' => 'decimal:2',
        'estimated_time_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class);
    }

    public function containsPoint(float $lat, float $lng): bool
    {
        if (empty($this->polygon)) {
            return false;
        }

        $polygon = $this->polygon;
        $n = count($polygon);
        $inside = false;

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $polygon[$i]['lng'];
            $yi = $polygon[$i]['lat'];
            $xj = $polygon[$j]['lng'];
            $yj = $polygon[$j]['lat'];

            if ((($yi > $lat) !== ($yj > $lat)) &&
                ($lng < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    public function getDeliveryPrice(float $orderAmount): float
    {
        if ($this->free_delivery_from && $orderAmount >= $this->free_delivery_from) {
            return 0;
        }

        return (float) $this->delivery_price;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
