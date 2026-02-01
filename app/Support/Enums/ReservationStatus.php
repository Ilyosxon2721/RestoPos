<?php

namespace App\Support\Enums;

enum ReservationStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case SEATED = 'seated';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case NO_SHOW = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Ожидает подтверждения',
            self::CONFIRMED => 'Подтверждено',
            self::SEATED => 'Гость за столом',
            self::COMPLETED => 'Завершено',
            self::CANCELLED => 'Отменено',
            self::NO_SHOW => 'Не пришёл',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'orange',
            self::CONFIRMED => 'blue',
            self::SEATED => 'green',
            self::COMPLETED => 'gray',
            self::CANCELLED => 'red',
            self::NO_SHOW => 'red',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::PENDING, self::CONFIRMED, self::SEATED]);
    }
}
