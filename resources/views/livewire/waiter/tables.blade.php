<div class="min-h-screen bg-gray-50 pb-6">
    {{-- Заголовок --}}
    <div class="bg-white px-4 py-4 shadow-sm">
        <h1 class="text-xl font-bold text-gray-900">Столы</h1>
    </div>

    {{-- Выбор зала --}}
    <div class="flex gap-2 overflow-x-auto px-4 py-3 scrollbar-thin">
        @foreach($halls as $hall)
            <button
                wire:click="selectHall({{ $hall->id }})"
                @class([
                    'flex-shrink-0 rounded-full px-5 py-2 text-sm font-medium transition-colors',
                    'bg-emerald-600 text-white shadow-sm' => $selectedHall === $hall->id,
                    'bg-white text-gray-700 border border-gray-300' => $selectedHall !== $hall->id,
                ])
            >
                {{ $hall->name }}
            </button>
        @endforeach
    </div>

    {{-- Сетка столов --}}
    <div class="px-4">
        @if($tables->isNotEmpty())
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach($tables as $table)
                    @php
                        $statusConfig = match($table->status) {
                            'free', 'available' => [
                                'bg' => 'bg-emerald-50 border-emerald-300',
                                'dot' => 'bg-emerald-500',
                                'label' => 'Свободен',
                                'text' => 'text-emerald-700',
                            ],
                            'occupied' => [
                                'bg' => 'bg-red-50 border-red-300',
                                'dot' => 'bg-red-500',
                                'label' => 'Занят',
                                'text' => 'text-red-700',
                            ],
                            'reserved' => [
                                'bg' => 'bg-amber-50 border-amber-300',
                                'dot' => 'bg-amber-500',
                                'label' => 'Бронь',
                                'text' => 'text-amber-700',
                            ],
                            default => [
                                'bg' => 'bg-gray-50 border-gray-300',
                                'dot' => 'bg-gray-400',
                                'label' => $table->status,
                                'text' => 'text-gray-700',
                            ],
                        };
                        $order = $table->currentOrder;
                    @endphp

                    <div class="rounded-2xl border-2 p-5 {{ $statusConfig['bg'] }}">
                        {{-- Номер стола --}}
                        <div class="mb-3 flex items-center justify-between">
                            <span class="text-3xl font-bold text-gray-800">{{ $table->number }}</span>
                            <span class="h-3.5 w-3.5 rounded-full {{ $statusConfig['dot'] }}"></span>
                        </div>

                        {{-- Статус --}}
                        <p class="text-sm font-medium {{ $statusConfig['text'] }}">{{ $statusConfig['label'] }}</p>

                        {{-- Гости --}}
                        @if($table->capacity)
                            <div class="mt-2 flex items-center gap-1 text-xs text-gray-500">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>{{ $order->guests_count ?? 0 }} / {{ $table->capacity }}</span>
                            </div>
                        @endif

                        {{-- Информация о заказе --}}
                        @if($order)
                            <div class="mt-3 rounded-lg bg-white/60 px-3 py-2">
                                <p class="text-xs font-medium text-gray-600">Заказ #{{ $order->order_number ?? $order->id }}</p>
                                <p class="text-xs text-gray-500">{{ number_format((float) $order->total_amount, 0, ',', ' ') }} сум</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-20 text-gray-400">
                <svg class="mb-3 h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5z"/>
                </svg>
                <p class="text-sm">Выберите зал</p>
            </div>
        @endif
    </div>
</div>
