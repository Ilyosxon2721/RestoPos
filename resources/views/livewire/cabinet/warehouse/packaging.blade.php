<div>
    {{-- Заголовок --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Фасовка</h1>
            <p class="text-sm text-gray-500 mt-1">Перефасовка ингредиентов в другую тару или единицы измерения</p>
        </div>
        <button wire:click="create"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
            <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Новая фасовка
        </button>
    </div>

    {{-- Таблица --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Склад</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ингредиент</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Кол-во</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тип</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сотрудник</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($packagings as $mv)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $mv->created_at->format('d.m.Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $mv->warehouse?->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $mv->ingredient?->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-medium {{ (float)$mv->quantity < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ (float)$mv->quantity < 0 ? '' : '+' }}{{ number_format((float)$mv->quantity, 3, '.', ' ') }}
                                {{ $mv->ingredient?->unit?->short_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ((float)$mv->quantity < 0)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-100 text-red-800">Расход</span>
                                @else
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-800">Приход</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500 text-xs">{{ $mv->user?->first_name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">Фасовки не найдены</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($packagings->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">{{ $packagings->links() }}</div>
        @endif
    </div>

    {{-- ===== Модалка новой фасовки ===== --}}
    @if ($showModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                <form wire:submit="save">
                    <div class="px-6 pt-6 pb-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Новая фасовка</h3>
                        <div class="space-y-4">
                            {{-- Склад --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Склад <span class="text-red-500">*</span></label>
                                <select wire:model="warehouseId" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Выберите склад...</option>
                                    @foreach ($this->warehouses as $wh)
                                        <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->branch?->name }})</option>
                                    @endforeach
                                </select>
                                @error('warehouseId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Источник --}}
                            <div class="bg-red-50 rounded-lg p-4 space-y-3">
                                <h4 class="text-sm font-semibold text-red-800">Источник (списание)</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Ингредиент <span class="text-red-500">*</span></label>
                                        <select wire:model="sourceIngredientId" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">Выберите ингредиент...</option>
                                            @foreach ($this->ingredients as $ingredient)
                                                <option value="{{ $ingredient->id }}">{{ $ingredient->name }} ({{ $ingredient->unit?->short_name }})</option>
                                            @endforeach
                                        </select>
                                        @error('sourceIngredientId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Количество <span class="text-red-500">*</span></label>
                                        <input type="number" step="0.001" min="0" wire:model="sourceQuantity"
                                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                               placeholder="0.000">
                                        @error('sourceQuantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Стрелка --}}
                            <div class="flex justify-center">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                </svg>
                            </div>

                            {{-- Цель --}}
                            <div class="bg-green-50 rounded-lg p-4 space-y-3">
                                <h4 class="text-sm font-semibold text-green-800">Результат (приход)</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Ингредиент <span class="text-red-500">*</span></label>
                                        <select wire:model="targetIngredientId" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">Выберите ингредиент...</option>
                                            @foreach ($this->ingredients as $ingredient)
                                                <option value="{{ $ingredient->id }}">{{ $ingredient->name }} ({{ $ingredient->unit?->short_name }})</option>
                                            @endforeach
                                        </select>
                                        @error('targetIngredientId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Количество <span class="text-red-500">*</span></label>
                                        <input type="number" step="0.001" min="0" wire:model="targetQuantity"
                                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                               placeholder="0.000">
                                        @error('targetQuantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Примечания --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Примечания</label>
                                <textarea wire:model="notes" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-end space-x-3">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">Отмена</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">Провести фасовку</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
