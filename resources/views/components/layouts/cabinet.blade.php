<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'FORRIS POS - Кабинет' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-100" x-data="{
    sidebarOpen: window.innerWidth >= 1024,
    mobileMenuOpen: false,
    openMenu: '{{ request()->is('cabinet/menu*') ? 'menu' : (request()->is('cabinet/warehouse*') ? 'warehouse' : (request()->is('cabinet/marketing*', 'cabinet/customers*') ? 'marketing' : (request()->is('cabinet/staff*', 'cabinet/branches*', 'cabinet/roles*') ? 'access' : (request()->is('cabinet/settings*', 'cabinet/subscription*') ? 'settings' : '')))) }}'
}">
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
                   'w-64': sidebarOpen || mobileMenuOpen,
                   'w-20': !sidebarOpen && !mobileMenuOpen,
                   'translate-x-0': mobileMenuOpen,
                   '-translate-x-full lg:translate-x-0': !mobileMenuOpen
               }"
               @resize.window="if(window.innerWidth >= 1024) mobileMenuOpen = false">
            {{-- Логотип --}}
            <div class="flex h-16 items-center justify-between px-4 border-b border-gray-700">
                <a href="/cabinet/dashboard" class="flex items-center">
                    <x-logo variant="icon" color="light" size="md" x-show="!sidebarOpen" />
                    <x-logo variant="full" color="light" size="md" x-show="sidebarOpen" x-transition />
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
            <nav class="flex-1 overflow-y-auto py-4 space-y-0.5 px-3">

                {{-- Статистика --}}
                <a href="/cabinet/dashboard"
                   class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                          {{ request()->is('cabinet/dashboard*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                   title="Статистика">
                    <span class="flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </span>
                    <span class="ml-3 whitespace-nowrap" x-show="sidebarOpen || mobileMenuOpen" x-transition>Статистика</span>
                </a>

                {{-- Финансы --}}
                <a href="/cabinet/finance"
                   class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                          {{ request()->is('cabinet/finance*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                   title="Финансы">
                    <span class="flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <span class="ml-3 whitespace-nowrap" x-show="sidebarOpen || mobileMenuOpen" x-transition>Финансы</span>
                </a>

                {{-- ===== Меню (раскрывающийся) ===== --}}
                <div x-show="sidebarOpen || mobileMenuOpen">
                    <button @click="openMenu = openMenu === 'menu' ? '' : 'menu'"
                            class="w-full flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                                   {{ request()->is('cabinet/menu*') ? 'text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <span class="flex items-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            <span class="ml-3">Меню</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="openMenu === 'menu' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openMenu === 'menu'" x-collapse class="ml-5 pl-3 border-l border-gray-700 space-y-0.5 mt-0.5">
                        <a href="/cabinet/menu" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/menu') && !request()->is('cabinet/menu/*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Категории</a>
                        <a href="/cabinet/menu/items" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/menu/items*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Блюда</a>
                        <a href="/cabinet/menu/ingredients" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/menu/ingredients*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Ингредиенты</a>
                        <a href="/cabinet/menu/tech-cards" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/menu/tech-cards*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Тех. карты</a>
                        <a href="/cabinet/menu/taxes" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/menu/taxes*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Налоги</a>
                        <a href="/cabinet/menu/preparation-methods" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/menu/preparation-methods*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Методы приготовления</a>
                        <a href="/cabinet/menu/qr-menu" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/menu/qr-menu*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">QR-меню</a>
                    </div>
                </div>
                {{-- Меню (свёрнутый sidebar) --}}
                <a href="/cabinet/menu" x-show="!sidebarOpen"
                   class="flex items-center justify-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                          {{ request()->is('cabinet/menu*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                   title="Меню">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </a>

                {{-- ===== Склад (раскрывающийся) ===== --}}
                <div x-show="sidebarOpen || mobileMenuOpen">
                    <button @click="openMenu = openMenu === 'warehouse' ? '' : 'warehouse'"
                            class="w-full flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                                   {{ request()->is('cabinet/warehouse*') ? 'text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <span class="flex items-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span class="ml-3">Склад</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="openMenu === 'warehouse' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openMenu === 'warehouse'" x-collapse class="ml-5 pl-3 border-l border-gray-700 space-y-0.5 mt-0.5">
                        <a href="/cabinet/warehouse" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/warehouse') && !request()->is('cabinet/warehouse/*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Остатки</a>
                        <a href="/cabinet/warehouse/supplies" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/warehouse/supplies*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Поставки</a>
                        <a href="/cabinet/warehouse/production" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/warehouse/production*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Производства</a>
                        <a href="/cabinet/warehouse/transfers" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/warehouse/transfers*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Перемещения</a>
                        <a href="/cabinet/warehouse/write-offs" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/warehouse/write-offs*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Списания</a>
                        <a href="/cabinet/warehouse/movement" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/warehouse/movement*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Отчёт по движению</a>
                        <a href="/cabinet/warehouse/inventory" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/warehouse/inventory*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Инвентаризации</a>
                        <a href="/cabinet/warehouse/suppliers" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/warehouse/suppliers*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Поставщики</a>
                        <a href="/cabinet/warehouse/locations" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/warehouse/locations*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Склады</a>
                        <a href="/cabinet/warehouse/packaging" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/warehouse/packaging*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Фасовки</a>
                    </div>
                </div>
                {{-- Collapsed warehouse icon --}}
                <a href="/cabinet/warehouse" x-show="!sidebarOpen"
                   class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {{ request()->is('cabinet/warehouse*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                   title="Склад">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </a>

                {{-- ===== Интернет-магазин ===== --}}
                <a href="/cabinet/store" x-show="sidebarOpen || mobileMenuOpen"
                   class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                          {{ request()->is('cabinet/store*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span class="ml-3">Интернет-магазин</span>
                </a>
                <a href="/cabinet/store" x-show="!sidebarOpen"
                   class="flex items-center justify-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                          {{ request()->is('cabinet/store*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                   title="Интернет-магазин">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </a>

                {{-- ===== Маркетинг (раскрывающийся) ===== --}}
                <div x-show="sidebarOpen || mobileMenuOpen">
                    <button @click="openMenu = openMenu === 'marketing' ? '' : 'marketing'"
                            class="w-full flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                                   {{ request()->is('cabinet/marketing*', 'cabinet/customers*') ? 'text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <span class="flex items-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                            <span class="ml-3">Маркетинг</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="openMenu === 'marketing' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openMenu === 'marketing'" x-collapse class="ml-5 pl-3 border-l border-gray-700 space-y-0.5 mt-0.5">
                        <a href="/cabinet/customers" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/customers') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Клиенты</a>
                        <a href="/cabinet/marketing/groups" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/marketing/groups*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Группы клиентов</a>
                        <a href="/cabinet/marketing/loyalty" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/marketing/loyalty*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Программы лояльности</a>
                        <a href="/cabinet/marketing/promotions" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/marketing/promotions*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Акции</a>
                    </div>
                </div>
                <a href="/cabinet/customers" x-show="!sidebarOpen"
                   class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {{ request()->is('cabinet/marketing*', 'cabinet/customers*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                   title="Маркетинг">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                </a>

                {{-- ===== Доступ (раскрывающийся) ===== --}}
                <div x-show="sidebarOpen || mobileMenuOpen">
                    <button @click="openMenu = openMenu === 'access' ? '' : 'access'"
                            class="w-full flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                                   {{ request()->is('cabinet/staff*', 'cabinet/branches*', 'cabinet/roles*') ? 'text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <span class="flex items-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span class="ml-3">Доступ</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="openMenu === 'access' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openMenu === 'access'" x-collapse class="ml-5 pl-3 border-l border-gray-700 space-y-0.5 mt-0.5">
                        <a href="/cabinet/staff" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/staff*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Сотрудники</a>
                        <a href="/cabinet/roles" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/roles*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Должности</a>
                        <a href="/cabinet/branches" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/branches*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Заведения</a>
                    </div>
                </div>
                <a href="/cabinet/staff" x-show="!sidebarOpen"
                   class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {{ request()->is('cabinet/staff*', 'cabinet/branches*', 'cabinet/roles*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                   title="Доступ">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </a>

                {{-- ===== Настройки (раскрывающийся) ===== --}}
                <div x-show="sidebarOpen || mobileMenuOpen">
                    <button @click="openMenu = openMenu === 'settings' ? '' : 'settings'"
                            class="w-full flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                                   {{ request()->is('cabinet/settings*', 'cabinet/subscription*') ? 'text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <span class="flex items-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="ml-3">Настройки</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="openMenu === 'settings' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openMenu === 'settings'" x-collapse class="ml-5 pl-3 border-l border-gray-700 space-y-0.5 mt-0.5">
                        <a href="/cabinet/settings" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/settings') && !request()->is('cabinet/settings/*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Общие</a>
                        <a href="/cabinet/subscription" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/subscription*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Оплата подписки</a>
                        <a href="/cabinet/settings/orders" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/settings/orders*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Заказы</a>
                        <a href="/cabinet/settings/delivery" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/settings/delivery*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Доставка</a>
                        <a href="/cabinet/settings/tables" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/settings/tables*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Столы</a>
                        <a href="/cabinet/settings/security" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/settings/security*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Безопасность</a>
                        <a href="/cabinet/settings/receipt" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/settings/receipt*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Чек</a>
                        <a href="/cabinet/settings/taxes" class="block rounded-lg px-3 py-2 text-sm {{ request()->is('cabinet/settings/taxes*') ? 'text-indigo-400 font-medium' : 'text-gray-400 hover:text-white' }}">Налоги</a>
                    </div>
                </div>
                <a href="/cabinet/settings" x-show="!sidebarOpen"
                   class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {{ request()->is('cabinet/settings*', 'cabinet/subscription*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                   title="Настройки">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </a>

                {{-- Быстрый доступ --}}
                <div class="my-3 border-t border-gray-700"></div>
                <p class="px-3 mb-1 text-xs font-semibold uppercase tracking-wider text-gray-500" x-show="sidebarOpen || mobileMenuOpen" x-transition>Быстрый доступ</p>

                @php
                    $userBranches = auth()->user()?->organization?->branches()->where('is_active', true)->get() ?? collect();
                @endphp

                {{-- POS Терминал --}}
                @if($userBranches->count() > 1)
                    <div x-data="{ showBranches: false }" class="relative">
                        <button @click="showBranches = !showBranches"
                                class="w-full flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors text-indigo-300 hover:bg-gray-800 hover:text-indigo-200"
                                title="POS Терминал">
                            <span class="flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </span>
                            <span class="ml-3 whitespace-nowrap" x-show="sidebarOpen || mobileMenuOpen" x-transition>POS Терминал</span>
                            <svg class="w-4 h-4 ml-auto transition-transform" :class="showBranches ? 'rotate-180' : ''" x-show="sidebarOpen || mobileMenuOpen" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="showBranches" x-collapse class="ml-5 pl-3 border-l border-gray-700 space-y-0.5 mt-0.5">
                            @foreach($userBranches as $branch)
                                <a href="/cashier/terminal?branch={{ $branch->id }}" target="_blank"
                                   class="block rounded-lg px-3 py-2 text-sm text-gray-400 hover:text-white transition-colors">
                                    {{ $branch->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="/cashier/terminal" target="_blank"
                       class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors text-indigo-300 hover:bg-gray-800 hover:text-indigo-200"
                       title="POS Терминал">
                        <span class="flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </span>
                        <span class="ml-3 whitespace-nowrap" x-show="sidebarOpen || mobileMenuOpen" x-transition>POS Терминал</span>
                        <svg class="w-3.5 h-3.5 ml-auto flex-shrink-0" x-show="sidebarOpen || mobileMenuOpen" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                @endif

                {{-- Кухня --}}
                @if($userBranches->count() > 1)
                    <div x-data="{ showBranches: false }" class="relative">
                        <button @click="showBranches = !showBranches"
                                class="w-full flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors text-indigo-300 hover:bg-gray-800 hover:text-indigo-200"
                                title="Кухня">
                            <span class="flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/></svg>
                            </span>
                            <span class="ml-3 whitespace-nowrap" x-show="sidebarOpen || mobileMenuOpen" x-transition>Кухня</span>
                            <svg class="w-4 h-4 ml-auto transition-transform" :class="showBranches ? 'rotate-180' : ''" x-show="sidebarOpen || mobileMenuOpen" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="showBranches" x-collapse class="ml-5 pl-3 border-l border-gray-700 space-y-0.5 mt-0.5">
                            @foreach($userBranches as $branch)
                                <a href="/kitchen?branch={{ $branch->id }}" target="_blank"
                                   class="block rounded-lg px-3 py-2 text-sm text-gray-400 hover:text-white transition-colors">
                                    {{ $branch->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="/kitchen" target="_blank"
                       class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors text-indigo-300 hover:bg-gray-800 hover:text-indigo-200"
                       title="Кухня">
                        <span class="flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/></svg>
                        </span>
                        <span class="ml-3 whitespace-nowrap" x-show="sidebarOpen || mobileMenuOpen" x-transition>Кухня</span>
                        <svg class="w-3.5 h-3.5 ml-auto flex-shrink-0" x-show="sidebarOpen || mobileMenuOpen" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                @endif
            </nav>

            {{-- Нижняя часть --}}
            <div class="border-t border-gray-700 p-4" x-show="sidebarOpen || mobileMenuOpen" x-transition>
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
                            {{ auth()->user()?->organization?->name ?? 'Организация' }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center space-x-2 sm:space-x-4">
                    <span class="hidden sm:inline text-sm text-gray-600">
                        {{ auth()->user()?->first_name ?? '' }} {{ auth()->user()?->last_name ?? '' }}
                    </span>
                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-2 py-1.5 sm:px-3 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                            <svg class="h-4 w-4 text-gray-400 sm:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span class="hidden sm:inline">Выйти</span>
                        </button>
                    </form>
                </div>
            </header>

            {{-- Контент --}}
            <main class="flex-1 p-3 sm:p-4 lg:p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
