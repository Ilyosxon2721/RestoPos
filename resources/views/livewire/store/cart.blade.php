<div class="max-w-2xl mx-auto px-4 py-6 pb-24 md:pb-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Корзина</h1>

    {{-- Список товаров --}}
    <div x-show="cart.length > 0">
        <div class="space-y-3">
            <template x-for="item in cart" :key="item.id">
                <div class="bg-white rounded-xl border border-gray-100 p-4 flex items-center gap-3 shadow-sm">
                    {{-- Изображение --}}
                    <template x-if="item.image">
                        <img :src="item.image" :alt="item.name" class="w-16 h-16 rounded-lg object-cover flex-shrink-0">
                    </template>
                    <template x-if="!item.image">
                        <div class="w-16 h-16 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    </template>

                    {{-- Инфо --}}
                    <div class="flex-1 min-w-0">
                        <h3 class="font-medium text-gray-900 text-sm truncate" x-text="item.name"></h3>
                        <p class="text-sm font-bold text-primary mt-0.5" x-text="formatPrice(item.price)"></p>
                    </div>

                    {{-- Количество --}}
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <button
                            @click="updateCartQty(item.id, item.qty - 1)"
                            class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-gray-200 transition"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        </button>
                        <span class="w-8 text-center text-sm font-medium" x-text="item.qty"></span>
                        <button
                            @click="updateCartQty(item.id, item.qty + 1)"
                            class="w-8 h-8 rounded-lg bg-primary text-white flex items-center justify-center hover:opacity-90 transition"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>

                    {{-- Удалить --}}
                    <button @click="removeFromCart(item.id)" class="p-1.5 text-gray-400 hover:text-red-500 transition flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </template>
        </div>

        {{-- Итого --}}
        <div class="mt-6 bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-600">Товаров:</span>
                <span class="font-medium" x-text="cartCount + ' шт.'"></span>
            </div>
            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                <span class="text-lg font-bold text-gray-900">Итого:</span>
                <span class="text-lg font-bold text-primary" x-text="formatPrice(cartTotal)"></span>
            </div>
        </div>

        {{-- Кнопки --}}
        <div class="mt-4 space-y-2">
            @if(auth('customer')->check())
                <a href="{{ route('shop.checkout', ['slug' => $store->slug]) }}"
                   class="block w-full text-center px-6 py-3 bg-primary text-white font-medium rounded-xl hover:opacity-90 transition">
                    Оформить заказ
                </a>
            @else
                <a href="{{ route('shop.login', ['slug' => $store->slug]) }}"
                   class="block w-full text-center px-6 py-3 bg-primary text-white font-medium rounded-xl hover:opacity-90 transition">
                    Войти для оформления
                </a>
            @endif
            <button @click="clearCart()" class="w-full text-center px-6 py-3 text-gray-500 font-medium rounded-xl hover:bg-gray-100 transition text-sm">
                Очистить корзину
            </button>
        </div>
    </div>

    {{-- Пустая корзина --}}
    <div x-show="cart.length === 0" class="text-center py-16">
        <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
        <h2 class="text-xl font-bold text-gray-900 mb-2">Корзина пуста</h2>
        <p class="text-gray-500 mb-6">Добавьте блюда из каталога</p>
        <a href="{{ route('shop.home', ['slug' => $store->slug]) }}" class="inline-flex items-center px-6 py-2.5 bg-primary text-white font-medium rounded-xl hover:opacity-90 transition">
            Перейти в каталог
        </a>
    </div>
</div>
