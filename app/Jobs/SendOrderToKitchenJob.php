<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SendOrderToKitchenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Количество попыток выполнения задачи.
     */
    public int $tries = 3;

    /**
     * Таймаут задачи в секундах.
     */
    public int $timeout = 30;

    public function __construct(
        public readonly Order $order,
        public readonly Collection $items,
    ) {}

    /**
     * Выполнение задачи — отправка позиций заказа на кухню.
     */
    public function handle(): void
    {
        Log::info('Отправка заказа на кухню', [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'items_count' => $this->items->count(),
            'items' => $this->items->pluck('name', 'id')->toArray(),
        ]);

        // Обновляем статус позиций на "отправлен"
        foreach ($this->items as $item) {
            if ($item->status === 'pending') {
                $item->update(['status' => 'sent']);
            }
        }

        Log::info('Заказ успешно отправлен на кухню', [
            'order_id' => $this->order->id,
        ]);
    }

    /**
     * Обработка ошибки задачи.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Ошибка отправки заказа на кухню', [
            'order_id' => $this->order->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
