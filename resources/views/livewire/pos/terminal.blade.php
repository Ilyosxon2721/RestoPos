<div class="h-screen flex flex-col bg-gray-100" x-data="{ showTableSelector: @entangle('selectedTable').live === null }">
    {{-- Верхняя панель: залы и столы --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        {{-- Залы --}}
        <div class="flex items-center gap-2 px-4 py-2 border-b border-gray-100">
            <span class="text-sm font-medium text-gray-500 mr-2">Зал:</span>
            @foreach($this->halls as $hall)
                <button
                    wire:click="selectHall({{ $hall->id }})"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors
                        {{ $selectedHall === $hall->id
                            ? 'bg-indigo-600 text-white'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                >
                    {{ $hall->name }}
                </button>
            @endforeach
        </div>

        {{-- Столы --}}
        <div class="px-4 py-3">
            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                @foreach($this->tables as $table)
                    @php
                        $statusColor = match($table->status) {
                            \App\Support\Enums\TableStatus::Free => 'bg-emerald-100 text-emerald-800 border-emerald-300 hover:bg-emerald-200',
                            \App\Support\Enums\TableStatus::Occupied => 'bg-red-100 text-red-800 border-red-300 hover:bg-red-200',
                            \App\Support\Enums\TableStatus::Reserved => 'bg-amber-100 text-amber-800 border-amber-300 hover:bg-amber-200',
                            default => 'bg-gray-100 text-gray-800 border-gray-300 hover:bg-gray-200',
                        };
                        $isSelected = $selectedTable === $table->id;
                    @endphp
                    <button
                        wire:click="selectTable({{ $table->id }})"
                        class="flex-shrink-0 w-16 h-16 rounded-xl border-2 flex flex-col items-center justify-center transition-all
                            {{ $isSelected ? 'ring-2 ring-indigo-500 ring-offset-2 border-indigo-500 bg-indigo-50' : $statusColor }}"
                    >
                        <span class="text-xs font-medium">Стол</span>
                        <span class="text-lg font-bold">{{ $table->number }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Основной контент --}}
    <div class="flex flex-1 overflow-hidden">
        {{-- Левая панель: Меню --}}
        <div class="w-2/3 flex flex-col border-r border-gray-200 bg-white">
            {{-- Категории --}}
            <div class="flex items-center gap-2 px-4 py-3 border-b border-gray-100 overflow-x-auto">
                <button
                    wire:click="selectCategory(null)"
                    class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition-colors
                        {{ $selectedCategory === null
                            ? 'bg-indigo-600 text-white'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                >
                    Все
                </button>
                @foreach($this->categories as $category)
                    <button
                        wire:click="selectCategory({{ $category->id }})"
                        class="flex-shrink-0 px-4 py-2 rounded-lg text-sm font-medium transition-colors
                            {{ $selectedCategory === $category->id
                                ? 'bg-indigo-600 text-white'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                    >
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>

            {{-- Поиск --}}
            <div class="px-4 py-3 border-b border-gray-100">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="searchQuery"
                        placeholder="Поиск блюда..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >
                    @if($searchQuery)
                        <button
                            wire:click="$set('searchQuery', '')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Сетка продуктов --}}
            <div class="flex-1 overflow-y-auto p-4">
                @if($selectedTable)
                    <div class="grid grid-cols-3 xl:grid-cols-4 gap-3">
                        @forelse($this->products as $product)
                            <button
                                wire:click="addToCart({{ $product->id }})"
                                class="flex flex-col items-center justify-center p-4 rounded-xl border border-gray-200 bg-white hover:bg-indigo-50 hover:border-indigo-300 transition-all shadow-sm hover:shadow group"
                            >
                                @if($product->image)
                                    <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-16 h-16 rounded-lg object-cover mb-2">
                                @else
                                    <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-indigo-100 to-indigo-200 flex items-center justify-center mb-2">
                                        <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </div>
                                @endif
                                <span class="text-sm font-medium text-gray-800 text-center leading-tight group-hover:text-indigo-700">
                                    {{ $product->name }}
                                </span>
                                <span class="text-sm font-bold text-indigo-600 mt-1">
                                    {{ number_format($product->price, 0, ',', ' ') }} ₽
                                </span>
                            </button>
                        @empty
                            <div class="col-span-full flex flex-col items-center justify-center py-16 text-gray-400">
                                <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                <span class="text-lg">Нет блюд для отображения</span>
                            </div>
                        @endforelse
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-full text-gray-400">
                        <svg class="w-24 h-24 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        <span class="text-xl font-medium">Выберите стол для начала работы</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Правая панель: Текущий заказ --}}
        <div class="w-1/3 flex flex-col bg-gray-50">
            {{-- Заголовок заказа --}}
            <div class="px-4 py-3 bg-white border-b border-gray-200">
                @if($selectedTable)
                    @php
                        $table = $this->tables->firstWhere('id', $selectedTable);
                    @endphp
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">
                                Стол {{ $table?->number }}
                            </h2>
                            @if($this->currentOrder)
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                    {{ match($this->currentOrder->status) {
                                        \App\Support\Enums\OrderStatus::Open => 'bg-blue-100 text-blue-700',
                                        \App\Support\Enums\OrderStatus::InProgress => 'bg-amber-100 text-amber-700',
                                        \App\Support\Enums\OrderStatus::Paid => 'bg-emerald-100 text-emerald-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    } }}">
                                    Заказ #{{ $this->currentOrder->id }}
                                </span>
                            @else
                                <span class="text-xs text-gray-500">Новый заказ</span>
                            @endif
                        </div>
                        @if(!empty($cart))
                            <button
                                wire:click="clearCart"
                                wire:confirm="Вы уверены, что хотите очистить заказ?"
                                class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                title="Очистить заказ"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                @else
                    <h2 class="text-lg font-bold text-gray-400">Стол не выбран</h2>
                @endif
            </div>

            {{-- Позиции заказа --}}
            <div class="flex-1 overflow-y-auto px-4 py-2">
                @forelse($cart as $index => $item)
                    <div class="flex items-center gap-3 py-3 border-b border-gray-200 last:border-b-0" wire:key="cart-item-{{ $index }}">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $item['name'] }}</p>
                            <p class="text-xs text-gray-500">
                                {{ number_format($item['price'], 0, ',', ' ') }} ₽
                                @if(isset($item['status']))
                                    <span class="inline-block ml-1 px-1.5 py-0.5 rounded text-xs
                                        {{ match($item['status']) {
                                            \App\Support\Enums\OrderItemStatus::Pending->value => 'bg-gray-100 text-gray-600',
                                            \App\Support\Enums\OrderItemStatus::Cooking->value => 'bg-amber-100 text-amber-700',
                                            \App\Support\Enums\OrderItemStatus::Ready->value => 'bg-emerald-100 text-emerald-700',
                                            \App\Support\Enums\OrderItemStatus::Served->value => 'bg-blue-100 text-blue-700',
                                            default => 'bg-gray-100 text-gray-600',
                                        } }}">
                                        {{ match($item['status']) {
                                            \App\Support\Enums\OrderItemStatus::Pending->value => 'Ожидает',
                                            \App\Support\Enums\OrderItemStatus::Cooking->value => 'Готовится',
                                            \App\Support\Enums\OrderItemStatus::Ready->value => 'Готово',
                                            \App\Support\Enums\OrderItemStatus::Served->value => 'Подано',
                                            default => $item['status'],
                                        } }}
                                    </span>
                                @endif
                            </p>
                        </div>

                        {{-- Количество --}}
                        <div class="flex items-center gap-1">
                            <button
                                wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})"
                                class="w-7 h-7 rounded-lg bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-gray-600 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                </svg>
                            </button>
                            <span class="w-8 text-center text-sm font-bold text-gray-800">{{ $item['quantity'] }}</span>
                            <button
                                wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})"
                                class="w-7 h-7 rounded-lg bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-gray-600 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Сумма позиции --}}
                        <div class="text-right min-w-[70px]">
                            <span class="text-sm font-bold text-gray-800">
                                {{ number_format($item['price'] * $item['quantity'], 0, ',', ' ') }} ₽
                            </span>
                        </div>

                        {{-- Удалить --}}
                        <button
                            wire:click="removeFromCart({{ $index }})"
                            class="p-1 text-gray-400 hover:text-red-500 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-gray-400">
                        <svg class="w-16 h-16 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                        <span class="text-sm">Заказ пуст</span>
                        <span class="text-xs mt-1">Выберите блюда из меню</span>
                    </div>
                @endforelse
            </div>

            {{-- Итоги и кнопки --}}
            <div class="bg-white border-t border-gray-200 px-4 py-3">
                {{-- Итоги --}}
                <div class="space-y-1 mb-4">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Подытог</span>
                        <span>{{ number_format($this->subtotal, 0, ',', ' ') }} ₽</span>
                    </div>
                    @if($this->discount > 0)
                        <div class="flex justify-between text-sm text-emerald-600">
                            <span>Скидка</span>
                            <span>-{{ number_format($this->discount, 0, ',', ' ') }} ₽</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-lg font-bold text-gray-800 pt-1 border-t border-gray-100">
                        <span>Итого</span>
                        <span>{{ number_format($this->total, 0, ',', ' ') }} ₽</span>
                    </div>
                </div>

                {{-- Кнопки действий --}}
                <div class="flex gap-2">
                    <button
                        wire:click="sendToKitchen"
                        @if(empty($cart)) disabled @endif
                        class="flex-1 py-3 rounded-xl text-sm font-bold transition-all
                            {{ empty($cart)
                                ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                : 'bg-amber-500 hover:bg-amber-600 text-white shadow-lg shadow-amber-500/25' }}"
                    >
                        <div class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            На кухню
                        </div>
                    </button>
                    <button
                        wire:click="openPayment"
                        @if(empty($cart)) disabled @endif
                        class="flex-1 py-3 rounded-xl text-sm font-bold transition-all
                            {{ empty($cart)
                                ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                : 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg shadow-emerald-500/25' }}"
                    >
                        <div class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Оплатить
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Модальное окно оплаты --}}
    @if($paymentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center" x-data x-trap.noscroll="true">
            {{-- Оверлей --}}
            <div class="absolute inset-0 bg-black/50" wire:click="$set('paymentModal', false)"></div>

            {{-- Модальное окно --}}
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
                {{-- Заголовок --}}
                <div class="px-6 py-4 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold">Оплата заказа</h3>
                        <button wire:click="$set('paymentModal', false)" class="p-1 hover:bg-white/20 rounded-lg transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-emerald-100 text-3xl font-bold mt-2">
                        {{ number_format($this->total, 0, ',', ' ') }} ₽
                    </p>
                </div>

                <div class="p-6 space-y-5">
                    {{-- Способ оплаты --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Способ оплаты</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                wire:click="$set('paymentMethod', 'cash')"
                                class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 transition-all
                                    {{ $paymentMethod === 'cash'
                                        ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                                        : 'border-gray-200 text-gray-600 hover:border-gray-300' }}"
                            >
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="text-xs font-medium">Наличные</span>
                            </button>
                            <button
                                wire:click="$set('paymentMethod', 'card')"
                                class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 transition-all
                                    {{ $paymentMethod === 'card'
                                        ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                                        : 'border-gray-200 text-gray-600 hover:border-gray-300' }}"
                            >
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                <span class="text-xs font-medium">Карта</span>
                            </button>
                            <button
                                wire:click="$set('paymentMethod', 'transfer')"
                                class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 transition-all
                                    {{ $paymentMethod === 'transfer'
                                        ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                                        : 'border-gray-200 text-gray-600 hover:border-gray-300' }}"
                            >
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-xs font-medium">Перевод</span>
                            </button>
                        </div>
                    </div>

                    {{-- Сумма (для наличных) --}}
                    @if($paymentMethod === 'cash')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Сумма от клиента</label>
                            <input
                                type="number"
                                wire:model.live="paymentAmount"
                                min="0"
                                step="1"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 text-lg font-bold text-center focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                            >

                            {{-- Быстрые суммы --}}
                            <div class="grid grid-cols-4 gap-2 mt-2">
                                @foreach([500, 1000, 2000, 5000] as $amount)
                                    <button
                                        wire:click="$set('paymentAmount', {{ $amount }})"
                                        class="py-2 rounded-lg text-sm font-medium bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors"
                                    >
                                        {{ number_format($amount, 0, ',', ' ') }}
                                    </button>
                                @endforeach
                            </div>

                            {{-- Сдача --}}
                            @if($this->change > 0)
                                <div class="mt-3 p-3 rounded-xl bg-amber-50 border border-amber-200">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-amber-700">Сдача</span>
                                        <span class="text-xl font-bold text-amber-700">
                                            {{ number_format($this->change, 0, ',', ' ') }} ₽
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Кнопка оплаты --}}
                    <button
                        wire:click="processPayment"
                        @if($paymentMethod === 'cash' && $paymentAmount < $this->total) disabled @endif
                        class="w-full py-4 rounded-xl text-lg font-bold transition-all
                            {{ ($paymentMethod === 'cash' && $paymentAmount < $this->total)
                                ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                : 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg shadow-emerald-500/25' }}"
                    >
                        Оплатить {{ number_format($this->total, 0, ',', ' ') }} ₽
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
