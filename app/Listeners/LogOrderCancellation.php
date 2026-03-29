<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderCancelled;
use Illuminate\Support\Facades\Log;

class LogOrderCancellation
{
    public function handle(OrderCancelled $event): void
    {
        Log::warning('Заказ отменён', [
            'order_id' => $event->order->id,
            'order_number' => $event->order->order_number,
            'branch_id' => $event->order->branch_id,
            'total_amount' => $event->order->total_amount,
            'reason' => $event->reason,
        ]);
    }
}
