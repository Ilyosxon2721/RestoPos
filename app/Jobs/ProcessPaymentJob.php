<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Models\Receipt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Количество попыток выполнения задачи.
     */
    public int $tries = 3;

    /**
     * Таймаут задачи в секундах.
     */
    public int $timeout = 60;

    public function __construct(
        public readonly Payment $payment,
    ) {}

    /**
     * Выполнение задачи — пост-обработка платежа.
     */
    public function handle(): void
    {
        Log::info('Обработка платежа', [
            'payment_id' => $this->payment->id,
            'order_id' => $this->payment->order_id,
            'amount' => $this->payment->amount,
        ]);

        $order = $this->payment->order;

        // Обновляем статус оплаты заказа
        if ($order) {
            $order->updatePaymentStatus();
        }

        // Генерируем чек
        if ($this->payment->isCompleted() && $order) {
            Receipt::create([
                'order_id' => $order->id,
                'payment_id' => $this->payment->id,
                'type' => 'sale',
                'number' => 'R-' . now()->format('YmdHis') . '-' . $this->payment->id,
                'amount' => $this->payment->amount,
                'status' => 'created',
            ]);
        }

        Log::info('Платёж обработан успешно', [
            'payment_id' => $this->payment->id,
            'order_payment_status' => $order?->payment_status?->value,
        ]);
    }

    /**
     * Обработка ошибки задачи.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Ошибка обработки платежа', [
            'payment_id' => $this->payment->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
