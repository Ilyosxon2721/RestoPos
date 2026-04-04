<div class="max-w-6xl mx-auto px-4 py-6 pb-24 md:pb-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Избранное</h1>

    <div x-show="favoritesCount > 0">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
            <template x-for="product in favorites" :key="product.id">
                <div class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <div class="relative aspect-square bg-gray-100 overflow-hidden">
                        <template x-if="product.image">
                            <img :src="product.image" :alt="product.name" class="w-full h-full object-cover" loading="lazy">
                        </template>
                        <template x-if="!product.image">
                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        </template>
                        <button
                            @click="toggleFavorite(product)"
                            class="absolute top-2 right-2 w-8 h-8 bg-white/80 backdrop-blur-sm rounded-full flex items-center justify-center shadow-sm hover:bg-white transition"
                        >
                            <svg class="w-4 h-4 text-red-500 fill-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </button>
                    </div>
                    <div class="p-3">
                        <h3 class="font-medium text-gray-900 text-sm leading-tight line-clamp-2 mb-1" x-text="product.name"></h3>
                        <template x-if="product.description">
                            <p class="text-xs text-gray-500 line-clamp-1 mb-2" x-text="product.description"></p>
                        </template>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-sm font-bold text-gray-900" x-text="formatPrice(product.price)"></span>
                            <button
                                @click="addToCart(product)"
                                class="w-8 h-8 bg-primary text-white rounded-lg flex items-center justify-center hover:opacity-90 transition"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div x-show="favoritesCount === 0" class="text-center py-16">
        <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        <h2 class="text-xl font-bold text-gray-900 mb-2">Нет избранных</h2>
        <p class="text-gray-500 mb-6">Нажмите на сердечко, чтобы добавить блюдо в избранное</p>
        <a href="{{ route('shop.home', ['slug' => $store->slug]) }}" class="inline-flex items-center px-6 py-2.5 bg-primary text-white font-medium rounded-xl hover:opacity-90 transition">
            Перейти в каталог
        </a>
    </div>
</div>
