<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Support\Facades\Log;

class LogOrderCreated
{
    public function handle(OrderCreated $event): void
    {
        Log::info('Заказ создан', [
            'order_id' => $event->order->id,
            'order_number' => $event->order->order_number,
            'branch_id' => $event->order->branch_id,
            'table_id' => $event->order->table_id,
            'waiter_id' => $event->order->waiter_id,
            'type' => $event->order->type?->value,
            'source' => $event->order->source?->value,
        ]);
    }
}
