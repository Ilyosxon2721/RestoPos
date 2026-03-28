<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Количество попыток выполнения задачи.
     */
    public int $tries = 2;

    /**
     * Таймаут задачи в секундах.
     */
    public int $timeout = 300;

    public function __construct(
        public readonly string $type,
        public readonly array $params,
        public readonly int $userId,
    ) {}

    /**
     * Выполнение задачи — генерация отчёта.
     */
    public function handle(): void
    {
        Log::info('Начало генерации отчёта', [
            'type' => $this->type,
            'params' => $this->params,
            'user_id' => $this->userId,
        ]);

        $reportData = $this->generateReportData();

        // Сохраняем отчёт в файл
        $filename = sprintf(
            'reports/%s/%s_%s.json',
            $this->type,
            now()->format('Y-m-d_H-i-s'),
            $this->userId
        );

        Storage::put($filename, json_encode($reportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Уведомляем пользователя о готовности отчёта
        SendNotificationJob::dispatch(
            $this->userId,
            'Отчёт готов',
            "Отчёт \"{$this->type}\" успешно сгенерирован.",
            ['file' => $filename, 'type' => $this->type]
        );

        Log::info('Отчёт успешно сгенерирован', [
            'type' => $this->type,
            'file' => $filename,
        ]);
    }

    /**
     * Генерация данных отчёта в зависимости от типа.
     */
    private function generateReportData(): array
    {
        return match ($this->type) {
            'sales' => $this->generateSalesReport(),
            'orders' => $this->generateOrdersReport(),
            'products' => $this->generateProductsReport(),
            default => [
                'type' => $this->type,
                'params' => $this->params,
                'generated_at' => now()->toIso8601String(),
                'data' => [],
            ],
        };
    }

    /**
     * Отчёт по продажам.
     */
    private function generateSalesReport(): array
    {
        $branchId = $this->params['branch_id'] ?? null;
        $dateFrom = $this->params['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $this->params['date_to'] ?? now()->toDateString();

        $query = \App\Domain\Order\Models\Order::query()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $orders = $query->get();

        return [
            'type' => 'sales',
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'generated_at' => now()->toIso8601String(),
            'data' => [
                'total_orders' => $orders->count(),
                'total_revenue' => $orders->sum('total_amount'),
                'average_order' => $orders->count() > 0
                    ? round($orders->sum('total_amount') / $orders->count(), 2)
                    : 0,
            ],
        ];
    }

    /**
     * Отчёт по заказам.
     */
    private function generateOrdersReport(): array
    {
        return [
            'type' => 'orders',
            'params' => $this->params,
            'generated_at' => now()->toIso8601String(),
            'data' => [],
        ];
    }

    /**
     * Отчёт по продуктам.
     */
    private function generateProductsReport(): array
    {
        return [
            'type' => 'products',
            'params' => $this->params,
            'generated_at' => now()->toIso8601String(),
            'data' => [],
        ];
    }

    /**
     * Обработка ошибки задачи.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Ошибка генерации отчёта', [
            'type' => $this->type,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);

        // Уведомляем пользователя об ошибке
        SendNotificationJob::dispatch(
            $this->userId,
            'Ошибка генерации отчёта',
            "Не удалось сгенерировать отчёт \"{$this->type}\". Попробуйте позже.",
            ['type' => $this->type, 'error' => $exception->getMessage()]
        );
    }
}
