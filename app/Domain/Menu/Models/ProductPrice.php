<?php

namespace App\Domain\Menu\Models;

use App\Support\Traits\BelongsToOrganization;
use App\Support\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPrice extends Model
{
    use BelongsToOrganization, BelongsToBranch;

    protected $fillable = [
        'organization_id',
        'product_id',
        'branch_id',
        'price',
        'old_price',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if product is on sale.
     */
    public function isOnSale(): bool
    {
        return $this->old_price !== null && $this->old_price > $this->price;
    }

    /**
     * Get discount percentage.
     */
    public function getDiscountPercent(): float
    {
        if (!$this->isOnSale() || $this->old_price <= 0) {
            return 0;
        }

        return round((($this->old_price - $this->price) / $this->old_price) * 100);
    }
}
