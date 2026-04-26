<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PaymentReceived;
use Illuminate\Support\Facades\Log;

class UpdateOrderPaymentStatus
{
    public function handle(PaymentReceived $event): void
    {
        $payment = $event->payment;
        $order = $payment->order;

        if (!$order) {
            Log::warning('Получен платёж без привязки к заказу', [
                'payment_id' => $payment->id,
            ]);

            return;
        }

        Log::info('Обновление статуса оплаты заказа', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
        ]);

        $order->updatePaymentStatus();
    }
}
