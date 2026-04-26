<div>
    {{-- Заголовок --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Тех. карты</h1>
            <p class="text-sm text-gray-500 mt-1">Рецептуры блюд — состав ингредиентов и нормы расхода</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="/{{ request()->segment(1) }}/menu/import?tab=tech-cards"
               class="inline-flex items-center rounded-lg bg-white border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Импорт из Poster
            </a>
            <button wire:click="create"
                    class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Создать тех. карту
            </button>
        </div>
    </div>

    {{-- Поиск --}}
    <div class="mb-6">
        <div class="relative max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search"
                   class="block w-full rounded-lg border-gray-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   placeholder="Поиск по названию блюда...">
        </div>
    </div>

    {{-- Список тех. карт --}}
    <div class="space-y-4">
        @forelse ($techCards as $card)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">{{ $card->product->name }}</h3>
                            <p class="text-xs text-gray-500">
                                Выход: {{ number_format((float) $card->output_quantity, 1) }} |
                                Ингредиентов: {{ $card->items->count() }} |
                                Версия: {{ $card->version }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        @if ($card->is_active)
                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Активна</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Неактивна</span>
                        @endif
                        <button wire:click="edit({{ $card->id }})" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Изменить</button>
                        <button wire:click="toggleActive({{ $card->id }})"
                                class="text-sm font-medium {{ $card->is_active ? 'text-amber-600 hover:text-amber-800' : 'text-green-600 hover:text-green-800' }}">
                            {{ $card->is_active ? 'Откл.' : 'Вкл.' }}
                        </button>
                        <button wire:click="deleteTechCard({{ $card->id }})"
                                wire:confirm="Удалить тех. карту для {{ $card->product->name }}?"
                                class="text-red-600 hover:text-red-800 text-sm font-medium">Удалить</button>
                    </div>
                </div>

                {{-- Таблица ингредиентов --}}
                @if ($card->items->isNotEmpty())
                    <div class="border-t border-gray-100">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-6 py-2 text-left text-xs font-medium text-gray-400 uppercase">Ингредиент</th>
                                    <th class="px-6 py-2 text-right text-xs font-medium text-gray-400 uppercase">Нетто</th>
                                    <th class="px-6 py-2 text-right text-xs font-medium text-gray-400 uppercase">Потери %</th>
                                    <th class="px-6 py-2 text-right text-xs font-medium text-gray-400 uppercase">Брутто</th>
                                    <th class="px-6 py-2 text-left text-xs font-medium text-gray-400 uppercase">Ед.</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($card->items as $item)
                                    <tr>
                                        <td class="px-6 py-2 text-gray-700">{{ $item->ingredient?->name ?? '—' }}</td>
                                        <td class="px-6 py-2 text-right text-gray-600 font-mono text-xs">{{ number_format((float) $item->quantity, 3) }}</td>
                                        <td class="px-6 py-2 text-right text-gray-400 text-xs">{{ (float) $item->loss_percent > 0 ? number_format((float) $item->loss_percent, 1) . '%' : '—' }}</td>
                                        <td class="px-6 py-2 text-right text-gray-600 font-mono text-xs">{{ number_format($item->gross_quantity, 3) }}</td>
                                        <td class="px-6 py-2 text-gray-400 text-xs">{{ $item->ingredient?->unit?->short_name ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if ($card->description)
                    <div class="px-6 py-3 border-t border-gray-100 text-xs text-gray-500">
                        {{ $card->description }}
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-gray-500">Тех. карты не найдены</p>
                <p class="text-sm text-gray-400 mt-1">Создайте тех. карту, чтобы привязать ингредиенты к блюду</p>
            </div>
        @endforelse
    </div>

    @if ($techCards->hasPages())
        <div class="mt-6">
            {{ $techCards->links() }}
        </div>
    @endif

    {{-- ===== Модалка: Создание / Редактирование тех. карты ===== --}}
    @if ($showModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                <form wire:submit="save">
                    <div class="px-6 pt-6 pb-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            {{ $editingId ? 'Редактировать тех. карту' : 'Новая тех. карта' }}
                        </h3>

                        <div class="space-y-4">
                            {{-- Блюдо --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Блюдо <span class="text-red-500">*</span></label>
                                <select wire:model="productId"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Выберите блюдо...</option>
                                    @foreach ($this->products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                                @error('productId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Выход и описание --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Выход (кол-во порций)</label>
                                    <input type="number" step="0.001" min="0.001" wire:model="outputQuantity"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="flex items-center space-x-3 mt-7">
                                        <input type="checkbox" wire:model="isActive" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-gray-700">Активна</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Описание --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
                                <textarea wire:model="description" rows="2"
                                          class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                          placeholder="Краткое описание рецепта..."></textarea>
                            </div>

                            {{-- Инструкция приготовления --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Инструкция приготовления</label>
                                <textarea wire:model="cookingInstructions" rows="3"
                                          class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                          placeholder="Пошаговая инструкция..."></textarea>
                            </div>

                            <hr class="border-gray-200">

                            {{-- Ингредиенты --}}
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <label class="block text-sm font-semibold text-gray-700">Ингредиенты <span class="text-red-500">*</span></label>
                                    <button type="button" wire:click="addItem"
                                            class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Добавить
                                    </button>
                                </div>

                                @error('cardItems') <p class="mb-2 text-sm text-red-600">{{ $message }}</p> @enderror

                                <div class="space-y-2">
                                    @foreach ($cardItems as $index => $item)
                                        <div class="flex items-start gap-2 bg-gray-50 rounded-lg p-3" wire:key="item-{{ $index }}">
                                            <div class="flex-1 min-w-0">
                                                <select wire:model="cardItems.{{ $index }}.ingredient_id"
                                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                                    <option value="">Ингредиент...</option>
                                                    @foreach ($this->ingredients as $ingredient)
                                                        <option value="{{ $ingredient->id }}">{{ $ingredient->name }} ({{ $ingredient->unit?->short_name }})</option>
                                                    @endforeach
                                                </select>
                                                @error("cardItems.{$index}.ingredient_id") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                            <div class="w-24">
                                                <input type="number" step="0.001" min="0" wire:model="cardItems.{{ $index }}.quantity"
                                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                       placeholder="Кол-во">
                                                @error("cardItems.{$index}.quantity") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                            <div class="w-20">
                                                <input type="number" step="0.1" min="0" max="100" wire:model="cardItems.{{ $index }}.loss_percent"
                                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                       placeholder="% пот.">
                                            </div>
                                            <button type="button" wire:click="removeItem({{ $index }})"
                                                    class="mt-1 text-red-400 hover:text-red-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>

                                @if (empty($cardItems))
                                    <div class="text-center py-4 text-gray-400 text-sm border border-dashed border-gray-300 rounded-lg">
                                        Нажмите «Добавить» чтобы добавить ингредиент
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-end space-x-3">
                        <button type="button" wire:click="$set('showModal', false)"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            Отмена
                        </button>
                        <button type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                            {{ $editingId ? 'Сохранить' : 'Создать' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
