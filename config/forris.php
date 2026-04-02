<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Мультитенантность
    |--------------------------------------------------------------------------
    */
    'multi_tenant' => env('FORRIS_POS_MULTI_TENANT', true),
    'base_domain' => env('FORRIS_POS_BASE_DOMAIN', 'pos.forris.uz'),
    'admin_subdomain' => env('FORRIS_POS_ADMIN_SUBDOMAIN', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | Валюта по умолчанию
    |--------------------------------------------------------------------------
    */
    'currency' => [
        'code' => env('FORRIS_POS_CURRENCY', 'UZS'),
        'symbol' => env('FORRIS_POS_CURRENCY_SYMBOL', 'сум'),
        'decimal_places' => env('FORRIS_POS_CURRENCY_DECIMALS', 0),
        'thousand_separator' => ' ',
        'decimal_separator' => ',',
    ],

    /*
    |--------------------------------------------------------------------------
    | Локализация
    |--------------------------------------------------------------------------
    */
    'locale' => [
        'default' => env('FORRIS_POS_LOCALE', 'ru'),
        'available' => ['ru', 'uz', 'en'],
        'timezone' => env('FORRIS_POS_TIMEZONE', 'Asia/Tashkent'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Формат нумерации заказов
    |--------------------------------------------------------------------------
    */
    'order' => [
        'number_format' => 'Ymd-{sequence}',
        'sequence_pad' => 4,
        'auto_accept' => env('FORRIS_POS_AUTO_ACCEPT_ORDERS', false),
        'default_type' => 'dine_in',
        'default_source' => 'pos',
    ],

    /*
    |--------------------------------------------------------------------------
    | Налоги и сервисный сбор
    |--------------------------------------------------------------------------
    */
    'tax' => [
        'enabled' => env('FORRIS_POS_TAX_ENABLED', false),
        'rate' => env('FORRIS_POS_TAX_RATE', 0),
        'included_in_price' => env('FORRIS_POS_TAX_INCLUDED', true),
    ],

    'service_charge' => [
        'enabled' => env('FORRIS_POS_SERVICE_CHARGE_ENABLED', false),
        'percent' => env('FORRIS_POS_SERVICE_CHARGE_PERCENT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Кассовые смены
    |--------------------------------------------------------------------------
    */
    'cash_shift' => [
        'require_open_shift' => env('FORRIS_POS_REQUIRE_CASH_SHIFT', true),
        'auto_close_hours' => env('FORRIS_POS_AUTO_CLOSE_SHIFT_HOURS', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | KDS (Kitchen Display System)
    |--------------------------------------------------------------------------
    */
    'kds' => [
        'refresh_interval' => env('FORRIS_POS_KDS_REFRESH', 5), // секунды
        'warning_minutes' => env('FORRIS_POS_KDS_WARNING_MINUTES', 15),
        'critical_minutes' => env('FORRIS_POS_KDS_CRITICAL_MINUTES', 25),
    ],

    /*
    |--------------------------------------------------------------------------
    | Печать
    |--------------------------------------------------------------------------
    */
    'printing' => [
        'enabled' => env('FORRIS_POS_PRINTING_ENABLED', false),
        'auto_print_receipt' => env('FORRIS_POS_AUTO_PRINT_RECEIPT', false),
        'auto_print_kitchen' => env('FORRIS_POS_AUTO_PRINT_KITCHEN', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Склад
    |--------------------------------------------------------------------------
    */
    'warehouse' => [
        'track_stock' => env('FORRIS_POS_TRACK_STOCK', true),
        'low_stock_threshold' => env('FORRIS_POS_LOW_STOCK_THRESHOLD', 10),
        'allow_negative_stock' => env('FORRIS_POS_ALLOW_NEGATIVE_STOCK', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Программа лояльности
    |--------------------------------------------------------------------------
    */
    'loyalty' => [
        'enabled' => env('FORRIS_POS_LOYALTY_ENABLED', true),
        'default_earn_percent' => env('FORRIS_POS_LOYALTY_EARN_PERCENT', 3),
        'max_pay_percent' => env('FORRIS_POS_LOYALTY_MAX_PAY_PERCENT', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Функциональные модули
    |--------------------------------------------------------------------------
    */
    'features' => [
        'delivery' => env('FORRIS_POS_FEATURE_DELIVERY', true),
        'reservations' => env('FORRIS_POS_FEATURE_RESERVATIONS', true),
        'kds' => env('FORRIS_POS_FEATURE_KDS', true),
        'warehouse' => env('FORRIS_POS_FEATURE_WAREHOUSE', true),
        'loyalty' => env('FORRIS_POS_FEATURE_LOYALTY', true),
        'staff_management' => env('FORRIS_POS_FEATURE_STAFF', true),
        'reports' => env('FORRIS_POS_FEATURE_REPORTS', true),
    ],

];
