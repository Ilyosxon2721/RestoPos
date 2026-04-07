<div class="flex h-screen bg-gray-900 text-white overflow-hidden" x-data="{ notification: null }"
     @notify.window="notification = $event.detail; setTimeout(() => notification = null, 3000)">

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
                    <div class="w-20 h-20 mx-auto rounded-2xl bg-indigo-600/20 flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white">POS Терминал</h1>
                    <p class="text-gray-400 text-sm mt-1">Введите PIN-код кассира</p>
                </div>

                {{-- Индикатор PIN --}}
                <div class="flex justify-center gap-4 mb-6">
                    <template x-for="i in 4">
                        <div class="h-4 w-4 rounded-full border-2 transition-all duration-200"
                             :class="localPin.length >= i ? 'bg-indigo-500 border-indigo-500 scale-110' : 'border-gray-600'"></div>
                    </template>
                </div>

                <p x-show="error" x-text="error" class="text-red-400 text-sm mb-4" x-cloak></p>
                <p x-show="loading" class="text-indigo-400 text-sm mb-4" x-cloak>Проверка...</p>

                {{-- Цифровая клавиатура --}}
                <div class="grid grid-cols-3 gap-3 max-w-xs mx-auto">
                    <template x-for="digit in ['1','2','3','4','5','6','7','8','9']">
                        <button @click="append(digit)"
                                :disabled="loading"
                                class="h-16 rounded-2xl bg-gray-800 hover:bg-gray-700 active:bg-indigo-600 text-2xl font-bold text-white transition-all duration-150 active:scale-95 disabled:opacity-50"
                                x-text="digit"></button>
                    </template>
                    <button @click="clear()"
                            class="h-16 rounded-2xl bg-gray-800 hover:bg-gray-700 text-sm font-bold text-red-400 transition-all duration-150">
                        Сброс
                    </button>
                    <button @click="append('0')"
                            :disabled="loading"
                            class="h-16 rounded-2xl bg-gray-800 hover:bg-gray-700 active:bg-indigo-600 text-2xl font-bold text-white transition-all duration-150 active:scale-95 disabled:opacity-50">
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

    {{-- ========== ЛЕВАЯ ПАНЕЛЬ: Категории ========== --}}
    <div class="w-24 bg-gray-950 flex flex-col border-r border-gray-800 overflow-y-auto scrollbar-hide">
        {{-- Все категории --}}
        <button wire:click="selectCategory(null)"
                class="flex flex-col items-center justify-center px-2 py-4 text-xs font-medium border-b border-gray-800 transition
                       {{ !$selectedCategory ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            Все
        </button>

        @foreach ($this->categories as $category)
            <button wire:click="selectCategory({{ $category->id }})"
                    class="flex flex-col items-center justify-center px-2 py-4 text-xs font-medium border-b border-gray-800 transition min-h-[72px]
                           {{ $selectedCategory == $category->id ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                @if ($category->image)
                    <img src="{{ $category->image }}" alt="" class="w-8 h-8 rounded-lg mb-1 object-cover">
                @else
                    <div class="w-8 h-8 rounded-lg mb-1 flex items-center justify-center text-lg"
                         style="background: {{ $category->color ?? '#4F46E5' }}20">
                        {{ mb_substr($category->name, 0, 1) }}
                    </div>
                @endif
                <span class="text-center leading-tight line-clamp-2">{{ $category->name }}</span>
            </button>
        @endforeach
    </div>

    {{-- ========== ЦЕНТРАЛЬНАЯ ПАНЕЛЬ: Товары ========== --}}
    <div class="flex-1 flex flex-col min-w-0">
        {{-- Шапка: оператор + поиск + тип заказа + кнопки --}}
        <div class="flex items-center gap-2 px-3 py-2 border-b border-gray-800" style="background: #1a1d23;">
            {{-- Оператор --}}
            @if ($operatorName)
                <button wire:click="lockTerminal"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-lg bg-indigo-600/20 text-indigo-300 hover:bg-indigo-600/30 text-sm font-medium transition shrink-0"
                        title="Сменить кассира">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    {{ $operatorName }}
                </button>
            @endif

            {{-- Поиск --}}
            <div class="relative flex-1 max-w-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="searchProduct"
                       type="text"
                       placeholder="Поиск товара..."
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg pl-10 pr-4 py-2 text-sm text-white placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
            </div>

            {{-- Стол / Тип заказа --}}
            <button wire:click="openTableModal"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition
                           {{ $selectedTable || $selectedTableName ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-gray-700 hover:bg-gray-600' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                {{ $selectedTableName ?? 'Выбрать стол' }}
            </button>

            {{-- Открытые заказы --}}
            <button wire:click="$set('showOrdersModal', true)"
                    class="flex items-center gap-2 px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Заказы
            </button>
        </div>

        {{-- Сетка товаров --}}
        <div class="flex-1 overflow-y-auto p-3">
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-2">
                @forelse ($this->products as $product)
                    <button wire:click="addToCart({{ $product->id }})"
                            class="group flex flex-col bg-gray-800 rounded-xl overflow-hidden hover:ring-1 hover:ring-indigo-500 transition-all active:scale-95 {{ $product->in_stop_list ? 'opacity-40 pointer-events-none' : '' }}"
                            style="min-height: 120px;">
                        @if ($product->image)
                            <div class="aspect-square w-full overflow-hidden">
                                <img src="{{ $product->image }}" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                            </div>
                        @else
                            <div class="aspect-square w-full flex items-center justify-center text-3xl"
                                 style="background: {{ $product->category?->color ?? '#4F46E5' }}15">
                                {{ mb_substr($product->name, 0, 1) }}
                            </div>
                        @endif
                        <div class="p-2 flex-1 flex flex-col justify-between">
                            <p class="text-xs font-medium text-gray-200 line-clamp-2 leading-tight">{{ $product->name }}</p>
                            <p class="text-sm font-bold text-emerald-400 mt-1">{{ number_format($product->price, 0, '.', ' ') }}</p>
                        </div>
                    </button>
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center py-20 text-gray-500">
                        <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <p class="text-lg">Товары не найдены</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ========== ПРАВАЯ ПАНЕЛЬ: Чек / Корзина ========== --}}
    <div class="w-80 lg:w-96 border-l border-gray-800 flex flex-col" style="background: #1a1d23;">
        {{-- Заголовок чека --}}
        <div class="px-4 py-3 border-b border-gray-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-white">
                    @if ($currentOrderId)
                        Заказ #{{ $currentOrderId }}
                    @else
                        Новый заказ
                    @endif
                </h3>
                <p class="text-xs text-gray-500">
                    {{ $selectedTableName ?? 'Стол не выбран' }}
                    @if (count($cart) > 0)
                        &middot; {{ $this->cartItemsCount }} поз.
                    @endif
                </p>
            </div>
            @if (count($cart) > 0)
                <button wire:click="clearCart" class="text-gray-500 hover:text-red-400 transition p-1" title="Очистить">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            @endif
        </div>

        {{-- Позиции чека --}}
        <div class="flex-1 overflow-y-auto">
            @forelse ($cart as $index => $item)
                <div class="px-4 py-2.5 border-b border-gray-800/60 {{ ($item['sent'] ?? false) ? 'bg-gray-800/30' : '' }}">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-200 truncate">
                                {{ $item['name'] }}
                                @if ($item['sent'] ?? false)
                                    <span class="text-[10px] text-emerald-500 font-normal ml-1">КУХНЯ</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ number_format($item['price'], 0, '.', ' ') }} x {{ $item['quantity'] }}
                            </p>
                        </div>
                        <span class="text-sm font-semibold text-white whitespace-nowrap">
                            {{ number_format($item['subtotal'], 0, '.', ' ') }}
                        </span>
                    </div>

                    {{-- Кнопки +/- --}}
                    <div class="flex items-center justify-between mt-1.5">
                        <div class="flex items-center gap-1">
                            @if (! ($item['sent'] ?? false))
                                <button wire:click="decrementItem({{ $index }})"
                                        class="w-7 h-7 flex items-center justify-center rounded-md bg-gray-700 hover:bg-red-600 text-gray-300 hover:text-white transition text-sm">
                                    @if ($item['quantity'] == 1)
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    @else
                                        &minus;
                                    @endif
                                </button>
                            @endif
                            <span class="w-8 text-center text-sm font-medium text-gray-300">{{ $item['quantity'] }}</span>
                            <button wire:click="incrementItem({{ $index }})"
                                    class="w-7 h-7 flex items-center justify-center rounded-md bg-gray-700 hover:bg-indigo-600 text-gray-300 hover:text-white transition text-sm">
                                +
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-16 text-gray-600">
                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                    </svg>
                    <p class="text-sm">Корзина пуста</p>
                    <p class="text-xs mt-1">Выберите товар из меню</p>
                </div>
            @endforelse
        </div>

        {{-- Итоги --}}
        @if (count($cart) > 0)
            <div class="border-t border-gray-700 px-4 py-3 space-y-1.5">
                <div class="flex justify-between text-sm text-gray-400">
                    <span>Подитог</span>
                    <span>{{ number_format($this->cartTotal, 0, '.', ' ') }}</span>
                </div>

                @if ($this->totalDiscount > 0)
                    <div class="flex justify-between text-sm text-orange-400">
                        <span>Скидка {{ $discountPercent > 0 ? "({$discountPercent}%)" : '' }}</span>
                        <span>-{{ number_format($this->totalDiscount, 0, '.', ' ') }}</span>
                    </div>
                @endif

                <div class="flex justify-between text-lg font-bold text-white pt-1 border-t border-gray-700">
                    <span>Итого</span>
                    <span class="text-emerald-400">{{ number_format($this->totalWithDiscount, 0, '.', ' ') }}</span>
                </div>
            </div>
        @endif

        {{-- Кнопки действий --}}
        <div class="p-3 border-t border-gray-700 space-y-2">
            @if ($this->totalDiscount == 0 && count($cart) > 0)
                <button wire:click="openDiscountModal"
                        class="w-full flex items-center justify-center gap-2 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-sm font-medium text-gray-300 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Скидка
                </button>
            @elseif ($this->totalDiscount > 0)
                <button wire:click="removeDiscount"
                        class="w-full flex items-center justify-center gap-2 py-2 rounded-lg bg-orange-600/20 hover:bg-orange-600/30 text-sm font-medium text-orange-400 transition">
                    Убрать скидку ({{ $discountPercent > 0 ? "{$discountPercent}%" : number_format($discountAmount, 0, '.', ' ') }})
                </button>
            @endif

            <div class="grid grid-cols-2 gap-2">
                <button wire:click="sendToKitchen"
                        @disabled(empty($cart))
                        class="flex items-center justify-center gap-2 py-3 rounded-xl bg-amber-600 hover:bg-amber-700 disabled:opacity-30 disabled:cursor-not-allowed text-sm font-bold text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10s4-1 5-3c1.5 2.5 3 3.5 3 5a4 4 0 01-4 4z"/>
                    </svg>
                    Кухня
                </button>

                <button wire:click="openPaymentModal"
                        @disabled(empty($cart))
                        class="flex items-center justify-center gap-2 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-30 disabled:cursor-not-allowed text-sm font-bold text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Оплата
                </button>
            </div>
        </div>
    </div>

    {{-- ========== МОДАЛКА: Выбор стола ========== --}}
    @if ($showTableModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70" wire:click.self="$set('showTableModal', false)">
            <div class="bg-gray-900 rounded-2xl w-full max-w-2xl max-h-[80vh] overflow-hidden border border-gray-700 shadow-2xl">
                <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                    <h3 class="text-lg font-bold">Выберите стол или тип заказа</h3>
                    <button wire:click="$set('showTableModal', false)" class="text-gray-500 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Быстрый выбор типа --}}
                <div class="px-6 py-3 flex gap-2 border-b border-gray-800">
                    <button wire:click="setOrderType('takeaway')"
                            class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-sm font-medium transition">
                        С собой
                    </button>
                    <button wire:click="setOrderType('delivery')"
                            class="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-sm font-medium transition">
                        Доставка
                    </button>
                </div>

                {{-- Залы --}}
                <div class="px-6 py-2 flex gap-2 overflow-x-auto">
                    @foreach ($this->halls as $hall)
                        <button wire:click="selectHall({{ $hall->id }})"
                                class="px-4 py-1.5 rounded-full text-sm font-medium transition whitespace-nowrap
                                       {{ $selectedHall == $hall->id ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700' }}">
                            {{ $hall->name }}
                        </button>
                    @endforeach
                </div>

                {{-- Сетка столов --}}
                <div class="px-6 py-4 overflow-y-auto max-h-[50vh]">
                    <div class="grid grid-cols-4 sm:grid-cols-5 gap-3">
                        @foreach ($this->tables as $table)
                            <button wire:click="selectTable({{ $table->id }})"
                                    class="aspect-square flex flex-col items-center justify-center rounded-xl border-2 transition font-medium
                                           {{ $table->has_order ? 'border-amber-500 bg-amber-500/10 text-amber-400' : 'border-gray-700 bg-gray-800 text-gray-300 hover:border-emerald-500 hover:bg-emerald-500/10' }}">
                                <span class="text-lg font-bold">{{ $table->name }}</span>
                                <span class="text-[10px] mt-0.5">{{ $table->capacity ?? 4 }} мест</span>
                                @if ($table->has_order)
                                    <span class="text-[10px] text-amber-500 mt-0.5">Занят</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ========== МОДАЛКА: Оплата ========== --}}
    @if ($showPaymentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70" wire:click.self="$set('showPaymentModal', false)">
            <div class="bg-gray-900 rounded-2xl w-full max-w-md border border-gray-700 shadow-2xl">
                <div class="px-6 py-4 border-b border-gray-800">
                    <h3 class="text-lg font-bold">Оплата заказа</h3>
                </div>

                <div class="px-6 py-4 space-y-4">
                    {{-- Сумма к оплате --}}
                    <div class="text-center py-4 bg-gray-800 rounded-xl">
                        <p class="text-sm text-gray-400 mb-1">К оплате</p>
                        <p class="text-4xl font-bold text-emerald-400">{{ number_format($this->totalWithDiscount, 0, '.', ' ') }}</p>
                        <p class="text-xs text-gray-500 mt-1">сум</p>
                    </div>

                    {{-- Способ оплаты --}}
                    <div class="grid grid-cols-3 gap-2">
                        <button wire:click="$set('paymentMethod', 'cash')"
                                class="py-3 rounded-xl text-sm font-bold transition
                                       {{ $paymentMethod === 'cash' ? 'bg-emerald-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700' }}">
                            Наличные
                        </button>
                        <button wire:click="$set('paymentMethod', 'card')"
                                class="py-3 rounded-xl text-sm font-bold transition
                                       {{ $paymentMethod === 'card' ? 'bg-blue-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700' }}">
                            Карта
                        </button>
                        <button wire:click="$set('paymentMethod', 'mixed')"
                                class="py-3 rounded-xl text-sm font-bold transition
                                       {{ $paymentMethod === 'mixed' ? 'bg-purple-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700' }}">
                            Смешанный
                        </button>
                    </div>

                    {{-- Ввод наличных --}}
                    @if ($paymentMethod === 'cash')
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Получено наличными</label>
                            <input wire:model.live="cashReceived"
                                   type="number"
                                   class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-lg font-bold text-white text-center focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                                   placeholder="0">
                            @if ($this->changeAmount > 0)
                                <p class="text-center text-amber-400 font-bold mt-2">
                                    Сдача: {{ number_format($this->changeAmount, 0, '.', ' ') }} сум
                                </p>
                            @endif

                            {{-- Быстрые суммы --}}
                            <div class="grid grid-cols-4 gap-2 mt-2">
                                @foreach ([10000, 20000, 50000, 100000] as $amount)
                                    <button wire:click="$set('cashReceived', '{{ $amount }}')"
                                            class="py-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-xs font-medium text-gray-400 transition">
                                        {{ number_format($amount, 0, '.', ' ') }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="px-6 py-4 border-t border-gray-800 flex gap-3">
                    <button wire:click="$set('showPaymentModal', false)"
                            class="flex-1 py-3 rounded-xl bg-gray-700 hover:bg-gray-600 text-sm font-bold transition">
                        Отмена
                    </button>
                    <button wire:click="processPayment"
                            class="flex-1 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-sm font-bold transition">
                        Оплатить
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========== МОДАЛКА: Скидка ========== --}}
    @if ($showDiscountModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70" wire:click.self="$set('showDiscountModal', false)">
            <div class="bg-gray-900 rounded-2xl w-full max-w-sm border border-gray-700 shadow-2xl">
                <div class="px-6 py-4 border-b border-gray-800">
                    <h3 class="text-lg font-bold">Скидка</h3>
                </div>

                <div class="px-6 py-4 space-y-4">
                    <div class="grid grid-cols-2 gap-2">
                        <button wire:click="$set('discountType', 'percent')"
                                class="py-2 rounded-lg text-sm font-bold transition
                                       {{ $discountType === 'percent' ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-400' }}">
                            Процент %
                        </button>
                        <button wire:click="$set('discountType', 'fixed')"
                                class="py-2 rounded-lg text-sm font-bold transition
                                       {{ $discountType === 'fixed' ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-400' }}">
                            Фикс. сумма
                        </button>
                    </div>

                    @if ($discountType === 'percent')
                        <input wire:model="discountPercent"
                               type="number" min="0" max="100" step="1"
                               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-2xl font-bold text-white text-center focus:border-indigo-500 focus:outline-none"
                               placeholder="0">
                        <div class="grid grid-cols-4 gap-2">
                            @foreach ([5, 10, 15, 20] as $p)
                                <button wire:click="$set('discountPercent', {{ $p }})"
                                        class="py-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-sm font-medium text-gray-400 transition">
                                    {{ $p }}%
                                </button>
                            @endforeach
                        </div>
                    @else
                        <input wire:model="discountAmount"
                               type="number" min="0"
                               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-2xl font-bold text-white text-center focus:border-indigo-500 focus:outline-none"
                               placeholder="0">
                    @endif
                </div>

                <div class="px-6 py-4 border-t border-gray-800 flex gap-3">
                    <button wire:click="$set('showDiscountModal', false)"
                            class="flex-1 py-3 rounded-xl bg-gray-700 hover:bg-gray-600 text-sm font-bold transition">
                        Отмена
                    </button>
                    <button wire:click="applyDiscount"
                            class="flex-1 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-sm font-bold transition">
                        Применить
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========== МОДАЛКА: Открытые заказы ========== --}}
    @if ($showOrdersModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70" wire:click.self="$set('showOrdersModal', false)">
            <div class="bg-gray-900 rounded-2xl w-full max-w-lg max-h-[80vh] overflow-hidden border border-gray-700 shadow-2xl">
                <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                    <h3 class="text-lg font-bold">Открытые заказы</h3>
                    <button wire:click="$set('showOrdersModal', false)" class="text-gray-500 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="overflow-y-auto max-h-[60vh]">
                    @forelse ($this->openOrders as $order)
                        <button wire:click="loadOrder({{ $order->id }})"
                                class="w-full text-left px-6 py-4 border-b border-gray-800 hover:bg-gray-800 transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-white">#{{ $order->order_number }}</span>
                                    <span class="text-sm text-gray-500 ml-2">
                                        {{ $order->table ? "Стол {$order->table->name}" : ($order->type === 'takeaway' ? 'С собой' : 'Доставка') }}
                                    </span>
                                </div>
                                <span class="text-sm font-bold text-emerald-400">
                                    {{ number_format($order->total_amount, 0, '.', ' ') }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs text-gray-500">
                                    {{ $order->items->count() }} поз. &middot; {{ $order->created_at->format('H:i') }}
                                </span>
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                    {{ match($order->status) {
                                        'new' => 'bg-blue-500/20 text-blue-400',
                                        'preparing' => 'bg-amber-500/20 text-amber-400',
                                        'ready' => 'bg-emerald-500/20 text-emerald-400',
                                        default => 'bg-gray-500/20 text-gray-400',
                                    } }}">
                                    {{ match($order->status) {
                                        'new' => 'Новый',
                                        'accepted' => 'Принят',
                                        'preparing' => 'Готовится',
                                        'ready' => 'Готов',
                                        default => $order->status,
                                    } }}
                                </span>
                            </div>
                        </button>
                    @empty
                        <div class="px-6 py-12 text-center text-gray-500">
                            <p>Нет открытых заказов</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- ========== Уведомление ========== --}}
    <div x-show="notification" x-transition
         class="fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-2xl text-sm font-medium"
         :class="notification?.type === 'success' ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white'"
         x-text="notification?.message">
    </div>
</div>
