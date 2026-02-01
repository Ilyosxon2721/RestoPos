<?php

namespace App\Support\Enums;

enum OrderSource: string
{
    case POS = 'pos';
    case WEBSITE = 'website';
    case APP = 'app';
    case AGGREGATOR = 'aggregator';
    case PHONE = 'phone';
    case QR = 'qr';

    public function label(): string
    {
        return match ($this) {
            self::POS => 'POS терминал',
            self::WEBSITE => 'Веб-сайт',
            self::APP => 'Мобильное приложение',
            self::AGGREGATOR => 'Агрегатор',
            self::PHONE => 'Телефон',
            self::QR => 'QR-код',
        };
    }
}
