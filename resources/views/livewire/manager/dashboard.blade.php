<div>
    {{-- Заголовок --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Панель управления</h1>
        <p class="mt-1 text-sm text-gray-500">Обзор за сегодня, {{ now()->format('d.m.Y') }}</p>
    </div>

    {{-- Карточки статистики --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        {{-- Заказы сегодня --}}
        <div class="overflow-hidden rounded-xl bg-white shadow-sm border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-lg bg-emerald-50 p-3">
                        <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500">Заказы сегодня</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $this->todayOrders }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Выручка --}}
        <div class="overflow-hidden rounded-xl bg-white shadow-sm border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-lg bg-green-50 p-3">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500">Выручка</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($this->todayRevenue, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">сум</span></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Открытые заказы --}}
        <div class="overflow-hidden rounded-xl bg-white shadow-sm border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-lg bg-emerald-50 p-3">
                        <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500">Открытые заказы</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $this->openOrders }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Занятые столы --}}
        <div class="overflow-hidden rounded-xl bg-white shadow-sm border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-lg bg-green-50 p-3">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500">Занятые столы</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $this->occupiedTables }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Последние заказы --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-sm border border-gray-200">
        <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Последние заказы</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Номер</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Сумма</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Время</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($this->recentOrders as $order)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                #{{ $order->order_number }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @php
                                    $statusStyles = match($order->status) {
                                        'new' => 'bg-emerald-100 text-emerald-700',
                                        'accepted' => 'bg-green-100 text-green-700',
                                        'preparing' => 'bg-yellow-100 text-yellow-700',
                                        'ready' => 'bg-emerald-100 text-emerald-700',
                                        'completed' => 'bg-green-100 text-green-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                    $statusLabel = match($order->status) {
                                        'new' => 'Новый',
                                        'accepted' => 'Принят',
                                        'preparing' => 'Готовится',
                                        'ready' => 'Готов',
                                        'completed' => 'Завершён',
                                        'cancelled' => 'Отменён',
                                        default => $order->status,
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusStyles }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                {{ number_format((float) $order->total_amount, 0, ',', ' ') }} сум
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                {{ $order->created_at?->format('d.m.Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">
                                Заказов пока нет
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
