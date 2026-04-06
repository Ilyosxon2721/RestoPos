<div>
    {{-- Заголовок --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Производство</h1>
            <p class="text-sm text-gray-500 mt-1">Производство блюд и полуфабрикатов из ингредиентов по техкартам</p>
        </div>
        <button wire:click="create"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
            <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Новое производство
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
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Себестоимость</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сотрудник</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Примечание</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($productions as $mv)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $mv->created_at->format('d.m.Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $mv->warehouse?->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $mv->ingredient?->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-medium {{ (float)$mv->quantity < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ (float)$mv->quantity < 0 ? '' : '+' }}{{ number_format((float)$mv->quantity, 3, '.', ' ') }}
                                {{ $mv->ingredient?->unit?->short_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-gray-500">
                                {{ number_format((float)$mv->cost_price, 2, '.', ' ') }} сум
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500 text-xs">{{ $mv->user?->first_name }}</td>
                            <td class="px-6 py-4 text-gray-400 text-xs max-w-[200px] truncate">{{ $mv->notes }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">Производства не найдены</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($productions->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">{{ $productions->links() }}</div>
        @endif
    </div>

    {{-- ===== Модалка нового производства ===== --}}
    @if ($showModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                <form wire:submit="save">
                    <div class="px-6 pt-6 pb-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Новое производство</h3>
                        <div class="space-y-4">
                            {{-- Склад --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Склад <span class="text-red-500">*</span></label>
                                <select wire:model.live="warehouseId" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Выберите склад...</option>
                                    @foreach ($this->warehouses as $wh)
                                        <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->branch?->name }})</option>
                                    @endforeach
                                </select>
                                @error('warehouseId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                {{-- Продукт --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Продукт (с техкартой) <span class="text-red-500">*</span></label>
                                    <select wire:model.live="productId" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Выберите продукт...</option>
                                        @foreach ($this->products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->unit?->short_name }})</option>
                                        @endforeach
                                    </select>
                                    @error('productId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                {{-- Количество --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Количество <span class="text-red-500">*</span></label>
                                    <input type="number" step="0.001" min="0" wire:model.live.debounce.300ms="quantity"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                           placeholder="0.000">
                                    @error('quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- Примечания --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Примечания</label>
                                <textarea wire:model="notes" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            </div>

                            {{-- Расчёт ингредиентов --}}
                            @if (count($calculatedIngredients) > 0)
                                <hr class="border-gray-200">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-3">Необходимые ингредиенты</label>
                                    <div class="space-y-2">
                                        @foreach ($calculatedIngredients as $ing)
                                            <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3 text-sm">
                                                <span class="font-medium text-gray-700">{{ $ing['name'] }}</span>
                                                <div class="flex items-center gap-4">
                                                    <span class="text-gray-500">
                                                        Требуется: <span class="font-medium text-gray-800">{{ $ing['required'] }} {{ $ing['unit'] }}</span>
                                                    </span>
                                                    <span class="{{ $ing['available'] >= $ing['required'] ? 'text-green-600' : 'text-red-600' }}">
                                                        Остаток: <span class="font-medium">{{ $ing['available'] }} {{ $ing['unit'] }}</span>
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    @if (!$canProduce)
                                        <div class="mt-3 bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
                                            Недостаточно ингредиентов на складе для производства.
                                        </div>
                                    @else
                                        <div class="mt-3 bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-700">
                                            Все ингредиенты доступны.
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-end space-x-3">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">Отмена</button>
                        <button type="submit"
                                @if(!$canProduce) disabled @endif
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            Произвести
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
