<div class="flex h-screen overflow-hidden bg-gray-100" x-data="{ showTableModal: false }">
    {{-- Левая часть: Меню (60%) --}}
    <div class="flex w-3/5 flex-col overflow-hidden border-r border-gray-200">
        {{-- Верхняя панель: выбор стола и зала --}}
        <div class="flex items-center gap-3 border-b border-gray-200 bg-white px-4 py-3">
            {{-- Кнопка выбора стола --}}
            <button
                @click="showTableModal = true"
                class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
            >
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"/>
                </svg>
                @if($selectedTable)
                    Стол {{ $this->tables->firstWhere('id', $selectedTable)?->number ?? $selectedTable }}
                @else
                    Выбрать стол
                @endif
            </button>

            {{-- Поиск --}}
            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input
                    wire:model.live.debounce.300ms="searchProduct"
                    type="text"
                    placeholder="Поиск блюда..."
                    class="block w-full rounded-lg border border-gray-300 bg-white py-2 pl-9 pr-4 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                >
            </div>
        </div>

        {{-- Категории --}}
        <div class="flex gap-2 overflow-x-auto border-b border-gray-200 bg-white px-4 py-2 scrollbar-thin">
            <button
                wire:click="selectCategory(null)"
                @class([
                    'flex-shrink-0 rounded-lg px-4 py-2 text-sm font-medium transition-colors',
                    'bg-emerald-600 text-white' => $selectedCategory === null,
                    'bg-gray-100 text-gray-700 hover:bg-gray-200' => $selectedCategory !== null,
                ])
            >
                Все
            </button>
            @foreach($this->categories as $category)
                <button
                    wire:click="selectCategory({{ $category->id }})"
                    @class([
                        'flex-shrink-0 rounded-lg px-4 py-2 text-sm font-medium transition-colors whitespace-nowrap',
                        'bg-emerald-600 text-white' => $selectedCategory === $category->id,
                        'bg-gray-100 text-gray-700 hover:bg-gray-200' => $selectedCategory !== $category->id,
                    ])
                >
                    {{ $category->name }}
                </button>
            @endforeach
        </div>

        {{-- Сетка продуктов --}}
        <div class="flex-1 overflow-y-auto p-4">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @forelse($this->products as $product)
                    <button
                        wire:click="addToCart({{ $product->id }})"
                        class="flex flex-col items-center rounded-xl border border-gray-200 bg-white p-4 text-center shadow-sm transition-all hover:border-emerald-300 hover:shadow-md active:scale-95"
                    >
                        @if($product->image)
                            <img src="{{ $product->image }}" alt="{{ $product->name }}" class="mb-2 h-16 w-16 rounded-lg object-cover">
                        @else
                            <div class="mb-2 flex h-16 w-16 items-center justify-center rounded-lg bg-gray-100">
                                <svg class="h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                        <span class="text-sm font-medium text-gray-800 line-clamp-2">{{ $product->name }}</span>
                        <span class="mt-1 text-sm font-bold text-emerald-600">{{ number_format((float) $product->price, 0, ',', ' ') }}</span>
                    </button>
                @empty
                    <div class="col-span-full py-12 text-center text-sm text-gray-500">
                        Товары не найдены
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Правая часть: Корзина (40%) --}}
    <div class="flex w-2/5 flex-col bg-white">
        {{-- Информация о столе --}}
        <div class="border-b border-gray-200 px-5 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">
                        @if($selectedTable)
                            Стол {{ $this->tables->firstWhere('id', $selectedTable)?->number ?? $selectedTable }}
                        @else
                            Стол не выбран
                        @endif
                    </h2>
                    <p class="text-xs text-gray-500">{{ count($cart) }} позиций</p>
                </div>
                @if(count($cart) > 0)
                    <button
                        wire:click="clearCart"
                        class="text-sm text-red-500 hover:text-red-700 transition-colors"
                    >
                        Очистить
                    </button>
                @endif
            </div>
        </div>

        {{-- Позиции корзины --}}
        <div class="flex-1 overflow-y-auto px-5 py-3">
            @forelse($cart as $index => $item)
                <div class="flex items-center gap-3 border-b border-gray-100 py-3" wire:key="cart-{{ $index }}">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $item['name'] }}</p>
                        <p class="text-xs text-gray-500">{{ number_format($item['price'], 0, ',', ' ') }} x {{ $item['quantity'] }}</p>
                    </div>

                    {{-- Управление количеством --}}
                    <div class="flex items-center gap-1">
                        <button
                            wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})"
                            class="flex h-7 w-7 items-center justify-center rounded-md border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 transition-colors"
                        >
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                            </svg>
                        </button>
                        <span class="w-8 text-center text-sm font-medium text-gray-800">{{ $item['quantity'] }}</span>
                        <button
                            wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})"
                            class="flex h-7 w-7 items-center justify-center rounded-md border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 transition-colors"
                        >
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Сумма --}}
                    <div class="w-20 text-right">
                        <span class="text-sm font-bold text-gray-900">{{ number_format($item['subtotal'], 0, ',', ' ') }}</span>
                    </div>

                    {{-- Удалить --}}
                    <button
                        wire:click="removeFromCart({{ $index }})"
                        class="flex h-7 w-7 items-center justify-center rounded-md text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                    <svg class="mb-3 h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                    </svg>
                    <p class="text-sm">Корзина пуста</p>
                    <p class="mt-1 text-xs">Выберите блюда из меню</p>
                </div>
            @endforelse
        </div>

        {{-- Итого и кнопки --}}
        <div class="border-t border-gray-200 px-5 py-4">
            <div class="mb-4 flex items-center justify-between">
                <span class="text-lg font-bold text-gray-900">Итого:</span>
                <span class="text-2xl font-bold text-emerald-600">{{ number_format($this->cartTotal, 0, ',', ' ') }} сум</span>
            </div>

            <div class="flex gap-3">
                <button
                    @class([
                        'flex-1 rounded-lg py-3 text-sm font-medium transition-colors',
                        'bg-blue-600 text-white hover:bg-blue-700' => count($cart) > 0 && $selectedTable,
                        'bg-gray-200 text-gray-400 cursor-not-allowed' => count($cart) === 0 || !$selectedTable,
                    ])
                    @disabled(count($cart) === 0 || !$selectedTable)
                >
                    Отправить на кухню
                </button>
                <button
                    @class([
                        'flex-1 rounded-lg py-3 text-sm font-medium transition-colors',
                        'bg-emerald-600 text-white hover:bg-emerald-700' => count($cart) > 0,
                        'bg-gray-200 text-gray-400 cursor-not-allowed' => count($cart) === 0,
                    ])
                    @disabled(count($cart) === 0)
                >
                    Оплатить
                </button>
            </div>
        </div>
    </div>

    {{-- Модальное окно выбора стола --}}
    <div
        x-show="showTableModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @click.self="showTableModal = false"
        style="display: none;"
    >
        <div
            class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Выберите стол</h3>
                <button @click="showTableModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Залы --}}
            <div class="mb-4 flex gap-2">
                @foreach($this->halls as $hall)
                    <button
                        wire:click="selectHall({{ $hall->id }})"
                        @class([
                            'rounded-lg px-4 py-2 text-sm font-medium transition-colors',
                            'bg-emerald-600 text-white' => $selectedHall === $hall->id,
                            'bg-gray-100 text-gray-700 hover:bg-gray-200' => $selectedHall !== $hall->id,
                        ])
                    >
                        {{ $hall->name }}
                    </button>
                @endforeach
            </div>

            {{-- Столы --}}
            <div class="grid grid-cols-4 gap-3 max-h-80 overflow-y-auto">
                @foreach($this->tables as $table)
                    @php
                        $isOccupied = $table->status === 'occupied';
                        $isFree = in_array($table->status, ['free', 'available']);
                    @endphp
                    <button
                        wire:click="selectTable({{ $table->id }})"
                        @click="showTableModal = false"
                        @class([
                            'flex flex-col items-center justify-center rounded-xl border-2 p-4 transition-all',
                            'border-emerald-500 bg-emerald-50' => $selectedTable === $table->id,
                            'border-emerald-200 bg-emerald-50 hover:border-emerald-400' => $isFree && $selectedTable !== $table->id,
                            'border-red-200 bg-red-50 hover:border-red-400' => $isOccupied && $selectedTable !== $table->id,
                            'border-amber-200 bg-amber-50 hover:border-amber-400' => $table->status === 'reserved' && $selectedTable !== $table->id,
                        ])
                    >
                        <span class="text-lg font-bold text-gray-800">{{ $table->number }}</span>
                        <span class="text-xs text-gray-500">
                            @if($isFree) Свободен @elseif($isOccupied) Занят @else Бронь @endif
                        </span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>
