<div class="pb-20 md:pb-8">
    {{-- Баннеры со скроллом --}}
    @if($this->banners->count() > 0)
        <div class="relative" x-data="bannerSlider({{ $this->banners->count() }})">
            <div class="overflow-hidden">
                <div class="flex transition-transform duration-500 ease-in-out" :style="'transform: translateX(-' + current * 100 + '%)'">
                    @foreach($this->banners as $banner)
                        <div class="w-full flex-shrink-0">
                            <div class="relative aspect-[2.5/1] md:aspect-[3.5/1] bg-gray-200 cursor-pointer"
                                 @if($banner->link) onclick="window.location='{{ $banner->link }}'" @endif>
                                <img src="{{ $banner->image }}" alt="{{ $banner->title }}" class="w-full h-full object-cover" loading="lazy">
                                @if($banner->title)
                                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-4 md:p-6">
                                        <h2 class="text-white text-lg md:text-2xl font-bold">{{ $banner->title }}</h2>
                                        @if($banner->description)
                                            <p class="text-white/80 text-sm mt-1">{{ $banner->description }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            {{-- Индикаторы --}}
            @if($this->banners->count() > 1)
                <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5">
                    @foreach($this->banners as $i => $banner)
                        <button
                            @click="goTo({{ $i }})"
                            :class="current === {{ $i }} ? 'bg-white w-6' : 'bg-white/50 w-2'"
                            class="h-2 rounded-full transition-all duration-300"
                        ></button>
                    @endforeach
                </div>
                {{-- Стрелки --}}
                <button @click="prev()" class="hidden md:flex absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/80 rounded-full items-center justify-center shadow hover:bg-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button @click="next()" class="hidden md:flex absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/80 rounded-full items-center justify-center shadow hover:bg-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            @endif
        </div>
    @endif

    <div class="max-w-6xl mx-auto px-4">
        {{-- Напиток дня --}}
        @if($this->drinkOfDay)
            <div class="mt-6 bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl p-4 md:p-6 border border-amber-100">
                <div class="flex items-center gap-4">
                    @if($this->drinkOfDay->image)
                        <img src="{{ $this->drinkOfDay->image }}" alt="{{ $this->drinkOfDay->name }}"
                             class="w-20 h-20 md:w-24 md:h-24 rounded-xl object-cover flex-shrink-0">
                    @endif
                    <div class="flex-1 min-w-0">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-200 text-amber-800 mb-1">
                            Напиток дня
                        </span>
                        <h3 class="text-lg font-bold text-gray-900 truncate">{{ $this->drinkOfDay->name }}</h3>
                        @if($this->drinkOfDay->description)
                            <p class="text-sm text-gray-600 line-clamp-2">{{ $this->drinkOfDay->description }}</p>
                        @endif
                        <div class="flex items-center gap-3 mt-2">
                            <span class="text-lg font-bold text-primary" x-text="formatPrice({{ $this->drinkOfDay->price }})"></span>
                            <button
                                @click="addToCart({id: {{ $this->drinkOfDay->id }}, name: '{{ addslashes($this->drinkOfDay->name) }}', price: {{ $this->drinkOfDay->price }}, image: {{ $this->drinkOfDay->image ? "'" . $this->drinkOfDay->image . "'" : 'null' }}})"
                                class="inline-flex items-center px-3 py-1.5 bg-primary text-white text-sm font-medium rounded-lg hover:opacity-90 transition"
                            >
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                В корзину
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Категории --}}
        <div class="mt-6 mb-4">
            <div class="flex overflow-x-auto gap-2 pb-2 category-scroll" style="-ms-overflow-style: none; scrollbar-width: none;">
                <button
                    wire:click="selectCategory(null)"
                    class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium transition-colors {{ $selectedCategory === null ? 'bg-primary text-white' : 'bg-white text-gray-700 border border-gray-200 hover:border-gray-300' }}"
                >
                    Все
                </button>
                @foreach($this->categories as $category)
                    <button
                        wire:click="selectCategory({{ $category->id }})"
                        class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium transition-colors whitespace-nowrap {{ $selectedCategory === $category->id ? 'bg-primary text-white' : 'bg-white text-gray-700 border border-gray-200 hover:border-gray-300' }}"
                    >
                        {{ $category->name }}
                    </button>
                    @foreach($category->children as $child)
                        <button
                            wire:click="selectCategory({{ $child->id }})"
                            class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-medium transition-colors whitespace-nowrap {{ $selectedCategory === $child->id ? 'bg-primary text-white' : 'bg-white text-gray-700 border border-gray-200 hover:border-gray-300' }}"
                        >
                            {{ $child->name }}
                        </button>
                    @endforeach
                @endforeach
            </div>
        </div>

        {{-- Сетка продуктов --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
            @forelse($this->products as $product)
                <div class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm hover:shadow-md transition-shadow group">
                    {{-- Изображение --}}
                    <div class="relative aspect-square bg-gray-100 overflow-hidden">
                        @if($product->image)
                            <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                        {{-- Кнопка избранного --}}
                        <button
                            @click.stop="toggleFavorite({id: {{ $product->id }}, name: '{{ addslashes($product->name) }}', price: {{ $product->price }}, image: {{ $product->image ? "'" . $product->image . "'" : 'null' }}, description: {{ $product->description ? "'" . addslashes($product->description) . "'" : 'null' }}})"
                            class="absolute top-2 right-2 w-8 h-8 bg-white/80 backdrop-blur-sm rounded-full flex items-center justify-center shadow-sm hover:bg-white transition"
                        >
                            <svg
                                class="w-4 h-4 transition-colors"
                                :class="isFavorite({{ $product->id }}) ? 'text-red-500 fill-red-500' : 'text-gray-400'"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </button>
                    </div>
                    {{-- Инфо --}}
                    <div class="p-3">
                        <h3 class="font-medium text-gray-900 text-sm leading-tight line-clamp-2 mb-1">{{ $product->name }}</h3>
                        @if($product->description)
                            <p class="text-xs text-gray-500 line-clamp-1 mb-2">{{ $product->description }}</p>
                        @endif
                        @if($product->weight)
                            <span class="text-xs text-gray-400">{{ $product->weight }} г</span>
                        @endif
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-sm font-bold text-gray-900" x-text="formatPrice({{ $product->price }})"></span>
                            <button
                                @click="addToCart({id: {{ $product->id }}, name: '{{ addslashes($product->name) }}', price: {{ $product->price }}, image: {{ $product->image ? "'" . $product->image . "'" : 'null' }}})"
                                class="w-8 h-8 bg-primary text-white rounded-lg flex items-center justify-center hover:opacity-90 transition"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-16">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <p class="text-gray-500">Ничего не найдено</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
    .category-scroll::-webkit-scrollbar { display: none; }
</style>

<script>
    function bannerSlider(count) {
        return {
            current: 0,
            total: count,
            interval: null,
            init() {
                if (this.total > 1) {
                    this.interval = setInterval(() => this.next(), 5000);
                }
            },
            next() {
                this.current = (this.current + 1) % this.total;
            },
            prev() {
                this.current = (this.current - 1 + this.total) % this.total;
            },
            goTo(index) {
                this.current = index;
                clearInterval(this.interval);
                this.interval = setInterval(() => this.next(), 5000);
            }
        }
    }
</script>
