<div>
    {{-- Заголовок --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Финансы</h1>
        <p class="text-sm text-gray-500 mt-1">Финансовый обзор организации</p>
    </div>

    {{-- Переключатель периодов --}}
    <div class="mb-6">
        <div class="inline-flex rounded-lg border border-gray-200 bg-white shadow-sm">
            @php
                $periods = [
                    'today' => 'Сегодня',
                    'week' => 'Неделя',
                    'month' => 'Месяц',
                    'all' => 'Всё время',
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

    {{-- Карточки статистики --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
        {{-- Выручка --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Выручка</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($this->revenue, 0, '.', ' ') }} <span class="text-base font-normal text-gray-500">сум</span></p>
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
                    <p class="text-sm font-medium text-gray-500">Количество заказов</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($this->ordersCount, 0, '.', ' ') }}</p>
                </div>
                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Средний чек --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Средний чек</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">
                    {{ $this->ordersCount > 0 ? number_format($this->revenue / $this->ordersCount, 0, '.', ' ') : 0 }}
                    <span class="text-base font-normal text-gray-500">сум</span>
                </p>
            </div>
            <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-orange-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                </svg>
            </div>
        </div>
    </div>
</div>
