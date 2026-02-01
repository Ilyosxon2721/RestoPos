<?php

namespace App\Support\Enums;

enum PaymentMethodType: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case TRANSFER = 'transfer';
    case BONUS = 'bonus';
    case CREDIT = 'credit';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Наличные',
            self::CARD => 'Карта',
            self::TRANSFER => 'Перевод',
            self::BONUS => 'Бонусы',
            self::CREDIT => 'В долг',
            self::OTHER => 'Другое',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CASH => 'pi-money-bill',
            self::CARD => 'pi-credit-card',
            self::TRANSFER => 'pi-mobile',
            self::BONUS => 'pi-star',
            self::CREDIT => 'pi-clock',
            self::OTHER => 'pi-question',
        };
    }

    public function requiresChange(): bool
    {
        return $this === self::CASH;
    }

    public function isFiscal(): bool
    {
        return in_array($this, [self::CASH, self::CARD, self::TRANSFER]);
    }
}
