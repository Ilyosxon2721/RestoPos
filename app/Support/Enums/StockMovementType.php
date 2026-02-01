<?php

namespace App\Support\Enums;

enum StockMovementType: string
{
    case SUPPLY = 'supply';
    case SALE = 'sale';
    case WRITE_OFF = 'write_off';
    case TRANSFER_IN = 'transfer_in';
    case TRANSFER_OUT = 'transfer_out';
    case PRODUCTION = 'production';
    case INVENTORY = 'inventory';
    case RETURN = 'return';

    public function label(): string
    {
        return match ($this) {
            self::SUPPLY => 'Поступление',
            self::SALE => 'Продажа',
            self::WRITE_OFF => 'Списание',
            self::TRANSFER_IN => 'Приход (перемещение)',
            self::TRANSFER_OUT => 'Расход (перемещение)',
            self::PRODUCTION => 'Производство',
            self::INVENTORY => 'Инвентаризация',
            self::RETURN => 'Возврат',
        };
    }

    public function isIncoming(): bool
    {
        return in_array($this, [
            self::SUPPLY,
            self::TRANSFER_IN,
            self::RETURN,
            self::INVENTORY, // может быть как + так и -
        ]);
    }

    public function isOutgoing(): bool
    {
        return in_array($this, [
            self::SALE,
            self::WRITE_OFF,
            self::TRANSFER_OUT,
            self::PRODUCTION,
        ]);
    }
}
