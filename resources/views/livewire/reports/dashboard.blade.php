<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Отчёты</h1>

    {{-- Выбор периода --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex rounded-lg overflow-hidden border border-gray-300">
                <button wire:click="$set('period', 'today')"
                    class="px-4 py-2 text-sm font-medium {{ $period === 'today' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    Сегодня
                </button>
                <button wire:click="$set('period', 'week')"
                    class="px-4 py-2 text-sm font-medium border-l {{ $period === 'week' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    Неделя
                </button>
                <button wire:click="$set('period', 'month')"
                    class="px-4 py-2 text-sm font-medium border-l {{ $period === 'month' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    Месяц
                </button>
                <button wire:click="$set('period', 'custom')"
                    class="px-4 py-2 text-sm font-medium border-l {{ $period === 'custom' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    Произвольный
                </button>
            </div>

            @if($period === 'custom')
                <div class="flex items-center gap-2">
                    <input type="date" wire:model="dateFrom"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500" />
                    <span class="text-gray-500">&mdash;</span>
                    <input type="date" wire:model="dateTo"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500" />
                    <button wire:click="applyCustomDates"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
                        Применить
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Статистика --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-5">
            <div class="text-sm text-gray-500 mb-1">Выручка</div>
            <div class="text-2xl font-bold text-gray-800">
                {{ number_format($this->salesData['total_sales'], 2, ',', ' ') }} ₽
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <div class="text-sm text-gray-500 mb-1">Количество заказов</div>
            <div class="text-2xl font-bold text-gray-800">
                {{ $this->salesData['order_count'] }}
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <div class="text-sm text-gray-500 mb-1">Средний чек</div>
            <div class="text-2xl font-bold text-gray-800">
                {{ number_format($this->salesData['avg_check'], 2, ',', ' ') }} ₽
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <div class="text-sm text-gray-500 mb-1">Период</div>
            <div class="text-lg font-semibold text-gray-800">
                {{ $dateFrom }} &mdash; {{ $dateTo }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Топ продуктов --}}
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Топ-10 продуктов</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">#</th>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Название</th>
                            <th class="px-4 py-3 text-right text-gray-600 font-medium">Кол-во</th>
                            <th class="px-4 py-3 text-right text-gray-600 font-medium">Выручка</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($this->topProducts as $index => $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 text-gray-800 font-medium">{{ $product->name }}</td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ $product->total_quantity }}</td>
                                <td class="px-4 py-3 text-right text-gray-800 font-medium">
                                    {{ number_format((float) $product->total_revenue, 2, ',', ' ') }} ₽
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-400">Нет данных за выбранный период</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Выручка по способу оплаты --}}
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Выручка по способу оплаты</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-gray-600 font-medium">Способ оплаты</th>
                            <th class="px-4 py-3 text-right text-gray-600 font-medium">Платежей</th>
                            <th class="px-4 py-3 text-right text-gray-600 font-medium">Сумма</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($this->revenueByPaymentMethod as $payment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-800 font-medium">
                                    @switch($payment->method_type)
                                        @case('cash') Наличные @break
                                        @case('card') Банковская карта @break
                                        @case('online') Онлайн @break
                                        @default {{ $payment->method_name }}
                                    @endswitch
                                </td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ $payment->payment_count }}</td>
                                <td class="px-4 py-3 text-right text-gray-800 font-medium">
                                    {{ number_format((float) $payment->total_amount, 2, ',', ' ') }} ₽
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-gray-400">Нет данных за выбранный период</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Заказы по часам --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Заказы по часам</h2>
        </div>
        <div class="p-4">
            @php
                $maxOrders = $this->ordersByHour->max('order_count') ?: 1;
            @endphp

            @if($this->ordersByHour->isEmpty())
                <p class="text-center text-gray-400 py-6">Нет данных за выбранный период</p>
            @else
                <div class="space-y-2">
                    @for($h = 0; $h < 24; $h++)
                        @php
                            $hourData = $this->ordersByHour->firstWhere('hour', $h);
                            $count = $hourData ? (int) $hourData->order_count : 0;
                            $widthPercent = $maxOrders > 0 ? round(($count / $maxOrders) * 100) : 0;
                        @endphp
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-500 w-12 text-right font-mono">{{ str_pad((string) $h, 2, '0', STR_PAD_LEFT) }}:00</span>
                            <div class="flex-1 bg-gray-100 rounded-full h-5 overflow-hidden">
                                @if($widthPercent > 0)
                                    <div class="bg-indigo-500 h-5 rounded-full transition-all duration-300 flex items-center justify-end pr-2"
                                        style="width: {{ $widthPercent }}%">
                                        @if($widthPercent > 10)
                                            <span class="text-xs text-white font-medium">{{ $count }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            @if($widthPercent <= 10 && $count > 0)
                                <span class="text-xs text-gray-500">{{ $count }}</span>
                            @endif
                        </div>
                    @endfor
                </div>
            @endif
        </div>
    </div>
</div>
