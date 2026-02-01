<?php

namespace App\Support\Enums;

enum ProductType: string
{
    case DISH = 'dish';
    case DRINK = 'drink';
    case PRODUCT = 'product';
    case SERVICE = 'service';
    case SEMI_FINISHED = 'semi_finished';

    public function label(): string
    {
        return match ($this) {
            self::DISH => 'Блюдо',
            self::DRINK => 'Напиток',
            self::PRODUCT => 'Товар',
            self::SERVICE => 'Услуга',
            self::SEMI_FINISHED => 'Полуфабрикат',
        };
    }

    public function requiresPreparation(): bool
    {
        return in_array($this, [self::DISH, self::DRINK, self::SEMI_FINISHED]);
    }

    public function isForSale(): bool
    {
        return in_array($this, [self::DISH, self::DRINK, self::PRODUCT, self::SERVICE]);
    }
}
