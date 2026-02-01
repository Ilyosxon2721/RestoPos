# Этап 1.1: Установка зависимостей

Установи все необходимые пакеты для RestoPOS.

## Задачи:

### 1. Composer пакеты

```bash
# Основные
composer require livewire/livewire
composer require livewire/flux
composer require laravel/sanctum
composer require spatie/laravel-permission
composer require spatie/laravel-medialibrary
composer require spatie/laravel-activitylog
composer require spatie/laravel-settings
composer require maatwebsite/excel
composer require barryvdh/laravel-dompdf
composer require intervention/image

# Разработка
composer require --dev laravel/pint
composer require --dev larastan/larastan
composer require --dev barryvdh/laravel-ide-helper
composer require --dev pestphp/pest
composer require --dev pestphp/pest-plugin-laravel
```

### 2. NPM пакеты

```bash
npm install -D tailwindcss postcss autoprefixer
npm install -D @tailwindcss/forms @tailwindcss/typography
npm install alpinejs
npm install apexcharts
```

### 3. Публикация конфигов

```bash
php artisan vendor:publish --provider="Livewire\LivewireServiceProvider"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"
php artisan vendor:publish --tag="sanctum-config"
```

### 4. Настройка Tailwind

Создай файл `tailwind.config.js`:

```javascript
import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/livewire/flux-pro/stubs/**/*.blade.php',
        './vendor/livewire/flux/stubs/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#fef2f2',
                    100: '#fee2e2',
                    200: '#fecaca',
                    300: '#fca5a5',
                    400: '#f87171',
                    500: '#ef4444',
                    600: '#dc2626',
                    700: '#b91c1c',
                    800: '#991b1b',
                    900: '#7f1d1d',
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
};
```

### 5. Настройка Vite

Обнови `vite.config.js`:

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
```

### 6. CSS файл

Обнови `resources/css/app.css`:

```css
@import 'tailwindcss';

@source '../../vendor/livewire/flux-pro/stubs';
@source '../../vendor/livewire/flux/stubs';

@layer base {
    :root {
        --font-family: 'Inter', sans-serif;
    }
    
    body {
        @apply antialiased;
    }
}
```

### 7. JS файл

Обнови `resources/js/app.js`:

```javascript
import './bootstrap';

import Alpine from 'alpinejs';
import Flux from '@livewire/flux';

Alpine.plugin(Flux);

window.Alpine = Alpine;
Alpine.start();
```

### 8. Конфигурация приложения

Создай `config/restopos.php`:

```php
<?php

return [
    'name' => env('APP_NAME', 'RestoPOS'),
    
    'multi_tenant' => env('RESTOPOS_MULTI_TENANT', true),
    
    'currency' => [
        'code' => env('RESTOPOS_DEFAULT_CURRENCY', 'UZS'),
        'symbol' => env('RESTOPOS_CURRENCY_SYMBOL', 'сум'),
        'decimals' => 0,
    ],
    
    'locale' => [
        'default' => 'ru',
        'available' => ['ru', 'uz', 'en'],
    ],
    
    'fiscal' => [
        'enabled' => env('RESTOPOS_FISCAL_ENABLED', false),
        'provider' => env('RESTOPOS_FISCAL_PROVIDER'),
    ],
    
    'features' => [
        'delivery' => true,
        'reservations' => true,
        'qr_menu' => true,
        'loyalty' => true,
        'kitchen_display' => true,
    ],
];
```

### 9. IDE Helper

```bash
php artisan ide-helper:generate
php artisan ide-helper:meta
```

## Проверка:

После выполнения запусти:

```bash
npm run build
php artisan optimize:clear
```

Этап 1.1 завершён!
