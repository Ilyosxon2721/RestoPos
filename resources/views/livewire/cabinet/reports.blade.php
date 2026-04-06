<div>
    {{-- Заголовок --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Отчёты</h1>
            <p class="text-sm text-gray-500 mt-1">Аналитика и отчёты по вашему бизнесу</p>
        </div>

        {{-- Переключатель периодов --}}
        <div class="inline-flex rounded-lg border border-gray-200 bg-white shadow-sm">
            @php
                $periods = [
                    'today' => 'Сегодня',
                    'week' => 'Неделя',
                    'month' => 'Месяц',
                ];
            @endphp
            @foreach ($periods as $value => $label)
                <button wire:click="$set('period', '{{ $value }}')"
                        class="px-4 py-2 text-sm font-medium transition-colors first:rounded-l-lg last:rounded-r-lg
                               {{ $period === $value ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-50' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Вкладки отчётов --}}
    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
            @php
                $tabs = [
                    'sales' => ['label' => 'Продажи', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                    'products' => ['label' => 'Топ продуктов', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                    'staff' => ['label' => 'Сотрудники', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                    'payments' => ['label' => 'Способы оплаты', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                ];
            @endphp
            @foreach ($tabs as $key => $tab)
                <button wire:click="$set('activeTab', '{{ $key }}')"
                        class="flex items-center gap-2 whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors
                               {{ $activeTab === $key
                                   ? 'border-indigo-600 text-indigo-600'
                                   : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/>
                    </svg>
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- Контент вкладок --}}
    <div wire:loading.class="opacity-50 pointer-events-none" class="transition-opacity">

        {{-- ============================================ --}}
        {{-- ПРОДАЖИ --}}
        {{-- ============================================ --}}
        @if ($activeTab === 'sales')
            {{-- Карточки основных метрик --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                {{-- Выручка --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Выручка</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">
                                {{ number_format($this->revenue, 0, '.', ' ') }}
                                <span class="text-sm font-normal text-gray-500">сум</span>
                            </p>
                        </div>
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Количество заказов --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Заказов</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">
                                {{ number_format($this->ordersCount, 0, '.', ' ') }}
                            </p>
                        </div>
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Средний чек --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Средний чек</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">
                                {{ number_format($this->averageCheck, 0, '.', ' ') }}
                                <span class="text-sm font-normal text-gray-500">сум</span>
                            </p>
                        </div>
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-orange-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Гостей --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Гостей</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">
                                {{ number_format($this->guestsCount, 0, '.', ' ') }}
                            </p>
                        </div>
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Пустое состояние --}}
            @if ($this->ordersCount === 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">Нет данных</h3>
                    <p class="mt-1 text-sm text-gray-500">За выбранный период нет завершённых заказов.</p>
                </div>
            @endif

        {{-- ============================================ --}}
        {{-- ТОП ПРОДУКТОВ --}}
        {{-- ============================================ --}}
        @elseif ($activeTab === 'products')
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Топ-10 продуктов</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Самые продаваемые позиции за период: {{ $this->periodLabel }}</p>
                </div>

                @if ($this->topProducts->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Продукт</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Количество</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Выручка</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Доля</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                    $maxRevenue = $this->topProducts->max('total_revenue');
                                    $totalProductsRevenue = $this->topProducts->sum('total_revenue');
                                @endphp
                                @foreach ($this->topProducts as $index => $product)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                            {{ number_format((float) $product->total_quantity, 0, '.', ' ') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <span class="text-sm font-medium text-gray-900">{{ number_format((float) $product->total_revenue, 0, '.', ' ') }}</span>
                                            <span class="text-xs text-gray-500">сум</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <div class="w-20 bg-gray-200 rounded-full h-2">
                                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $maxRevenue > 0 ? round(($product->total_revenue / $maxRevenue) * 100) : 0 }}%"></div>
                                                </div>
                                                <span class="text-sm text-gray-600 w-12 text-right">
                                                    {{ $totalProductsRevenue > 0 ? number_format(($product->total_revenue / $totalProductsRevenue) * 100, 1) : 0 }}%
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900">Нет данных</h3>
                        <p class="mt-1 text-sm text-gray-500">За выбранный период нет проданных товаров.</p>
                    </div>
                @endif
            </div>

        {{-- ============================================ --}}
        {{-- СОТРУДНИКИ --}}
        {{-- ============================================ --}}
        @elseif ($activeTab === 'staff')
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Эффективность сотрудников</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Заказы и выручка по официантам за период: {{ $this->periodLabel }}</p>
                </div>

                @if ($this->staffPerformance->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сотрудник</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Заказов</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Выручка</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Средний чек</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php $maxStaffRevenue = $this->staffPerformance->max('total_revenue'); @endphp
                                @foreach ($this->staffPerformance as $index => $staff)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                                    <span class="text-xs font-medium text-indigo-700">
                                                        {{ $staff->waiter?->user ? mb_substr($staff->waiter->user->first_name, 0, 1) . mb_substr($staff->waiter->user->last_name, 0, 1) : '?' }}
                                                    </span>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $staff->waiter?->user ? $staff->waiter->user->first_name . ' ' . $staff->waiter->user->last_name : 'Неизвестный' }}
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $staff->waiter?->position ?? '' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                            {{ number_format((int) $staff->orders_count, 0, '.', ' ') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div>
                                                <span class="text-sm font-medium text-gray-900">{{ number_format((float) $staff->total_revenue, 0, '.', ' ') }}</span>
                                                <span class="text-xs text-gray-500">сум</span>
                                            </div>
                                            {{-- Прогресс-бар --}}
                                            <div class="mt-1 w-24 bg-gray-200 rounded-full h-1.5 ml-auto">
                                                <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $maxStaffRevenue > 0 ? round(($staff->total_revenue / $maxStaffRevenue) * 100) : 0 }}%"></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <span class="text-sm text-gray-900">{{ number_format((float) $staff->avg_check, 0, '.', ' ') }}</span>
                                            <span class="text-xs text-gray-500">сум</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900">Нет данных</h3>
                        <p class="mt-1 text-sm text-gray-500">За выбранный период нет данных по сотрудникам.</p>
                    </div>
                @endif
            </div>

        {{-- ============================================ --}}
        {{-- СПОСОБЫ ОПЛАТЫ --}}
        {{-- ============================================ --}}
        @elseif ($activeTab === 'payments')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Карточки по типам оплаты --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Разбивка по способам оплаты</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Период: {{ $this->periodLabel }}</p>
                    </div>

                    @if ($this->paymentMethods->isNotEmpty())
                        <div class="divide-y divide-gray-200">
                            @php
                                $paymentColors = [
                                    'cash' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'bar' => 'bg-green-500'],
                                    'card' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'bar' => 'bg-blue-500'],
                                    'transfer' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'bar' => 'bg-purple-500'],
                                    'bonus' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'bar' => 'bg-yellow-500'],
                                    'credit' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'bar' => 'bg-red-500'],
                                    'other' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'bar' => 'bg-gray-500'],
                                ];
                            @endphp
                            @foreach ($this->paymentMethods as $method)
                                @php
                                    $typeKey = $method->paymentMethod?->type?->value ?? 'other';
                                    $colors = $paymentColors[$typeKey] ?? $paymentColors['other'];
                                    $percentage = $this->paymentMethodsTotal > 0
                                        ? ($method->total_amount / $this->paymentMethodsTotal) * 100
                                        : 0;
                                @endphp
                                <div class="px-6 py-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colors['bg'] }} {{ $colors['text'] }}">
                                                {{ $method->paymentMethod?->name ?? 'Неизвестный' }}
                                            </span>
                                            <span class="text-sm text-gray-500">{{ $method->payments_count }} платежей</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-sm font-semibold text-gray-900">{{ number_format((float) $method->total_amount, 0, '.', ' ') }}</span>
                                            <span class="text-xs text-gray-500">сум</span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="{{ $colors['bar'] }} h-2 rounded-full transition-all duration-300" style="width: {{ round($percentage) }}%"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1 text-right">{{ number_format($percentage, 1) }}%</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900">Нет данных</h3>
                            <p class="mt-1 text-sm text-gray-500">За выбранный период нет платежей.</p>
                        </div>
                    @endif
                </div>

                {{-- Сводная карточка --}}
                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-sm font-medium text-gray-500">Общая сумма платежей</h3>
                        <p class="text-3xl font-bold text-gray-900 mt-2">
                            {{ number_format($this->paymentMethodsTotal, 0, '.', ' ') }}
                            <span class="text-base font-normal text-gray-500">сум</span>
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-sm font-medium text-gray-500">Количество платежей</h3>
                        <p class="text-3xl font-bold text-gray-900 mt-2">
                            {{ number_format($this->paymentMethods->sum('payments_count'), 0, '.', ' ') }}
                        </p>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-sm font-medium text-gray-500">Способов оплаты</h3>
                        <p class="text-3xl font-bold text-gray-900 mt-2">
                            {{ $this->paymentMethods->count() }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
