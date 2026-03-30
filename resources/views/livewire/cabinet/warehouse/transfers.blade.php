<div>
    {{-- Заголовок --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Перемещения</h1>
            <p class="text-sm text-gray-500 mt-1">Перемещение товаров между складами</p>
        </div>
        <button wire:click="create"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
            <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Новое перемещение
        </button>
    </div>

    {{-- Таблица --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Откуда</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Куда</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Позиций</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сотрудник</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($transfers as $transfer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $transfer->created_at->format('d.m.Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $transfer->fromWarehouse?->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-gray-400">
                                <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $transfer->toWarehouse?->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-gray-500">{{ $transfer->items->count() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $statusClass = match($transfer->status) {
                                        'received' => 'bg-green-100 text-green-800',
                                        'sent' => 'bg-blue-100 text-blue-800',
                                        'draft' => 'bg-gray-100 text-gray-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                    $statusLabel = match($transfer->status) {
                                        'received' => 'Получено',
                                        'sent' => 'Отправлено',
                                        'draft' => 'Черновик',
                                        'cancelled' => 'Отменено',
                                        default => $transfer->status,
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500 text-xs">{{ $transfer->user?->first_name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">Перемещения не найдены</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($transfers->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">{{ $transfers->links() }}</div>
        @endif
    </div>

    {{-- ===== Модалка ===== --}}
    @if ($showModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                <form wire:submit="save">
                    <div class="px-6 pt-6 pb-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Новое перемещение</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Откуда <span class="text-red-500">*</span></label>
                                    <select wire:model="fromWarehouseId" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Склад-отправитель...</option>
                                        @foreach ($this->warehouses as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->branch?->name }})</option>
                                        @endforeach
                                    </select>
                                    @error('fromWarehouseId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Куда <span class="text-red-500">*</span></label>
                                    <select wire:model="toWarehouseId" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Склад-получатель...</option>
                                        @foreach ($this->warehouses as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->branch?->name }})</option>
                                        @endforeach
                                    </select>
                                    @error('toWarehouseId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Примечания</label>
                                <textarea wire:model="notes" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            </div>
                            <hr class="border-gray-200">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <label class="block text-sm font-semibold text-gray-700">Позиции <span class="text-red-500">*</span></label>
                                    <button type="button" wire:click="addItem" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Добавить
                                    </button>
                                </div>
                                @error('transferItems') <p class="mb-2 text-sm text-red-600">{{ $message }}</p> @enderror
                                <div class="space-y-2">
                                    @foreach ($transferItems as $index => $item)
                                        <div class="flex items-start gap-2 bg-blue-50 rounded-lg p-3" wire:key="tr-item-{{ $index }}">
                                            <div class="flex-1 min-w-0">
                                                <select wire:model="transferItems.{{ $index }}.ingredient_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                                    <option value="">Ингредиент...</option>
                                                    @foreach ($this->ingredients as $ingredient)
                                                        <option value="{{ $ingredient->id }}">{{ $ingredient->name }} ({{ $ingredient->unit?->short_name }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="w-28">
                                                <input type="number" step="0.001" min="0" wire:model="transferItems.{{ $index }}.quantity" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Кол-во">
                                            </div>
                                            <button type="button" wire:click="removeItem({{ $index }})" class="mt-1 text-red-400 hover:text-red-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                                @if (empty($transferItems))
                                    <div class="text-center py-4 text-gray-400 text-sm border border-dashed border-gray-300 rounded-lg">Нажмите «Добавить»</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-end space-x-3">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">Отмена</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">Провести перемещение</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
