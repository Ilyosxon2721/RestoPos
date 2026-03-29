<div class="min-h-screen bg-gray-900 text-white" wire:poll.5s>
    {{-- Верхняя панель --}}
    <div class="bg-gray-800 border-b border-gray-700 px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h1 class="text-2xl font-bold">KDS</h1>
                <span class="text-sm text-gray-400">Kitchen Display System</span>
            </div>

            {{-- Фильтры --}}
            <div class="flex items-center gap-2">
                <button
                    wire:click="setFilter('all')"
                    @class([
                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                        'bg-blue-600 text-white' => $filter === 'all',
                        'bg-gray-700 text-gray-300 hover:bg-gray-600' => $filter !== 'all',
                    ])
                >
                    Все
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-xs bg-gray-600">{{ $this->stats['total'] }}</span>
                </button>

                <button
                    wire:click="setFilter('pending')"
                    @class([
                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                        'bg-yellow-600 text-white' => $filter === 'pending',
                        'bg-gray-700 text-gray-300 hover:bg-gray-600' => $filter !== 'pending',
                    ])
                >
                    Новые
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-xs bg-gray-600">{{ $this->stats['sent'] }}</span>
                </button>

                <button
                    wire:click="setFilter('preparing')"
                    @class([
                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                        'bg-orange-600 text-white' => $filter === 'preparing',
                        'bg-gray-700 text-gray-300 hover:bg-gray-600' => $filter !== 'preparing',
                    ])
                >
                    Готовятся
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-xs bg-gray-600">{{ $this->stats['preparing'] }}</span>
                </button>

                <button
                    wire:click="setFilter('ready')"
                    @class([
                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                        'bg-green-600 text-white' => $filter === 'ready',
                        'bg-gray-700 text-gray-300 hover:bg-gray-600' => $filter !== 'ready',
                    ])
                >
                    Готово
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-xs bg-gray-600">{{ $this->stats['ready'] }}</span>
                </button>
            </div>

            {{-- Время и индикатор обновления --}}
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 text-sm text-gray-400">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    </span>
                    <span>Авто-обновление</span>
                </div>
                <div class="text-gray-400 text-sm font-mono">
                    {{ now()->format('H:i:s') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Статистика --}}
    <div class="bg-gray-800/50 px-6 py-3 flex items-center gap-8 text-sm border-b border-gray-700">
        <div class="flex items-center gap-2">
            <span class="text-gray-400">Всего позиций:</span>
            <span class="font-bold text-lg">{{ $this->stats['total'] }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-gray-400">Среднее время:</span>
            <span class="font-bold text-lg">{{ $this->stats['avg_prep_time'] }} мин</span>
        </div>
        <div class="flex-1"></div>
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                <span class="text-gray-400">Новые: <span class="text-white font-medium">{{ $this->stats['sent'] }}</span></span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-orange-500"></span>
                <span class="text-gray-400">Готовятся: <span class="text-white font-medium">{{ $this->stats['preparing'] }}</span></span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                <span class="text-gray-400">Готово: <span class="text-white font-medium">{{ $this->stats['ready'] }}</span></span>
            </div>
        </div>
    </div>

    {{-- Сетка заказов --}}
    <div class="p-6">
        @if($this->orders->isEmpty())
            <div class="flex flex-col items-center justify-center py-32 text-gray-500">
                <svg class="w-20 h-20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 5h6"/>
                </svg>
                <p class="text-xl font-medium">Нет активных заказов</p>
                <p class="mt-1 text-sm text-gray-600">Новые заказы появятся здесь автоматически</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4">
                @foreach($this->orders as $order)
                    @php
                        $minutesElapsed = (int) now()->diffInMinutes($order->created_at);
                        $cardStyle = match(true) {
                            $minutesElapsed >= 25 => 'bg-red-900/80 border-red-500 shadow-red-500/20',
                            $minutesElapsed >= 15 => 'bg-yellow-900/60 border-yellow-500 shadow-yellow-500/20',
                            default => 'bg-gray-800 border-gray-600',
                        };
                        $timeStyle = match(true) {
                            $minutesElapsed >= 25 => 'text-red-400 bg-red-900/50',
                            $minutesElapsed >= 15 => 'text-yellow-400 bg-yellow-900/50',
                            default => 'text-gray-400 bg-gray-700',
                        };
                    @endphp

                    <div class="rounded-xl border-2 {{ $cardStyle }} shadow-lg overflow-hidden">
                        {{-- Заголовок карточки --}}
                        <div class="px-4 py-3 flex items-center justify-between border-b border-gray-700/50">
                            <div>
                                <span class="text-xl font-bold">#{{ $order->order_number }}</span>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5z"/>
                                    </svg>
                                    <span class="text-sm text-gray-400">Стол {{ $order->table_number }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-mono px-2.5 py-1 rounded-lg {{ $timeStyle }}">
                                    {{ $minutesElapsed }} мин
                                </div>
                            </div>
                        </div>

                        {{-- Позиции заказа --}}
                        <div class="px-4 py-3 space-y-2.5">
                            @foreach($order->items as $item)
                                @php
                                    $itemBorder = match($item->status) {
                                        \App\Support\Enums\OrderItemStatus::SENT => 'border-yellow-500 bg-yellow-500/10',
                                        \App\Support\Enums\OrderItemStatus::PREPARING => 'border-orange-500 bg-orange-500/10',
                                        \App\Support\Enums\OrderItemStatus::READY => 'border-green-500 bg-green-500/10',
                                        default => 'border-gray-600 bg-gray-700/50',
                                    };
                                    $dotColor = match($item->status) {
                                        \App\Support\Enums\OrderItemStatus::SENT => 'bg-yellow-500',
                                        \App\Support\Enums\OrderItemStatus::PREPARING => 'bg-orange-500',
                                        \App\Support\Enums\OrderItemStatus::READY => 'bg-green-500',
                                        default => 'bg-gray-500',
                                    };
                                    $statusLabel = match($item->status) {
                                        \App\Support\Enums\OrderItemStatus::SENT => 'Новый',
                                        \App\Support\Enums\OrderItemStatus::PREPARING => 'Готовится',
                                        \App\Support\Enums\OrderItemStatus::READY => 'Готово',
                                        default => '',
                                    };
                                @endphp

                                <div class="border-l-4 pl-3 py-2 rounded-r {{ $itemBorder }}">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $dotColor }}"></span>
                                            <div>
                                                <span class="font-medium">{{ $item->product?->name ?? 'Блюдо' }}</span>
                                                @if($item->quantity > 1)
                                                    <span class="text-gray-400 ml-1">x{{ $item->quantity }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <span class="text-xs text-gray-500 flex-shrink-0">{{ $statusLabel }}</span>
                                    </div>

                                    @if($item->notes)
                                        <p class="text-xs text-gray-400 mt-1 ml-4 italic">{{ $item->notes }}</p>
                                    @endif

                                    {{-- Кнопки действий --}}
                                    <div class="mt-2 ml-4 flex gap-2">
                                        @if($item->status === \App\Support\Enums\OrderItemStatus::SENT)
                                            <button
                                                wire:click="startPreparing({{ $item->id }})"
                                                class="px-3 py-1.5 text-xs font-medium rounded-lg bg-orange-600 hover:bg-orange-500 text-white transition-colors flex items-center gap-1"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                                                </svg>
                                                Начать
                                            </button>
                                        @endif

                                        @if($item->status === \App\Support\Enums\OrderItemStatus::PREPARING)
                                            <button
                                                wire:click="markReady({{ $item->id }})"
                                                class="px-3 py-1.5 text-xs font-medium rounded-lg bg-green-600 hover:bg-green-500 text-white transition-colors flex items-center gap-1"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Готово
                                            </button>
                                        @endif

                                        @if($item->status === \App\Support\Enums\OrderItemStatus::READY)
                                            <button
                                                wire:click="markServed({{ $item->id }})"
                                                class="px-3 py-1.5 text-xs font-medium rounded-lg bg-blue-600 hover:bg-blue-500 text-white transition-colors flex items-center gap-1"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                                </svg>
                                                Подано
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
