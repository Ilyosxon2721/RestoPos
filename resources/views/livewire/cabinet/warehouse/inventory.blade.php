<div>
    {{-- Заголовок --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Инвентаризации</h1>
            <p class="text-sm text-gray-500 mt-1">Проверка фактических остатков на складе</p>
        </div>
        <button wire:click="create"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
            <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Новая инвентаризация
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
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Позиций</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Расхождений</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сотрудник</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($inventories as $inv)
                        @php
                            $discrepancies = $inv->items->filter(fn($item) => $item->actual_quantity !== null && abs((float)$item->actual_quantity - (float)$item->expected_quantity) > 0.001)->count();
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $inv->created_at->format('d.m.Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $inv->warehouse?->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-gray-500">{{ $inv->items->count() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if ($discrepancies > 0)
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">{{ $discrepancies }}</span>
                                @else
                                    <span class="text-green-600 text-xs font-medium">Нет</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $statusClass = match($inv->status) {
                                        'completed' => 'bg-green-100 text-green-800',
                                        'in_progress' => 'bg-blue-100 text-blue-800',
                                        'draft' => 'bg-gray-100 text-gray-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                    $statusLabel = match($inv->status) {
                                        'completed' => 'Завершена',
                                        'in_progress' => 'В процессе',
                                        'draft' => 'Черновик',
                                        'cancelled' => 'Отменена',
                                        default => $inv->status,
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500 text-xs">{{ $inv->user?->first_name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">Инвентаризации не найдены</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($inventories->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">{{ $inventories->links() }}</div>
        @endif
    </div>

    {{-- ===== Модалка ===== --}}
    @if ($showModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto">
                <form wire:submit="save">
                    <div class="px-6 pt-6 pb-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Новая инвентаризация</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Склад <span class="text-red-500">*</span></label>
                                    <select wire:model="warehouseId" wire:change="loadStockItems"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Выберите склад...</option>
                                        @foreach ($this->warehouses as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->branch?->name }})</option>
                                        @endforeach
                                    </select>
                                    @error('warehouseId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Примечания</label>
                                    <input type="text" wire:model="notes" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Комментарий...">
                                </div>
                            </div>

                            @if (count($inventoryItems) > 0)
                                <hr class="border-gray-200">
                                <div>
                                    <p class="text-sm font-semibold text-gray-700 mb-3">Введите фактические остатки:</p>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Ингредиент</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Ожидаемое</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Фактическое</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Разница</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach ($inventoryItems as $index => $item)
                                                    <tr wire:key="inv-item-{{ $index }}">
                                                        <td class="px-4 py-2 text-gray-700">{{ $item['ingredient_name'] }}</td>
                                                        <td class="px-4 py-2 text-right text-gray-500 font-mono text-xs">{{ number_format((float)$item['expected_quantity'], 3) }}</td>
                                                        <td class="px-4 py-2 text-right">
                                                            <input type="number" step="0.001" min="0"
                                                                   wire:model="inventoryItems.{{ $index }}.actual_quantity"
                                                                   class="w-28 text-right rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                                   placeholder="0.000">
                                                        </td>
                                                        <td class="px-4 py-2 text-right font-mono text-xs">
                                                            @if ($item['actual_quantity'] !== '')
                                                                @php $diff = (float)$item['actual_quantity'] - (float)$item['expected_quantity']; @endphp
                                                                <span class="{{ $diff < 0 ? 'text-red-600' : ($diff > 0 ? 'text-green-600' : 'text-gray-400') }}">
                                                                    {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 3) }}
                                                                </span>
                                                            @else
                                                                <span class="text-gray-300">—</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @elseif ($warehouseId)
                                <div class="text-center py-4 text-gray-400 text-sm">На этом складе нет товаров с остатками</div>
                            @else
                                <div class="text-center py-4 text-gray-400 text-sm border border-dashed border-gray-300 rounded-lg">Выберите склад для загрузки позиций</div>
                            @endif
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-end space-x-3">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">Отмена</button>
                        @if (count($inventoryItems) > 0)
                            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">Завершить инвентаризацию</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
