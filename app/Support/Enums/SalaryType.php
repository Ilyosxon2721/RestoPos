<?php

namespace App\Support\Enums;

enum SalaryType: string
{
    case HOURLY = 'hourly';
    case MONTHLY = 'monthly';
    case PERCENT = 'percent';
    case MIXED = 'mixed';

    public function label(): string
    {
        return match ($this) {
            self::HOURLY => 'Почасовая',
            self::MONTHLY => 'Оклад',
            self::PERCENT => 'Процент с продаж',
            self::MIXED => 'Смешанная',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::HOURLY => 'Оплата за каждый отработанный час',
            self::MONTHLY => 'Фиксированный ежемесячный оклад',
            self::PERCENT => 'Процент от суммы продаж сотрудника',
            self::MIXED => 'Оклад + процент с продаж',
        };
    }
}
