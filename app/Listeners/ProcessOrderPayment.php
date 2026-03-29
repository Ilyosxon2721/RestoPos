<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Jobs\ProcessPaymentJob;
use Illuminate\Support\Facades\Log;

class ProcessOrderPayment
{
    public function handle(OrderPaid $event): void
    {
        Log::info('Заказ оплачен, запуск обработки', [
            'order_id' => $event->order->id,
            'payment_id' => $event->payment->id,
            'amount' => $event->payment->amount,
        ]);

        // Обновляем статус оплаты заказа
        $event->order->updatePaymentStatus();

        // Запускаем пост-обработку платежа через очередь
        ProcessPaymentJob::dispatch($event->payment);
    }
}
