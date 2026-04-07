<div class="min-h-screen bg-gray-900 text-white" wire:poll.5s>

    {{-- ========== PIN-ЭКРАН АВТОРИЗАЦИИ ========== --}}
    @if ($pinLocked)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900"
             x-data="{
                 localPin: '',
                 error: '',
                 loading: false,
                 append(d) {
                     if (this.localPin.length >= 4 || this.loading) return;
                     this.error = '';
                     this.localPin += d;
                     if (this.localPin.length === 4) {
                         this.loading = true;
                         $wire.verifyPin(this.localPin).then(() => {
                             this.loading = false;
                             if (this.localPin.length === 4) {
                                 this.error = 'Неверный PIN-код или нет доступа.';
                                 this.localPin = '';
                             }
                         }).catch(() => {
                             this.loading = false;
                             this.error = 'Ошибка проверки';
                             this.localPin = '';
                         });
                     }
                 },
                 clear() { this.localPin = ''; this.error = ''; },
                 backspace() { this.localPin = this.localPin.slice(0, -1); this.error = ''; }
             }"
             @keydown.window="
                 if ($event.key >= '0' && $event.key <= '9') append($event.key);
                 else if ($event.key === 'Backspace') backspace();
                 else if ($event.key === 'Escape') clear();
             ">
            <div class="w-full max-w-sm mx-auto text-center">
                {{-- Логотип --}}
                <div class="mb-8">
                    <div class="w-20 h-20 mx-auto rounded-2xl bg-orange-600/20 flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white">Кухонный дисплей</h1>
                    <p class="text-gray-400 text-sm mt-1">Введите PIN-код повара</p>
                </div>

                {{-- Индикатор PIN --}}
                <div class="flex justify-center gap-4 mb-6">
                    <template x-for="i in 4">
                        <div class="h-4 w-4 rounded-full border-2 transition-all duration-200"
                             :class="localPin.length >= i ? 'bg-orange-500 border-orange-500 scale-110' : 'border-gray-600'"></div>
                    </template>
                </div>

                <p x-show="error" x-text="error" class="text-red-400 text-sm mb-4" x-cloak></p>
                <p x-show="loading" class="text-orange-400 text-sm mb-4" x-cloak>Проверка...</p>

                {{-- Цифровая клавиатура --}}
                <div class="grid grid-cols-3 gap-3 max-w-xs mx-auto">
                    <template x-for="digit in ['1','2','3','4','5','6','7','8','9']">
                        <button @click="append(digit)"
                                :disabled="loading"
                                class="h-16 rounded-2xl bg-gray-800 hover:bg-gray-700 active:bg-orange-600 text-2xl font-bold text-white transition-all duration-150 active:scale-95 disabled:opacity-50"
                                x-text="digit"></button>
                    </template>
                    <button @click="clear()"
                            class="h-16 rounded-2xl bg-gray-800 hover:bg-gray-700 text-sm font-bold text-red-400 transition-all duration-150">
                        Сброс
                    </button>
                    <button @click="append('0')"
                            :disabled="loading"
                            class="h-16 rounded-2xl bg-gray-800 hover:bg-gray-700 active:bg-orange-600 text-2xl font-bold text-white transition-all duration-150 active:scale-95 disabled:opacity-50">
                        0
                    </button>
                    <button @click="backspace()"
                            class="h-16 rounded-2xl bg-gray-800 hover:bg-gray-700 text-xl font-bold text-gray-400 transition-all duration-150 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l7-7 12 0v14H10l-7-7z"/>
                        </svg>
                    </button>
                </div>

                {{-- Кнопка выхода --}}
                <div class="mt-8">
                    <a href="/redirect" class="text-gray-500 hover:text-gray-300 text-sm transition">
                        &larr; Вернуться в панель
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Верхняя панель фильтров --}}
    <div class="bg-gray-800 border-b border-gray-700 px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h1 class="text-2xl font-bold">Кухонный дисплей</h1>
                @if ($operatorName)
                    <button wire:click="lockKitchen"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-600/20 text-orange-300 hover:bg-orange-600/30 text-sm font-medium transition"
                            title="Сменить повара">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ $operatorName }}
                    </button>
                @endif
            </div>

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

            <div class="text-gray-400 text-sm">
                {{ now()->format('H:i:s') }}
            </div>
        </div>
    </div>

    {{-- Статистика --}}
    <div class="bg-gray-800/50 px-6 py-3 flex items-center gap-8 text-sm border-b border-gray-700">
        <div class="flex items-center gap-2">
            <span class="text-gray-400">Всего заказов:</span>
            <span class="font-bold text-lg">{{ $this->stats['total'] }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-gray-400">Среднее время готовки:</span>
            <span class="font-bold text-lg">{{ $this->stats['avg_prep_time'] }} мин</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
            <span class="text-gray-400">Новые: {{ $this->stats['sent'] }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-orange-500"></span>
            <span class="text-gray-400">Готовятся: {{ $this->stats['preparing'] }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-green-500"></span>
            <span class="text-gray-400">Готово: {{ $this->stats['ready'] }}</span>
        </div>
    </div>

    {{-- Сетка заказов --}}
    <div class="p-6">
        @if($this->orders->isEmpty())
            <div class="flex flex-col items-center justify-center py-32 text-gray-500">
                <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 5h6" />
                </svg>
                <p class="text-xl">Нет активных заказов</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($this->orders as $order)
                    @php
                        $minutesElapsed = (int) now()->diffInMinutes($order->created_at);
                        $timeClass = match(true) {
                            $minutesElapsed >= 25 => 'bg-red-900 border-red-500',
                            $minutesElapsed >= 15 => 'bg-yellow-900 border-yellow-500',
                            default => 'bg-gray-800 border-gray-600',
                        };
                    @endphp

                    <div class="rounded-xl border-2 {{ $timeClass }} overflow-hidden">
                        {{-- Заголовок карточки --}}
                        <div class="px-4 py-3 flex items-center justify-between border-b border-gray-700">
                            <div>
                                <span class="text-lg font-bold">#{{ $order->order_number }}</span>
                                <span class="ml-2 text-gray-400 text-sm">Стол {{ $order->table_number }}</span>
                            </div>
                            <div @class([
                                'text-sm font-mono px-2 py-1 rounded',
                                'text-red-400 bg-red-900/50' => $minutesElapsed >= 25,
                                'text-yellow-400 bg-yellow-900/50' => $minutesElapsed >= 15 && $minutesElapsed < 25,
                                'text-gray-400 bg-gray-700' => $minutesElapsed < 15,
                            ])>
                                {{ $minutesElapsed }} мин
                            </div>
                        </div>

                        {{-- Позиции заказа --}}
                        <div class="px-4 py-3 space-y-3">
                            @foreach($order->items as $item)
                                @php
                                    $statusColor = match($item->status) {
                                        \App\Support\Enums\OrderItemStatus::SENT => 'border-yellow-500 bg-yellow-500/10',
                                        \App\Support\Enums\OrderItemStatus::PREPARING => 'border-orange-500 bg-orange-500/10',
                                        \App\Support\Enums\OrderItemStatus::READY => 'border-green-500 bg-green-500/10',
                                        default => 'border-gray-600 bg-gray-700',
                                    };
                                    $dotColor = match($item->status) {
                                        \App\Support\Enums\OrderItemStatus::SENT => 'bg-yellow-500',
                                        \App\Support\Enums\OrderItemStatus::PREPARING => 'bg-orange-500',
                                        \App\Support\Enums\OrderItemStatus::READY => 'bg-green-500',
                                        default => 'bg-gray-500',
                                    };
                                @endphp

                                <div class="border-l-4 pl-3 py-2 rounded-r {{ $statusColor }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full {{ $dotColor }}"></span>
                                            <span class="font-medium">{{ $item->product?->name ?? 'Блюдо' }}</span>
                                            @if($item->quantity > 1)
                                                <span class="text-gray-400">x{{ $item->quantity }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($item->notes)
                                        <p class="text-xs text-gray-400 mt-1 ml-4">{{ $item->notes }}</p>
                                    @endif

                                    {{-- Кнопки действий --}}
                                    <div class="mt-2 ml-4 flex gap-2">
                                        @if($item->status === \App\Support\Enums\OrderItemStatus::SENT)
                                            <button
                                                wire:click="startPreparing({{ $item->id }})"
                                                class="px-3 py-1 text-xs font-medium rounded bg-orange-600 hover:bg-orange-500 text-white transition-colors"
                                            >
                                                Готовлю
                                            </button>
                                        @endif

                                        @if($item->status === \App\Support\Enums\OrderItemStatus::PREPARING)
                                            <button
                                                wire:click="markReady({{ $item->id }})"
                                                class="px-3 py-1 text-xs font-medium rounded bg-green-600 hover:bg-green-500 text-white transition-colors"
                                            >
                                                Готово
                                            </button>
                                        @endif

                                        @if($item->status === \App\Support\Enums\OrderItemStatus::READY)
                                            <button
                                                wire:click="markServed({{ $item->id }})"
                                                class="px-3 py-1 text-xs font-medium rounded bg-blue-600 hover:bg-blue-500 text-white transition-colors"
                                            >
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
