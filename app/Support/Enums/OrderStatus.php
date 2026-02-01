<?php

namespace App\Support\Enums;

enum OrderStatus: string
{
    case NEW = 'new';
    case ACCEPTED = 'accepted';
    case PREPARING = 'preparing';
    case READY = 'ready';
    case SERVED = 'served';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'Новый',
            self::ACCEPTED => 'Принят',
            self::PREPARING => 'Готовится',
            self::READY => 'Готов',
            self::SERVED => 'Подан',
            self::COMPLETED => 'Завершён',
            self::CANCELLED => 'Отменён',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEW => 'blue',
            self::ACCEPTED => 'cyan',
            self::PREPARING => 'orange',
            self::READY => 'green',
            self::SERVED => 'purple',
            self::COMPLETED => 'gray',
            self::CANCELLED => 'red',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        $allowedTransitions = match ($this) {
            self::NEW => [self::ACCEPTED, self::CANCELLED],
            self::ACCEPTED => [self::PREPARING, self::CANCELLED],
            self::PREPARING => [self::READY, self::CANCELLED],
            self::READY => [self::SERVED, self::CANCELLED],
            self::SERVED => [self::COMPLETED],
            self::COMPLETED => [],
            self::CANCELLED => [],
        };

        return in_array($newStatus, $allowedTransitions);
    }

    public static function activeStatuses(): array
    {
        return [
            self::NEW,
            self::ACCEPTED,
            self::PREPARING,
            self::READY,
            self::SERVED,
        ];
    }
}
