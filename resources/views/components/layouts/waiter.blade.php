<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'FORRIS POS' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-100">
    <div class="flex min-h-screen flex-col">
        {{-- Верхняя панель --}}
        <header class="sticky top-0 z-30 flex h-14 items-center justify-between border-b bg-white px-4 shadow-sm">
            <div class="flex items-center space-x-2">
                <x-logo variant="full" color="default" size="sm" />
                <span class="h-5 w-px bg-gray-300"></span>
                <span class="text-sm text-gray-500 truncate max-w-[150px]">
                    {{ auth()->user()?->branch?->name ?? 'Филиал' }}
                </span>
            </div>

            <div class="flex items-center space-x-3">
                <span class="text-sm text-gray-600 hidden sm:inline">
                    {{ auth()->user()?->name ?? 'Официант' }}
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white p-1.5 text-sm text-gray-500 hover:bg-gray-50 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </header>

        {{-- Контент --}}
        <main class="flex-1 pb-20 overflow-y-auto">
            {{ $slot }}
        </main>

        {{-- Нижняя навигация (мобильное приложение) --}}
        <nav class="fixed bottom-0 inset-x-0 z-30 border-t bg-white shadow-[0_-2px_10px_rgba(0,0,0,0.08)]">
            <div class="flex items-center justify-around">
                @php
                    $bottomLinks = [
                        [
                            'url' => '/waiter/tables',
                            'label' => 'Столы',
                            'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>',
                        ],
                        [
                            'url' => '/waiter/orders',
                            'label' => 'Заказы',
                            'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>',
                        ],
                        [
                            'url' => '/waiter/notifications',
                            'label' => 'Уведомления',
                            'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>',
                        ],
                        [
                            'url' => '/waiter/profile',
                            'label' => 'Профиль',
                            'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
                        ],
                    ];
                @endphp

                @foreach ($bottomLinks as $link)
                    <a href="{{ $link['url'] }}"
                       class="flex flex-col items-center justify-center py-2 px-3 min-w-[64px] transition-colors
                              {{ request()->is(ltrim($link['url'], '/') . '*') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                        <span class="flex-shrink-0">{!! $link['icon'] !!}</span>
                        <span class="mt-1 text-xs font-medium">{{ $link['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </nav>
    </div>

    @livewireScripts
</body>
</html>
