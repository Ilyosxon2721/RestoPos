<?php

namespace App\Support\Enums;

enum PaymentStatus: string
{
    case UNPAID = 'unpaid';
    case PARTIAL = 'partial';
    case PAID = 'paid';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::UNPAID => 'Не оплачен',
            self::PARTIAL => 'Частично оплачен',
            self::PAID => 'Оплачен',
            self::REFUNDED => 'Возврат',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::UNPAID => 'red',
            self::PARTIAL => 'orange',
            self::PAID => 'green',
            self::REFUNDED => 'gray',
        };
    }
}
