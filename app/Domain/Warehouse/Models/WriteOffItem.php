<?php

declare(strict_types=1);

namespace App\Domain\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WriteOffItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'write_off_id',
        'ingredient_id',
        'quantity',
        'cost_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'cost_price' => 'decimal:4',
        ];
    }

    public function writeOff(): BelongsTo
    {
        return $this->belongsTo(WriteOff::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
