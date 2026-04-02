<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FORRIS POS — облачная POS-система для автоматизации ресторанов и кафе">
    <title>FORRIS POS — Облачная POS-система для ресторанов</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-900 antialiased" x-data="{ mobileMenu: false }">

    {{-- Header --}}
    <header class="fixed top-0 inset-x-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="flex items-center space-x-2">
                    <span class="text-2xl">🍽️</span>
                    <span class="text-xl font-bold text-indigo-600">FORRIS POS</span>
                </a>

                <nav class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition">Возможности</a>
                    <a href="#pricing" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition">Тарифы</a>
                    <a href="#contacts" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition">Контакты</a>
                </nav>

                <div class="hidden md:flex items-center space-x-4">
                    <a href="/login" class="text-sm font-medium text-gray-700 hover:text-indigo-600 transition">Войти</a>
                    <a href="/register" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm">
                        Попробовать бесплатно
                    </a>
                </div>

                <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Мобильное меню --}}
        <div x-show="mobileMenu" x-transition class="md:hidden bg-white border-t px-4 py-4 space-y-3">
            <a href="#features" class="block text-sm font-medium text-gray-600">Возможности</a>
            <a href="#pricing" class="block text-sm font-medium text-gray-600">Тарифы</a>
            <a href="#contacts" class="block text-sm font-medium text-gray-600">Контакты</a>
            <hr class="my-2">
            <a href="/login" class="block text-sm font-medium text-gray-700">Войти</a>
            <a href="/register" class="block text-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg">Попробовать бесплатно</a>
        </div>
    </header>

    {{-- Hero --}}
    <section class="pt-32 pb-20 bg-gradient-to-br from-indigo-50 via-white to-purple-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-900 leading-tight">
                    Облачная POS-система
                    <span class="text-indigo-600">для ресторанов</span>
                </h1>
                <p class="mt-6 text-lg sm:text-xl text-gray-600 leading-relaxed">
                    Автоматизируйте ваш ресторан, кафе или магазин. Управление заказами, меню, складом, персоналом и финансами — всё в одной системе.
                </p>
                <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="/register" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                        Начать бесплатно
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    <a href="#features" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 border-2 border-gray-200 text-gray-700 font-semibold rounded-xl hover:border-indigo-300 hover:text-indigo-600 transition">
                        Узнать больше
                    </a>
                </div>
                <p class="mt-4 text-sm text-gray-500">14 дней бесплатно. Без привязки карты.</p>
            </div>

            <div class="mt-16 relative">
                <div class="bg-gradient-to-b from-indigo-100 to-indigo-50 rounded-2xl p-8 shadow-2xl shadow-indigo-100 max-w-4xl mx-auto">
                    <div class="bg-white rounded-xl shadow-lg p-6 flex items-center justify-center min-h-[300px]">
                        <div class="text-center text-gray-400">
                            <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <p class="text-lg font-medium">Интерфейс FORRIS POS</p>
                            <p class="text-sm">POS-терминал, управление заказами, аналитика</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">Всё для управления рестораном</h2>
                <p class="mt-4 text-lg text-gray-600">Полный набор инструментов в одной системе</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                {{-- POS Терминал --}}
                <div class="group p-6 rounded-2xl border border-gray-100 hover:border-indigo-200 hover:shadow-lg transition-all duration-300">
                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-indigo-200 transition">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">POS Терминал</h3>
                    <p class="text-gray-600">Быстрый приём заказов, интуитивный интерфейс для кассиров и официантов. Работает на любом устройстве.</p>
                </div>

                {{-- Управление меню --}}
                <div class="group p-6 rounded-2xl border border-gray-100 hover:border-emerald-200 hover:shadow-lg transition-all duration-300">
                    <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-emerald-200 transition">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Управление меню</h3>
                    <p class="text-gray-600">Категории, модификаторы, техкарты. Гибкое управление ценами и стоп-листом.</p>
                </div>

                {{-- Склад --}}
                <div class="group p-6 rounded-2xl border border-gray-100 hover:border-amber-200 hover:shadow-lg transition-all duration-300">
                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-amber-200 transition">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Склад и учёт</h3>
                    <p class="text-gray-600">FIFO учёт, инвентаризация, приёмка поставок. Автоматическое списание ингредиентов.</p>
                </div>

                {{-- KDS Кухня --}}
                <div class="group p-6 rounded-2xl border border-gray-100 hover:border-red-200 hover:shadow-lg transition-all duration-300">
                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-red-200 transition">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10s4-1 5-3c1.5 2.5 3 3.5 3 5a4 4 0 01-4 4z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">KDS Кухня</h3>
                    <p class="text-gray-600">Экран для поваров в реальном времени. Отслеживание статуса каждого блюда от заказа до подачи.</p>
                </div>

                {{-- CRM --}}
                <div class="group p-6 rounded-2xl border border-gray-100 hover:border-purple-200 hover:shadow-lg transition-all duration-300">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-purple-200 transition">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">CRM и лояльность</h3>
                    <p class="text-gray-600">Бонусная программа, скидки, история заказов клиентов. Повышайте возвращаемость гостей.</p>
                </div>

                {{-- Аналитика --}}
                <div class="group p-6 rounded-2xl border border-gray-100 hover:border-blue-200 hover:shadow-lg transition-all duration-300">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-blue-200 transition">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Аналитика</h3>
                    <p class="text-gray-600">Отчёты по продажам, P&L, ABC-анализ товаров. Принимайте решения на основе данных.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Как это работает --}}
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">Начните за 3 шага</h2>
                <p class="mt-4 text-lg text-gray-600">Быстрая настройка без программистов</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-600 text-white rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-4">1</div>
                    <h3 class="text-xl font-semibold mb-2">Регистрация</h3>
                    <p class="text-gray-600">Создайте аккаунт за 1 минуту. Без привязки карты и скрытых платежей.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-600 text-white rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-4">2</div>
                    <h3 class="text-xl font-semibold mb-2">Настройка</h3>
                    <p class="text-gray-600">Добавьте меню, столы, персонал. Система подскажет на каждом этапе.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-600 text-white rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-4">3</div>
                    <h3 class="text-xl font-semibold mb-2">Начало работы</h3>
                    <p class="text-gray-600">Принимайте заказы, управляйте бизнесом и наблюдайте за ростом прибыли.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Тарифы --}}
    <section id="pricing" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">Тарифы</h2>
                <p class="mt-4 text-lg text-gray-600">Выберите подходящий план для вашего бизнеса</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                {{-- Стартовый --}}
                <div class="rounded-2xl border border-gray-200 p-8 hover:shadow-lg transition-shadow">
                    <h3 class="text-lg font-semibold text-gray-900">Стартовый</h3>
                    <p class="text-sm text-gray-500 mt-1">Для небольших заведений</p>
                    <div class="mt-6">
                        <span class="text-4xl font-bold text-gray-900">99 000</span>
                        <span class="text-gray-500 ml-1">сум/мес</span>
                    </div>
                    <ul class="mt-8 space-y-3">
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            1 филиал
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            3 пользователя
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            100 товаров
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            POS + KDS
                        </li>
                    </ul>
                    <a href="/register" class="mt-8 block text-center px-6 py-3 border-2 border-indigo-600 text-indigo-600 font-semibold rounded-xl hover:bg-indigo-50 transition">
                        Выбрать
                    </a>
                </div>

                {{-- Бизнес --}}
                <div class="rounded-2xl border-2 border-indigo-600 p-8 relative shadow-xl">
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 bg-indigo-600 text-white text-xs font-bold rounded-full uppercase tracking-wide">
                        Популярный
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Бизнес</h3>
                    <p class="text-sm text-gray-500 mt-1">Для растущих заведений</p>
                    <div class="mt-6">
                        <span class="text-4xl font-bold text-gray-900">249 000</span>
                        <span class="text-gray-500 ml-1">сум/мес</span>
                    </div>
                    <ul class="mt-8 space-y-3">
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            3 филиала
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            10 пользователей
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            500 товаров
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Склад и аналитика
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            CRM и лояльность
                        </li>
                    </ul>
                    <a href="/register" class="mt-8 block text-center px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                        Выбрать
                    </a>
                </div>

                {{-- Премиум --}}
                <div class="rounded-2xl border border-gray-200 p-8 hover:shadow-lg transition-shadow">
                    <h3 class="text-lg font-semibold text-gray-900">Премиум</h3>
                    <p class="text-sm text-gray-500 mt-1">Для сетей и франшиз</p>
                    <div class="mt-6">
                        <span class="text-4xl font-bold text-gray-900">499 000</span>
                        <span class="text-gray-500 ml-1">сум/мес</span>
                    </div>
                    <ul class="mt-8 space-y-3">
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Безлимит филиалов
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Безлимит пользователей
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Безлимит товаров
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Приоритетная поддержка
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            API доступ
                        </li>
                    </ul>
                    <a href="/register" class="mt-8 block text-center px-6 py-3 border-2 border-indigo-600 text-indigo-600 font-semibold rounded-xl hover:bg-indigo-50 transition">
                        Выбрать
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-20 bg-gradient-to-r from-indigo-600 to-purple-700">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white">Готовы начать?</h2>
            <p class="mt-4 text-lg text-indigo-100">Попробуйте FORRIS POS бесплатно 14 дней и убедитесь, что это именно то, что нужно вашему бизнесу.</p>
            <a href="/register" class="mt-8 inline-flex items-center px-8 py-4 bg-white text-indigo-600 font-bold rounded-xl hover:bg-gray-50 transition shadow-lg text-lg">
                Попробовать бесплатно
                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </section>

    {{-- Footer --}}
    <footer id="contacts" class="bg-gray-900 text-gray-400 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-2 mb-4">
                        <span class="text-2xl">🍽️</span>
                        <span class="text-xl font-bold text-white">FORRIS POS</span>
                    </div>
                    <p class="text-sm leading-relaxed max-w-md">
                        Облачная POS-система для автоматизации ресторанов, кафе и магазинов. Простое управление бизнесом из любой точки мира.
                    </p>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Продукт</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#features" class="hover:text-white transition">Возможности</a></li>
                        <li><a href="#pricing" class="hover:text-white transition">Тарифы</a></li>
                        <li><a href="#" class="hover:text-white transition">API документация</a></li>
                        <li><a href="#" class="hover:text-white transition">Обновления</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Контакты</h4>
                    <ul class="space-y-2 text-sm">
                        <li>info@forris.uz</li>
                        <li>+998 71 123 45 67</li>
                        <li>Ташкент, Узбекистан</li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-gray-800 text-center text-sm">
                <p>&copy; {{ date('Y') }} FORRIS POS. Все права защищены.</p>
            </div>
        </div>
    </footer>

</body>
</html>
