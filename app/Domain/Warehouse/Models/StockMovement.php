<?php

namespace App\Domain\Warehouse\Models;

use App\Support\Traits\HasUuid;
use App\Support\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasUuid;

    protected $fillable = [
        'stock_id',
        'type',
        'quantity',
        'cost_price',
        'reference_type',
        'reference_id',
        'notes',
    ];

    protected $casts = [
        'type' => StockMovementType::class,
        'quantity' => 'decimal:3',
        'cost_price' => 'decimal:2',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }
}
