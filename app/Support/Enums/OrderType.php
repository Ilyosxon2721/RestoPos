<?php

namespace App\Support\Enums;

enum OrderType: string
{
    case DINE_IN = 'dine_in';
    case TAKEAWAY = 'takeaway';
    case DELIVERY = 'delivery';
    case PREORDER = 'preorder';

    public function label(): string
    {
        return match ($this) {
            self::DINE_IN => 'В зале',
            self::TAKEAWAY => 'Навынос',
            self::DELIVERY => 'Доставка',
            self::PREORDER => 'Предзаказ',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DINE_IN => 'pi-home',
            self::TAKEAWAY => 'pi-shopping-bag',
            self::DELIVERY => 'pi-truck',
            self::PREORDER => 'pi-calendar',
        };
    }

    public function requiresTable(): bool
    {
        return $this === self::DINE_IN;
    }

    public function requiresDeliveryAddress(): bool
    {
        return $this === self::DELIVERY;
    }
}
