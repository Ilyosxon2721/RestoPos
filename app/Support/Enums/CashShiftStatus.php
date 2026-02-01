<?php

namespace App\Support\Enums;

enum CashShiftStatus: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Открыта',
            self::CLOSED => 'Закрыта',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::OPEN => 'green',
            self::CLOSED => 'gray',
        };
    }

    public function canAcceptPayments(): bool
    {
        return $this === self::OPEN;
    }
}
