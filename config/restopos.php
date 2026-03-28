<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Мультитенантность
    |--------------------------------------------------------------------------
    */
    'multi_tenant' => env('RESTOPOS_MULTI_TENANT', true),

    /*
    |--------------------------------------------------------------------------
    | Валюта по умолчанию
    |--------------------------------------------------------------------------
    */
    'currency' => [
        'code' => env('RESTOPOS_CURRENCY', 'UZS'),
        'symbol' => env('RESTOPOS_CURRENCY_SYMBOL', 'сум'),
        'decimal_places' => env('RESTOPOS_CURRENCY_DECIMALS', 0),
        'thousand_separator' => ' ',
        'decimal_separator' => ',',
    ],

    /*
    |--------------------------------------------------------------------------
    | Локализация
    |--------------------------------------------------------------------------
    */
    'locale' => [
        'default' => env('RESTOPOS_LOCALE', 'ru'),
        'available' => ['ru', 'uz', 'en'],
        'timezone' => env('RESTOPOS_TIMEZONE', 'Asia/Tashkent'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Формат нумерации заказов
    |--------------------------------------------------------------------------
    */
    'order' => [
        'number_format' => 'Ymd-{sequence}',
        'sequence_pad' => 4,
        'auto_accept' => env('RESTOPOS_AUTO_ACCEPT_ORDERS', false),
        'default_type' => 'dine_in',
        'default_source' => 'pos',
    ],

    /*
    |--------------------------------------------------------------------------
    | Налоги и сервисный сбор
    |--------------------------------------------------------------------------
    */
    'tax' => [
        'enabled' => env('RESTOPOS_TAX_ENABLED', false),
        'rate' => env('RESTOPOS_TAX_RATE', 0),
        'included_in_price' => env('RESTOPOS_TAX_INCLUDED', true),
    ],

    'service_charge' => [
        'enabled' => env('RESTOPOS_SERVICE_CHARGE_ENABLED', false),
        'percent' => env('RESTOPOS_SERVICE_CHARGE_PERCENT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Кассовые смены
    |--------------------------------------------------------------------------
    */
    'cash_shift' => [
        'require_open_shift' => env('RESTOPOS_REQUIRE_CASH_SHIFT', true),
        'auto_close_hours' => env('RESTOPOS_AUTO_CLOSE_SHIFT_HOURS', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | KDS (Kitchen Display System)
    |--------------------------------------------------------------------------
    */
    'kds' => [
        'refresh_interval' => env('RESTOPOS_KDS_REFRESH', 5), // секунды
        'warning_minutes' => env('RESTOPOS_KDS_WARNING_MINUTES', 15),
        'critical_minutes' => env('RESTOPOS_KDS_CRITICAL_MINUTES', 25),
    ],

    /*
    |--------------------------------------------------------------------------
    | Печать
    |--------------------------------------------------------------------------
    */
    'printing' => [
        'enabled' => env('RESTOPOS_PRINTING_ENABLED', false),
        'auto_print_receipt' => env('RESTOPOS_AUTO_PRINT_RECEIPT', false),
        'auto_print_kitchen' => env('RESTOPOS_AUTO_PRINT_KITCHEN', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Склад
    |--------------------------------------------------------------------------
    */
    'warehouse' => [
        'track_stock' => env('RESTOPOS_TRACK_STOCK', true),
        'low_stock_threshold' => env('RESTOPOS_LOW_STOCK_THRESHOLD', 10),
        'allow_negative_stock' => env('RESTOPOS_ALLOW_NEGATIVE_STOCK', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Программа лояльности
    |--------------------------------------------------------------------------
    */
    'loyalty' => [
        'enabled' => env('RESTOPOS_LOYALTY_ENABLED', true),
        'default_earn_percent' => env('RESTOPOS_LOYALTY_EARN_PERCENT', 3),
        'max_pay_percent' => env('RESTOPOS_LOYALTY_MAX_PAY_PERCENT', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Функциональные модули
    |--------------------------------------------------------------------------
    */
    'features' => [
        'delivery' => env('RESTOPOS_FEATURE_DELIVERY', true),
        'reservations' => env('RESTOPOS_FEATURE_RESERVATIONS', true),
        'kds' => env('RESTOPOS_FEATURE_KDS', true),
        'warehouse' => env('RESTOPOS_FEATURE_WAREHOUSE', true),
        'loyalty' => env('RESTOPOS_FEATURE_LOYALTY', true),
        'staff_management' => env('RESTOPOS_FEATURE_STAFF', true),
        'reports' => env('RESTOPOS_FEATURE_REPORTS', true),
    ],

];
