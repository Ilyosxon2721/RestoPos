<?php

namespace App\Support\Enums;

enum TableStatus: string
{
    case FREE = 'free';
    case OCCUPIED = 'occupied';
    case RESERVED = 'reserved';
    case UNAVAILABLE = 'unavailable';

    public function label(): string
    {
        return match ($this) {
            self::FREE => 'Свободен',
            self::OCCUPIED => 'Занят',
            self::RESERVED => 'Забронирован',
            self::UNAVAILABLE => 'Недоступен',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::FREE => 'green',
            self::OCCUPIED => 'red',
            self::RESERVED => 'orange',
            self::UNAVAILABLE => 'gray',
        };
    }

    public function canAcceptNewOrder(): bool
    {
        return $this === self::FREE;
    }

    public function canBeReserved(): bool
    {
        return $this === self::FREE;
    }
}
