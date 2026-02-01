<?php

namespace App\Support\Enums;

enum OrderItemStatus: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case PREPARING = 'preparing';
    case READY = 'ready';
    case SERVED = 'served';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Ожидает',
            self::SENT => 'Отправлен',
            self::PREPARING => 'Готовится',
            self::READY => 'Готов',
            self::SERVED => 'Подан',
            self::CANCELLED => 'Отменён',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::SENT => 'blue',
            self::PREPARING => 'orange',
            self::READY => 'green',
            self::SERVED => 'purple',
            self::CANCELLED => 'red',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        $allowedTransitions = match ($this) {
            self::PENDING => [self::SENT, self::CANCELLED],
            self::SENT => [self::PREPARING, self::CANCELLED],
            self::PREPARING => [self::READY, self::CANCELLED],
            self::READY => [self::SERVED],
            self::SERVED => [],
            self::CANCELLED => [],
        };

        return in_array($newStatus, $allowedTransitions);
    }
}
