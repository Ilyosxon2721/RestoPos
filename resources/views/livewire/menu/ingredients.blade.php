<div>
    {{-- Заголовок --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Ингредиенты</h1>
            <p class="text-sm text-gray-500 mt-1">Управление ингредиентами для тех. карт и складского учёта</p>
        </div>
        <button wire:click="create"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
            <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Добавить ингредиент
        </button>
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
                   placeholder="Поиск по названию, SKU или штрих-коду...">
        </div>
    </div>

    {{-- Таблица --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ед. изм.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Мин. остаток</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Себестоимость</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($ingredients as $ingredient)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $ingredient->name }}</div>
                                @if ($ingredient->barcode)
                                    <div class="text-xs text-gray-400 font-mono">{{ $ingredient->barcode }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                {{ $ingredient->unit?->short_name ?? $ingredient->unit?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500 font-mono text-xs">
                                {{ $ingredient->sku ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-gray-500">
                                {{ number_format((float) $ingredient->min_stock, 1) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-gray-500">
                                {{ number_format((float) $ingredient->current_cost, 0, '.', ' ') }} сум
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if ($ingredient->is_active)
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Активен</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Неактивен</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                <button wire:click="edit({{ $ingredient->id }})" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Изменить</button>
                                <button wire:click="toggleActive({{ $ingredient->id }})"
                                        class="text-sm font-medium {{ $ingredient->is_active ? 'text-amber-600 hover:text-amber-800' : 'text-green-600 hover:text-green-800' }}">
                                    {{ $ingredient->is_active ? 'Откл.' : 'Вкл.' }}
                                </button>
                                <button wire:click="deleteIngredient({{ $ingredient->id }})"
                                        wire:confirm="Удалить ингредиент {{ $ingredient->name }}?"
                                        class="text-red-600 hover:text-red-800 text-sm font-medium">Удалить</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                Ингредиенты не найдены
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($ingredients->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $ingredients->links() }}
            </div>
        @endif
    </div>

    {{-- ===== Модалка: Создание / Редактирование ===== --}}
    @if ($showModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                <form wire:submit="save">
                    <div class="px-6 pt-6 pb-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            {{ $editingId ? 'Редактировать ингредиент' : 'Новый ингредиент' }}
                        </h3>

                        <div class="space-y-4">
                            {{-- Название --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Название <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="name"
                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                       placeholder="Например: Мука пшеничная">
                                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Единица измерения --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Единица измерения <span class="text-red-500">*</span></label>
                                <select wire:model="unitId"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Выберите...</option>
                                    @foreach ($this->units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->short_name }})</option>
                                    @endforeach
                                </select>
                                @error('unitId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- SKU и Штрих-код --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                                    <input type="text" wire:model="sku"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                           placeholder="Артикул">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Штрих-код</label>
                                    <input type="text" wire:model="barcode"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                           placeholder="EAN-13">
                                </div>
                            </div>

                            <hr class="border-gray-200">

                            {{-- Мин. остаток и Себестоимость --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Мин. остаток</label>
                                    <input type="number" step="0.1" wire:model="minStock"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Себестоимость (сум)</label>
                                    <input type="number" step="0.01" wire:model="currentCost"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>

                            {{-- Потери и Срок годности --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Потери при обработке (%)</label>
                                    <input type="number" step="0.1" min="0" max="100" wire:model="lossPercent"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Срок годности (дней)</label>
                                    <input type="number" min="1" wire:model="shelfLifeDays"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                           placeholder="—">
                                </div>
                            </div>

                            {{-- Активность --}}
                            <label class="flex items-center space-x-3">
                                <input type="checkbox" wire:model="isActive" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">Активен</span>
                            </label>
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
