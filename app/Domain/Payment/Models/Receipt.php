<?php

declare(strict_types=1);

namespace App\Domain\Payment\Models;

use App\Domain\Order\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    protected $fillable = [
        'order_id',
        'payment_id',
        'type',
        'number',
        'fiscal_number',
        'fiscal_sign',
        'amount',
        'status',
        'error_message',
        'receipt_data',
        'printed_at',
    ];

    protected function casts(): array
    {
        return [
            'receipt_data' => 'array',
            'amount' => 'decimal:2',
            'printed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
