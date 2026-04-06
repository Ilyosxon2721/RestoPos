<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'FORRIS POS Admin' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-100" x-data="{ sidebarOpen: true, mobileMenuOpen: false }">
    <div class="flex min-h-screen">
        {{-- Мобильное затемнение --}}
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-black/50 lg:hidden"
             @click="mobileMenuOpen = false">
        </div>

        {{-- Боковая панель --}}
        <aside class="fixed inset-y-0 left-0 z-50 flex flex-col bg-gray-900 text-white transition-all duration-300 lg:relative"
               :class="{
                   'w-64': sidebarOpen,
                   'w-20': !sidebarOpen,
                   'translate-x-0': mobileMenuOpen || true,
                   '-translate-x-full lg:translate-x-0': !mobileMenuOpen
               }">
            {{-- Логотип --}}
            <div class="flex h-16 items-center justify-between px-4 border-b border-gray-700">
                <a href="/admin/dashboard" class="flex items-center">
                    <x-logo variant="icon" color="light" size="md" x-show="!sidebarOpen" />
                    <span x-show="sidebarOpen" x-transition class="flex items-center space-x-2">
                        <x-logo variant="full" color="light" size="md" />
                        <span class="text-xs font-medium text-red-400 bg-red-400/10 px-2 py-0.5 rounded-full">Admin</span>
                    </span>
                </a>
                <button @click="sidebarOpen = !sidebarOpen" class="hidden lg:block text-gray-400 hover:text-white">
                    <svg x-show="sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                    <svg x-show="!sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            {{-- Навигация --}}
            <nav class="flex-1 overflow-y-auto py-4 space-y-1 px-3">
                @php
                    $links = [
                        ['url' => '/admin/dashboard', 'label' => 'Панель управления', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/></svg>'],
                        ['url' => '/admin/organizations', 'label' => 'Организации', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'],
                        ['url' => '/admin/plans', 'label' => 'Тарифы', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>'],
                        ['url' => '/admin/subscriptions', 'label' => 'Подписки', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>'],
                        ['url' => '/admin/payments', 'label' => 'Платежи', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>'],
                        ['url' => '/admin/settings', 'label' => 'Настройки', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'],
                    ];
                @endphp

                @foreach ($links as $link)
                    <a href="{{ $link['url'] }}"
                       class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                              {{ request()->is(ltrim($link['url'], '/') . '*') ? 'bg-red-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                       title="{{ $link['label'] }}">
                        <span class="flex-shrink-0">{!! $link['icon'] !!}</span>
                        <span class="ml-3 whitespace-nowrap" x-show="sidebarOpen" x-transition>{{ $link['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            {{-- Нижняя часть --}}
            <div class="border-t border-gray-700 p-4" x-show="sidebarOpen" x-transition>
                <p class="text-xs text-gray-500">&copy; {{ date('Y') }} FORRIS POS</p>
            </div>
        </aside>

        {{-- Основной контент --}}
        <div class="flex flex-1 flex-col min-w-0">
            {{-- Верхняя панель --}}
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b bg-white px-4 shadow-sm">
                <div class="flex items-center space-x-4">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-500 hover:text-gray-700 lg:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <button @click="sidebarOpen = !sidebarOpen" class="hidden lg:block text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div class="hidden sm:block">
                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                            Super Admin
                        </span>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">
                        {{ auth('platform')->user()?->name ?? 'Администратор' }}
                    </span>
                    <form method="POST" action="/admin/logout">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                            <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Выйти
                        </button>
                    </form>
                </div>
            </header>

            {{-- Контент --}}
            <main class="flex-1 p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
