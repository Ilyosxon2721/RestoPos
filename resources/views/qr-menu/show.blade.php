<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $qrMenu->name }} — Меню</title>
    <meta name="description" content="{{ $qrMenu->description ?? $qrMenu->name }}">
    @vite(['resources/css/app.css'])
    <style>
        :root {
            --primary: {{ $qrMenu->primary_color }};
            --bg: {{ $qrMenu->background_color }};
        }
        body { background-color: var(--bg); }
        .btn-primary { background-color: var(--primary); }
        .text-primary { color: var(--primary); }
        .border-primary { border-color: var(--primary); }
        .bg-primary { background-color: var(--primary); }
        .ring-primary { --tw-ring-color: var(--primary); }

        /* Плавная прокрутка */
        html { scroll-behavior: smooth; }

        /* Скрытие скроллбара в категориях */
        .category-scroll::-webkit-scrollbar { display: none; }
        .category-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="min-h-screen pb-20" x-data="qrMenu()">

    {{-- Шапка --}}
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-sm shadow-sm">
        <div class="max-w-lg mx-auto px-4 py-3">
            <div class="flex items-center gap-3">
                @if($qrMenu->logo)
                    <img src="{{ $qrMenu->logo }}" alt="{{ $qrMenu->name }}" class="w-10 h-10 rounded-full object-cover">
                @else
                    <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center">
                        <span class="text-white font-bold text-lg">{{ mb_substr($qrMenu->name, 0, 1) }}</span>
                    </div>
                @endif
                <div>
                    <h1 class="text-lg font-bold text-gray-900 leading-tight">{{ $qrMenu->name }}</h1>
                    @if($qrMenu->description)
                        <p class="text-xs text-gray-500">{{ $qrMenu->description }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Категории - горизонтальная прокрутка --}}
        <div class="border-t border-gray-100">
            <div class="max-w-lg mx-auto">
                <nav class="category-scroll flex overflow-x-auto gap-1 px-4 py-2">
                    <button
                        @click="selectCategory(null)"
                        :class="selectedCategory === null ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="flex-shrink-0 px-4 py-1.5 rounded-full text-sm font-medium transition-colors"
                    >
                        Все
                    </button>
                    @foreach($categories as $category)
                        <button
                            @click="selectCategory({{ $category->id }})"
                            :class="selectedCategory === {{ $category->id }} ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="flex-shrink-0 px-4 py-1.5 rounded-full text-sm font-medium transition-colors whitespace-nowrap"
                        >
                            {{ $category->name }}
                        </button>
                        @foreach($category->children as $child)
                            <button
                                @click="selectCategory({{ $child->id }})"
                                :class="selectedCategory === {{ $child->id }} ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="flex-shrink-0 px-4 py-1.5 rounded-full text-sm font-medium transition-colors whitespace-nowrap"
                            >
                                {{ $child->name }}
                            </button>
                        @endforeach
                    @endforeach
                </nav>
            </div>
        </div>
    </header>

    {{-- Поиск --}}
    <div class="max-w-lg mx-auto px-4 pt-4">
        <div class="relative">
            <input
                type="text"
                x-model="search"
                placeholder="Поиск блюд..."
                class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 ring-primary focus:border-transparent"
            >
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <button x-show="search.length > 0" @click="search = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Список блюд --}}
    <main class="max-w-lg mx-auto px-4 pt-4 pb-8">
        @foreach($categories as $category)
            {{-- Родительская категория --}}
            <section
                x-show="selectedCategory === null || selectedCategory === {{ $category->id }} || [{{ $category->children->pluck('id')->implode(',') }}].includes(selectedCategory)"
                x-cloak
                class="mb-6"
                id="category-{{ $category->id }}"
            >
                <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                    @if($category->color)
                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $category->color }}"></span>
                    @endif
                    {{ $category->name }}
                </h2>

                {{-- Продукты родительской категории --}}
                @if(isset($products[$category->id]))
                    <div
                        x-show="selectedCategory === null || selectedCategory === {{ $category->id }}"
                        class="space-y-3"
                    >
                        @foreach($products[$category->id] as $product)
                            <template x-if="matchesSearch('{{ addslashes($product->name) }}')">
                                @include('qr-menu.partials.product-card', ['product' => $product, 'qrMenu' => $qrMenu])
                            </template>
                        @endforeach
                    </div>
                @endif

                {{-- Дочерние категории --}}
                @foreach($category->children as $child)
                    <div
                        x-show="selectedCategory === null || selectedCategory === {{ $category->id }} || selectedCategory === {{ $child->id }}"
                        class="mt-4"
                    >
                        <h3 class="text-base font-semibold text-gray-700 mb-2 ml-1">{{ $child->name }}</h3>
                        @if(isset($products[$child->id]))
                            <div class="space-y-3">
                                @foreach($products[$child->id] as $product)
                                    <template x-if="matchesSearch('{{ addslashes($product->name) }}')">
                                        @include('qr-menu.partials.product-card', ['product' => $product, 'qrMenu' => $qrMenu])
                                    </template>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </section>
        @endforeach

        {{-- Пустое состояние --}}
        <div x-show="search.length > 0" x-cloak class="text-center py-12" id="no-results" style="display: none;">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <p class="text-gray-500">Введите название блюда для поиска</p>
        </div>
    </main>

    {{-- Футер --}}
    <footer class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-sm border-t border-gray-100 py-2">
        <div class="max-w-lg mx-auto px-4 text-center">
            <p class="text-xs text-gray-400">Powered by <span class="font-medium text-primary">FORRIS POS</span></p>
        </div>
    </footer>

    {{-- Модальное окно продукта --}}
    <div
        x-show="showProductModal"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-end justify-center"
        @click.self="showProductModal = false"
    >
        <div class="fixed inset-0 bg-black/50"></div>
        <div
            x-show="showProductModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
            class="relative w-full max-w-lg bg-white rounded-t-2xl max-h-[85vh] overflow-y-auto"
            @click.stop
        >
            {{-- Изображение --}}
            <template x-if="selectedProduct?.image">
                <div class="relative h-56 bg-gray-100">
                    <img :src="selectedProduct.image" :alt="selectedProduct.name" class="w-full h-full object-cover">
                    <button @click="showProductModal = false" class="absolute top-3 right-3 w-8 h-8 bg-black/40 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>

            <div class="p-5">
                <template x-if="!selectedProduct?.image">
                    <div class="flex justify-end mb-2">
                        <button @click="showProductModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>

                <h3 x-text="selectedProduct?.name" class="text-xl font-bold text-gray-900 mb-1"></h3>

                <template x-if="selectedProduct?.description">
                    <p x-text="selectedProduct.description" class="text-sm text-gray-600 mb-4"></p>
                </template>

                {{-- КБЖУ --}}
                <template x-if="selectedProduct?.calories">
                    <div class="grid grid-cols-4 gap-2 mb-4">
                        <div class="bg-gray-50 rounded-lg p-2 text-center">
                            <div x-text="selectedProduct.calories" class="text-sm font-bold text-gray-900"></div>
                            <div class="text-xs text-gray-500">ккал</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2 text-center">
                            <div x-text="selectedProduct.proteins || '—'" class="text-sm font-bold text-gray-900"></div>
                            <div class="text-xs text-gray-500">белки</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2 text-center">
                            <div x-text="selectedProduct.fats || '—'" class="text-sm font-bold text-gray-900"></div>
                            <div class="text-xs text-gray-500">жиры</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2 text-center">
                            <div x-text="selectedProduct.carbs || '—'" class="text-sm font-bold text-gray-900"></div>
                            <div class="text-xs text-gray-500">углев.</div>
                        </div>
                    </div>
                </template>

                {{-- Вес и время --}}
                <div class="flex items-center gap-4 mb-4">
                    <template x-if="selectedProduct?.weight">
                        <span class="text-sm text-gray-500 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                            </svg>
                            <span x-text="selectedProduct.weight + ' г'"></span>
                        </span>
                    </template>
                    <template x-if="selectedProduct?.cooking_time">
                        <span class="text-sm text-gray-500 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="selectedProduct.cooking_time + ' мин'"></span>
                        </span>
                    </template>
                </div>

                {{-- Цена --}}
                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <span class="text-2xl font-bold text-primary" x-text="formatPrice(selectedProduct?.price)"></span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function qrMenu() {
            return {
                search: '',
                selectedCategory: null,
                showProductModal: false,
                selectedProduct: null,
                currency: '{{ $qrMenu->currency }}',

                selectCategory(id) {
                    this.selectedCategory = id;
                },

                matchesSearch(name) {
                    if (this.search.length === 0) return true;
                    return name.toLowerCase().includes(this.search.toLowerCase());
                },

                openProduct(product) {
                    this.selectedProduct = product;
                    this.showProductModal = true;
                },

                formatPrice(price) {
                    if (!price) return '';
                    return Number(price).toLocaleString('ru-RU') + ' ' + this.currency;
                }
            }
        }
    </script>
</body>
</html>
