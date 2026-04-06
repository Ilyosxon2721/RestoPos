@props([
    'variant' => 'full',       {{-- full | icon | text --}}
    'color' => 'default',      {{-- default | light | dark | white --}}
    'size' => 'md',            {{-- sm | md | lg | xl --}}
])

@php
    // Размеры: пропорциональная шкала
    $sizes = [
        'sm' => ['icon' => 'w-7 h-7', 'text' => 'text-base', 'gap' => 'gap-2'],
        'md' => ['icon' => 'w-9 h-9', 'text' => 'text-xl', 'gap' => 'gap-2.5'],
        'lg' => ['icon' => 'w-11 h-11', 'text' => 'text-2xl', 'gap' => 'gap-3'],
        'xl' => ['icon' => 'w-14 h-14', 'text' => 'text-3xl', 'gap' => 'gap-3.5'],
    ];

    // Цветовые схемы:
    // bg       — фон контейнера иконки
    // letter   — цвет буквы F
    // text     — цвет "FORRIS" в словесном знаке
    // sub      — цвет "POS" в словесном знаке
    $colors = [
        'default' => ['bg' => '#1E1B4B', 'letter' => '#FFFFFF', 'text' => 'text-gray-900',  'sub' => 'text-indigo-600'],
        'light'   => ['bg' => '#4338CA', 'letter' => '#FFFFFF', 'text' => 'text-white',      'sub' => 'text-indigo-300'],
        'dark'    => ['bg' => '#0F0D2E', 'letter' => '#FFFFFF', 'text' => 'text-gray-800',   'sub' => 'text-indigo-700'],
        'white'   => ['bg' => '#FFFFFF', 'letter' => '#1E1B4B', 'text' => 'text-white',      'sub' => 'text-indigo-200'],
    ];

    $s = $sizes[$size] ?? $sizes['md'];
    $c = $colors[$color] ?? $colors['default'];
@endphp

@if ($variant === 'icon' || $variant === 'full')
<span class="inline-flex items-center {{ $variant === 'full' ? $s['gap'] : '' }}" {{ $attributes }}>
    {{--
        Иконка: буква F в скруглённом квадрате.

        Конструкция:
        — Контейнер: 40×40, rx=10 (squircle-пропорция)
        — F построена по золотому сечению:
          Высота F = 22, точка пересечения средней перекладины = 8.5 от верха (0.386 ≈ 1/φ)
          Верхняя перекладина: 17.5 ед. → средняя: 13 ед. (соотношение ≈ φ)
          Основание ножки = 5.5 ед. (визуальная устойчивость)

        Философия:
        «FORRIS» восходит к лат. «foris» — дверь, врата.
        Буква F — это портал: вертикальная опора и две горизонтали
        образуют архитектурный проём. Вход в экосистему управления бизнесом.
    --}}
    <svg class="{{ $s['icon'] }} flex-shrink-0" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect width="40" height="40" rx="10" fill="{{ $c['bg'] }}"/>
        <path d="M11 9H28.5V14H16.5V17.5H24V22H16.5V31H11V9Z" fill="{{ $c['letter'] }}"/>
    </svg>

    @if ($variant === 'full')
    <span class="font-semibold tracking-tight leading-none {{ $s['text'] }} {{ $c['text'] }}">FORRIS<span class="font-normal {{ $c['sub'] }}"> POS</span></span>
    @endif
</span>
@endif

@if ($variant === 'text')
<span class="font-semibold tracking-tight leading-none {{ $s['text'] }} {{ $c['text'] }}" {{ $attributes }}>FORRIS<span class="font-normal {{ $c['sub'] }}"> POS</span></span>
@endif
