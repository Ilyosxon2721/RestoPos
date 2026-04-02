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
                <a href="/warehouse-panel/stock" class="flex items-center space-x-2 min-w-0">
                    <span class="text-2xl flex-shrink-0">
                        <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </span>
                    <div x-show="sidebarOpen" x-transition class="min-w-0">
                        <span class="text-lg font-bold tracking-wide text-amber-400 block truncate">FORRIS POS</span>
                        <span class="text-xs text-gray-400 block truncate">Склад</span>
                    </div>
                </a>
                <button @click="sidebarOpen = !sidebarOpen" class="hidden lg:block text-gray-400 hover:text-white flex-shrink-0">
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
                        ['url' => '/warehouse-panel/stock', 'label' => 'Остатки', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>'],
                        ['url' => '/warehouse-panel/supplies', 'label' => 'Приёмка', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'],
                        ['url' => '/warehouse-panel/write-off', 'label' => 'Списание', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>'],
                        ['url' => '/warehouse-panel/inventory', 'label' => 'Инвентаризация', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>'],
                        ['url' => '/warehouse-panel/suppliers', 'label' => 'Поставщики', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>'],
                    ];
                @endphp

                @foreach ($links as $link)
                    <a href="{{ $link['url'] }}"
                       class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                              {{ request()->is(ltrim($link['url'], '/') . '*') ? 'bg-amber-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
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
                        <span class="text-sm font-medium text-gray-700">
                            {{ auth()->user()?->branch?->name ?? 'Склад' }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">
                        {{ auth()->user()?->name ?? 'Кладовщик' }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
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
