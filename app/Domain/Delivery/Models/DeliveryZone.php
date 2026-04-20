<?php

declare(strict_types=1);

namespace App\Domain\Delivery\Models;

use App\Domain\Organization\Models\Branch;
use App\Support\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryZone extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'name',
        'polygon',
        'min_order_amount',
        'delivery_fee',
        'free_delivery_from',
        'estimated_time',
        'is_active',
    ];

    protected $casts = [
        'polygon' => 'array',
        'min_order_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'free_delivery_from' => 'decimal:2',
        'estimated_time' => 'integer',
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
        if (empty($this->polygon) || !is_array($this->polygon)) {
            return false;
        }

        $points = $this->normalizePolygon($this->polygon);
        $n = count($points);
        if ($n < 3) {
            return false;
        }

        $inside = false;
        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            [$yi, $xi] = $points[$i];
            [$yj, $xj] = $points[$j];

            if ((($yi > $lat) !== ($yj > $lat)) &&
                ($lng < ($xj - $xi) * ($lat - $yi) / (($yj - $yi) ?: 1e-12) + $xi)) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    /**
     * Normalize polygon to list of [lat, lng] tuples.
     * Accepts both [[lat,lng], ...] and [{lat:..,lng:..}, ...].
     */
    private function normalizePolygon(array $polygon): array
    {
        $out = [];
        foreach ($polygon as $p) {
            if (is_array($p) && array_key_exists('lat', $p) && array_key_exists('lng', $p)) {
                $out[] = [(float) $p['lat'], (float) $p['lng']];
            } elseif (is_array($p) && isset($p[0], $p[1])) {
                $out[] = [(float) $p[0], (float) $p[1]];
            }
        }
        return $out;
    }

    public function getDeliveryFee(float $orderAmount): float
    {
        if ($this->free_delivery_from && $orderAmount >= $this->free_delivery_from) {
            return 0;
        }

        return (float) $this->delivery_fee;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
