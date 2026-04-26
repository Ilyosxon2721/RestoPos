<?php

namespace App\Support\Enums;

enum CourierStatus: string
{
    case Available = 'available';
    case Busy = 'busy';
    case Offline = 'offline';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Доступен',
            self::Busy => 'Занят',
            self::Offline => 'Не в сети',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::Busy => 'warning',
            self::Offline => 'secondary',
        };
    }
}
