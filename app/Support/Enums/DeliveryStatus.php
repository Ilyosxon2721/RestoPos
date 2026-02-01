<?php

namespace App\Support\Enums;

enum DeliveryStatus: string
{
    case PENDING = 'pending';
    case ASSIGNED = 'assigned';
    case PICKED_UP = 'picked_up';
    case IN_TRANSIT = 'in_transit';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Ожидает курьера',
            self::ASSIGNED => 'Назначен курьер',
            self::PICKED_UP => 'Забран',
            self::IN_TRANSIT => 'В пути',
            self::DELIVERED => 'Доставлен',
            self::FAILED => 'Не доставлен',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'orange',
            self::ASSIGNED => 'blue',
            self::PICKED_UP => 'cyan',
            self::IN_TRANSIT => 'purple',
            self::DELIVERED => 'green',
            self::FAILED => 'red',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        $allowedTransitions = match ($this) {
            self::PENDING => [self::ASSIGNED, self::FAILED],
            self::ASSIGNED => [self::PICKED_UP, self::PENDING, self::FAILED],
            self::PICKED_UP => [self::IN_TRANSIT, self::FAILED],
            self::IN_TRANSIT => [self::DELIVERED, self::FAILED],
            self::DELIVERED => [],
            self::FAILED => [self::PENDING],
        };

        return in_array($newStatus, $allowedTransitions);
    }

    public function isActive(): bool
    {
        return in_array($this, [self::PENDING, self::ASSIGNED, self::PICKED_UP, self::IN_TRANSIT]);
    }
}
