<?php

declare(strict_types=1);

namespace App\Domain\Store\Models;

use App\Domain\Menu\Models\Product;
use App\Domain\Organization\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreSettings extends Model
{
    protected $fillable = [
        'organization_id',
        'store_name',
        'slug',
        'description',
        'logo',
        'cover_image',
        'primary_color',
        'currency',
        'drink_of_day_product_id',
        'delivery_enabled',
        'pickup_enabled',
        'min_order_amount',
        'phone',
        'instagram',
        'telegram',
        'working_hours_text',
        'is_active',
    ];

    protected $casts = [
        'delivery_enabled' => 'boolean',
        'pickup_enabled' => 'boolean',
        'min_order_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function drinkOfDay(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'drink_of_day_product_id');
    }

    /**
     * Получить URL магазина.
     */
    public function getStoreUrl(): string
    {
        return url("/shop/{$this->slug}");
    }
}
