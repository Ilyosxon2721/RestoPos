<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? ($store->store_name ?? 'Магазин') }}</title>
    <meta name="description" content="{{ $store->description ?? '' }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        :root {
            --primary: {{ $store->primary_color ?? '#10b981' }};
            --primary-hover: color-mix(in srgb, var(--primary) 85%, black);
        }
        .bg-primary { background-color: var(--primary); }
        .text-primary { color: var(--primary); }
        .border-primary { border-color: var(--primary); }
        .ring-primary { --tw-ring-color: var(--primary); }
        .hover\:bg-primary:hover { background-color: var(--primary); }
        .hover\:text-primary:hover { color: var(--primary); }
        .bg-primary-light { background-color: color-mix(in srgb, var(--primary) 10%, white); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="storeApp()" x-cloak>

    {{-- Навигация --}}
    <header class="sticky top-0 z-40 bg-white shadow-sm">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex items-center justify-between h-14">
                {{-- Логотип --}}
                <a href="{{ route('shop.home', ['slug' => $store->slug]) }}" class="flex items-center gap-2">
                    @if($store->logo)
                        <img src="{{ $store->logo }}" alt="{{ $store->store_name }}" class="w-8 h-8 rounded-full object-cover">
                    @endif
                    <span class="font-bold text-gray-900 text-lg">{{ $store->store_name ?? $store->organization->name }}</span>
                </a>

                {{-- Поиск (desktop) --}}
                <div class="hidden md:block flex-1 max-w-md mx-8">
                    <div class="relative">
                        <input
                            type="text"
                            x-model="globalSearch"
                            @input.debounce.300ms="$dispatch('store-search', { query: globalSearch })"
                            placeholder="Поиск блюд..."
                            class="w-full pl-10 pr-4 py-2 bg-gray-100 border-0 rounded-xl text-sm focus:ring-2 ring-primary focus:bg-white"
                        >
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                {{-- Действия --}}
                <div class="flex items-center gap-2">
                    {{-- Избранное --}}
                    <a href="{{ route('shop.favorites', ['slug' => $store->slug]) }}" class="relative p-2 text-gray-500 hover:text-primary transition-colors" title="Избранное">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <span
                            x-show="favoritesCount > 0"
                            x-text="favoritesCount"
                            class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center"
                        ></span>
                    </a>

                    {{-- Корзина --}}
                    <a href="{{ route('shop.cart', ['slug' => $store->slug]) }}" class="relative p-2 text-gray-500 hover:text-primary transition-colors" title="Корзина">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                        <span
                            x-show="cartCount > 0"
                            x-text="cartCount"
                            class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-primary text-white text-[10px] font-bold rounded-full flex items-center justify-center"
                        ></span>
                    </a>

                    {{-- Профиль --}}
                    @if(auth('customer')->check())
                        <a href="{{ route('shop.profile', ['slug' => $store->slug]) }}" class="p-2 text-gray-500 hover:text-primary transition-colors" title="Профиль">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('shop.login', ['slug' => $store->slug]) }}" class="inline-flex items-center px-3 py-1.5 bg-primary text-white text-sm font-medium rounded-lg hover:opacity-90 transition-opacity">
                            Войти
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Поиск (mobile) --}}
        <div class="md:hidden px-4 pb-2">
            <div class="relative">
                <input
                    type="text"
                    x-model="globalSearch"
                    @input.debounce.300ms="$dispatch('store-search', { query: globalSearch })"
                    placeholder="Поиск блюд..."
                    class="w-full pl-10 pr-4 py-2 bg-gray-100 border-0 rounded-xl text-sm focus:ring-2 ring-primary focus:bg-white"
                >
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>
    </header>

    {{-- Контент --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Нижняя навигация (mobile) --}}
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-gray-200 safe-area-bottom">
        <div class="flex items-center justify-around py-2">
            <a href="{{ route('shop.home', ['slug' => $store->slug]) }}" class="flex flex-col items-center gap-0.5 p-1 {{ request()->routeIs('shop.home') ? 'text-primary' : 'text-gray-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="text-[10px] font-medium">Главная</span>
            </a>
            <a href="{{ route('shop.favorites', ['slug' => $store->slug]) }}" class="flex flex-col items-center gap-0.5 p-1 relative {{ request()->routeIs('shop.favorites') ? 'text-primary' : 'text-gray-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                <span class="text-[10px] font-medium">Избранное</span>
                <span x-show="favoritesCount > 0" x-text="favoritesCount" class="absolute -top-0.5 left-1/2 ml-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center"></span>
            </a>
            <a href="{{ route('shop.cart', ['slug' => $store->slug]) }}" class="flex flex-col items-center gap-0.5 p-1 relative {{ request()->routeIs('shop.cart') ? 'text-primary' : 'text-gray-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                <span class="text-[10px] font-medium">Корзина</span>
                <span x-show="cartCount > 0" x-text="cartCount" class="absolute -top-0.5 left-1/2 ml-1 w-4 h-4 bg-primary text-white text-[10px] font-bold rounded-full flex items-center justify-center"></span>
            </a>
            @if(auth('customer')->check())
                <a href="{{ route('shop.profile', ['slug' => $store->slug]) }}" class="flex flex-col items-center gap-0.5 p-1 {{ request()->routeIs('shop.profile') ? 'text-primary' : 'text-gray-400' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span class="text-[10px] font-medium">Профиль</span>
                </a>
            @else
                <a href="{{ route('shop.login', ['slug' => $store->slug]) }}" class="flex flex-col items-center gap-0.5 p-1 {{ request()->routeIs('shop.login') ? 'text-primary' : 'text-gray-400' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span class="text-[10px] font-medium">Войти</span>
                </a>
            @endif
        </div>
    </nav>

    {{-- Корзина Toast (при добавлении) --}}
    <div
        x-show="showCartToast"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-full opacity-0"
        class="fixed bottom-20 md:bottom-6 left-4 right-4 md:left-auto md:right-6 md:w-80 z-50"
    >
        <div class="bg-gray-900 text-white rounded-xl px-4 py-3 shadow-lg flex items-center justify-between">
            <span class="text-sm" x-text="cartToastMessage"></span>
            <a href="{{ route('shop.cart', ['slug' => $store->slug ?? '']) }}" class="text-sm font-medium text-primary ml-3 whitespace-nowrap">
                Корзина
            </a>
        </div>
    </div>

    @livewireScripts

    <script>
        function storeApp() {
            return {
                globalSearch: '',
                showCartToast: false,
                cartToastMessage: '',
                cartToastTimeout: null,

                get cart() {
                    return JSON.parse(localStorage.getItem('shop_cart') || '[]');
                },

                get cartCount() {
                    return this.cart.reduce((sum, item) => sum + item.qty, 0);
                },

                get cartTotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
                },

                get favorites() {
                    return JSON.parse(localStorage.getItem('shop_favorites') || '[]');
                },

                get favoritesCount() {
                    return this.favorites.length;
                },

                addToCart(product) {
                    let cart = this.cart;
                    const idx = cart.findIndex(i => i.id === product.id);
                    if (idx >= 0) {
                        cart[idx].qty++;
                    } else {
                        cart.push({
                            id: product.id,
                            name: product.name,
                            price: parseFloat(product.price),
                            image: product.image || null,
                            qty: 1
                        });
                    }
                    localStorage.setItem('shop_cart', JSON.stringify(cart));
                    this.showToast(product.name + ' добавлен в корзину');
                },

                removeFromCart(productId) {
                    let cart = this.cart.filter(i => i.id !== productId);
                    localStorage.setItem('shop_cart', JSON.stringify(cart));
                },

                updateCartQty(productId, qty) {
                    let cart = this.cart;
                    const idx = cart.findIndex(i => i.id === productId);
                    if (idx >= 0) {
                        if (qty <= 0) {
                            cart.splice(idx, 1);
                        } else {
                            cart[idx].qty = qty;
                        }
                    }
                    localStorage.setItem('shop_cart', JSON.stringify(cart));
                },

                clearCart() {
                    localStorage.setItem('shop_cart', '[]');
                },

                toggleFavorite(product) {
                    let favs = this.favorites;
                    const idx = favs.findIndex(f => f.id === product.id);
                    if (idx >= 0) {
                        favs.splice(idx, 1);
                    } else {
                        favs.push({
                            id: product.id,
                            name: product.name,
                            price: parseFloat(product.price),
                            image: product.image || null,
                            description: product.description || null
                        });
                    }
                    localStorage.setItem('shop_favorites', JSON.stringify(favs));
                },

                isFavorite(productId) {
                    return this.favorites.some(f => f.id === productId);
                },

                formatPrice(price) {
                    return Number(price).toLocaleString('ru-RU') + ' {{ $store->currency ?? "сум" }}';
                },

                showToast(message) {
                    this.cartToastMessage = message;
                    this.showCartToast = true;
                    clearTimeout(this.cartToastTimeout);
                    this.cartToastTimeout = setTimeout(() => {
                        this.showCartToast = false;
                    }, 2500);
                }
            }
        }
    </script>
</body>
</html>
