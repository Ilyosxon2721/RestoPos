<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
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
        public readonly int $userId,
        public readonly string $title,
        public readonly string $message,
        public readonly array $data = [],
    ) {}

    /**
     * Выполнение задачи — создание уведомления в БД.
     */
    public function handle(): void
    {
        Log::info('Отправка уведомления пользователю', [
            'user_id' => $this->userId,
            'title' => $this->title,
        ]);

        DB::table('notifications')->insert([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\\Notifications\\SystemNotification',
            'notifiable_type' => 'App\\Domain\\Auth\\Models\\User',
            'notifiable_id' => $this->userId,
            'data' => json_encode([
                'title' => $this->title,
                'message' => $this->message,
                'data' => $this->data,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info('Уведомление отправлено', [
            'user_id' => $this->userId,
            'title' => $this->title,
        ]);
    }

    /**
     * Обработка ошибки задачи.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Ошибка отправки уведомления', [
            'user_id' => $this->userId,
            'title' => $this->title,
            'error' => $exception->getMessage(),
        ]);
    }
}
