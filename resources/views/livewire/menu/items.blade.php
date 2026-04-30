<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Заголовок и панель управления --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Меню</h1>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full sm:w-auto">
                {{-- Поиск --}}
                <div class="relative w-full sm:w-64">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="searchQuery"
                        placeholder="Поиск блюд..."
                        class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                    >
                </div>

                {{-- Фильтр по категории --}}
                <select
                    wire:model.live="filterCategory"
                    class="w-full sm:w-auto rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                >
                    <option value="">Все категории</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>

                {{-- Кнопка добавления --}}
                <a
                    href="{{ url('/cabinet/menu/dishes/create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors whitespace-nowrap"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Тех. карта
                </a>
                <button
                    wire:click="create"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Быстрое
                </button>
            </div>
        </div>

        {{-- Таблица блюд --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Блюдо</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Категория</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Цена</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Себестоимость</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Время готовки</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($this->products as $product)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                                        @if($product->description)
                                            <p class="text-xs text-gray-500 truncate max-w-xs">{{ $product->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if($product->category)
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $product->category->color ?? '#3B82F6' }}"></span>
                                        {{ $product->category->name }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 text-right">
                                {{ number_format((float) $product->price, 2, '.', ' ') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 text-right">
                                @if($product->cost_price)
                                    {{ number_format((float) $product->cost_price, 2, '.', ' ') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 text-center">
                                @if($product->cooking_time)
                                    {{ $product->cooking_time }} мин
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button
                                    wire:click="toggleAvailability({{ $product->id }})"
                                    title="Нажмите для переключения"
                                >
                                    @if($product->is_available)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 cursor-pointer hover:bg-green-200 transition-colors">
                                            Доступно
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 cursor-pointer hover:bg-red-200 transition-colors">
                                            Стоп-лист
                                        </span>
                                    @endif
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        href="{{ url('/cabinet/menu/dishes/'.$product->id.'/edit') }}"
                                        class="p-2 text-gray-400 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 transition-colors"
                                        title="Тех. карта"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </a>
                                    <button
                                        wire:click="edit({{ $product->id }})"
                                        class="p-2 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-colors"
                                        title="Быстрое редактирование"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button
                                        wire:click="delete({{ $product->id }})"
                                        wire:confirm="Вы уверены, что хотите удалить это блюдо?"
                                        class="p-2 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors"
                                        title="Удалить"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <p class="text-lg mb-2">Блюда не найдены</p>
                                <p class="text-sm">
                                    @if($searchQuery || $filterCategory)
                                        Попробуйте изменить параметры поиска
                                    @else
                                        Нажмите "Добавить" для создания первого блюда
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Пагинация --}}
            @if($this->products->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $this->products->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Модальное окно создания/редактирования --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4">
                {{-- Оверлей --}}
                <div class="fixed inset-0 bg-black/50 transition-opacity" wire:click="$set('showModal', false)"></div>

                {{-- Модальное окно --}}
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 z-10">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">
                        {{ $editingId ? 'Редактировать блюдо' : 'Новое блюдо' }}
                    </h2>

                    <form wire:submit="save" class="space-y-4">
                        {{-- Название --}}
                        <div>
                            <label for="itemName" class="block text-sm font-medium text-gray-700 mb-1">Название</label>
                            <input
                                type="text"
                                id="itemName"
                                wire:model="name"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Название блюда"
                            >
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Категория --}}
                        <div>
                            <label for="itemCategory" class="block text-sm font-medium text-gray-700 mb-1">Категория</label>
                            <select
                                id="itemCategory"
                                wire:model="categoryId"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">— Выберите категорию —</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('categoryId')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Цены --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="itemPrice" class="block text-sm font-medium text-gray-700 mb-1">Цена продажи</label>
                                <input
                                    type="number"
                                    id="itemPrice"
                                    wire:model="price"
                                    step="0.01"
                                    min="0"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="0.00"
                                >
                                @error('price')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="itemCostPrice" class="block text-sm font-medium text-gray-700 mb-1">Себестоимость</label>
                                <input
                                    type="number"
                                    id="itemCostPrice"
                                    wire:model="costPrice"
                                    step="0.01"
                                    min="0"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="0.00"
                                >
                                @error('costPrice')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Описание --}}
                        <div>
                            <label for="itemDescription" class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
                            <textarea
                                id="itemDescription"
                                wire:model="description"
                                rows="3"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Описание блюда..."
                            ></textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Время приготовления --}}
                        <div>
                            <label for="itemCookingTime" class="block text-sm font-medium text-gray-700 mb-1">Время приготовления (мин)</label>
                            <input
                                type="number"
                                id="itemCookingTime"
                                wire:model="cookingTime"
                                min="0"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="15"
                            >
                            @error('cookingTime')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Доступность --}}
                        <div class="flex items-center gap-3">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="isAvailable" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                            <span class="text-sm font-medium text-gray-700">Доступно для заказа</span>
                        </div>

                        {{-- Кнопки --}}
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                            <button
                                type="button"
                                wire:click="$set('showModal', false)"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                            >
                                Отмена
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                            >
                                {{ $editingId ? 'Сохранить' : 'Создать' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
