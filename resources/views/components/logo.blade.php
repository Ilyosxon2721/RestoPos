@props([
    'variant' => 'full',       {{-- full | icon | text --}}
    'color' => 'default',      {{-- default | light | dark | white --}}
    'size' => 'md',            {{-- sm | md | lg | xl --}}
])

@php
    $sizes = [
        'sm' => ['icon' => 'w-6 h-6', 'text' => 'text-sm', 'gap' => 'space-x-1.5'],
        'md' => ['icon' => 'w-8 h-8', 'text' => 'text-lg', 'gap' => 'space-x-2'],
        'lg' => ['icon' => 'w-10 h-10', 'text' => 'text-xl', 'gap' => 'space-x-2.5'],
        'xl' => ['icon' => 'w-14 h-14', 'text' => 'text-3xl', 'gap' => 'space-x-3'],
    ];

    $colors = [
        'default' => ['icon' => '#6366F1', 'accent' => '#818CF8', 'text' => 'text-gray-900', 'sub' => 'text-indigo-500'],
        'light'   => ['icon' => '#818CF8', 'accent' => '#A5B4FC', 'text' => 'text-indigo-400', 'sub' => 'text-indigo-300'],
        'dark'    => ['icon' => '#4F46E5', 'accent' => '#6366F1', 'text' => 'text-gray-800', 'sub' => 'text-indigo-600'],
        'white'   => ['icon' => '#FFFFFF', 'accent' => '#E0E7FF', 'text' => 'text-white', 'sub' => 'text-indigo-200'],
    ];

    $s = $sizes[$size] ?? $sizes['md'];
    $c = $colors[$color] ?? $colors['default'];
@endphp

@if ($variant === 'icon' || $variant === 'full')
<span class="inline-flex items-center {{ $variant === 'full' ? $s['gap'] : '' }}" {{ $attributes }}>
    {{-- Иконка: стилизованная "F" в шестиугольнике --}}
    <svg class="{{ $s['icon'] }} flex-shrink-0" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
        {{-- Шестиугольный фон --}}
        <path d="M20 2L36.66 11V29L20 38L3.34 29V11L20 2Z" fill="{{ $c['icon'] }}" opacity="0.15"/>
        <path d="M20 4L34.66 12V28L20 36L5.34 28V12L20 4Z" fill="{{ $c['icon'] }}" opacity="0.08"/>
        {{-- Внутренний шестиугольник --}}
        <path d="M20 6L32.66 13V27L20 34L7.34 27V13L20 6Z" fill="{{ $c['icon'] }}"/>
        {{-- Буква F --}}
        <path d="M14 12H26V15.5H18V18.5H24.5V22H18V28H14V12Z" fill="white"/>
        {{-- Акцентная точка --}}
        <circle cx="28" cy="12" r="2.5" fill="{{ $c['accent'] }}"/>
    </svg>

    @if ($variant === 'full')
        <span class="font-bold tracking-wide {{ $s['text'] }} {{ $c['text'] }}">
            FORRIS<span class="{{ $c['sub'] }} font-semibold"> POS</span>
        </span>
    @endif
</span>
@endif

@if ($variant === 'text')
<span class="font-bold tracking-wide {{ $s['text'] }} {{ $c['text'] }}" {{ $attributes }}>
    FORRIS<span class="{{ $c['sub'] }} font-semibold"> POS</span>
</span>
@endif
