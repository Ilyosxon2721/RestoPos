<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Заголовок --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Склад</h1>
            <div class="flex items-center space-x-3">
                <label class="inline-flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        wire:model.live="showLowStock"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    />
                    <span class="ml-2 text-sm text-gray-700">Только низкий остаток</span>
                </label>
            </div>
        </div>

        {{-- Поиск --}}
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <input
                type="text"
                wire:model.live.debounce.300ms="searchQuery"
                placeholder="Поиск по названию..."
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
        </div>

        {{-- Таблица --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Наименование</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Единица</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Текущий остаток</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Мин. остаток</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Обновлено</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($items as $item)
                            @php
                                $currentStock = (float) $item->current_stock;
                                $minStock = (float) ($item->min_stock ?? 0);

                                if ($currentStock <= 0) {
                                    $statusLabel = 'Нет в наличии';
                                    $statusColor = 'red';
                                } elseif ($currentStock <= $minStock) {
                                    $statusLabel = 'Низкий остаток';
                                    $statusColor = 'orange';
                                } else {
                                    $statusLabel = 'В наличии';
                                    $statusColor = 'green';
                                }
                            @endphp
                            <tr class="hover:bg-gray-50 {{ $currentStock <= $minStock ? 'bg-red-50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $item->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $item->unit ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $currentStock <= $minStock ? 'text-red-600 font-bold' : 'text-gray-900' }}">
                                    {{ number_format($currentStock, 2, '.', ' ') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($minStock, 2, '.', ' ') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $item->last_updated ? \Carbon\Carbon::parse($item->last_updated)->format('d.m.Y H:i') : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    Товары не найдены
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Пагинация --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $items->links() }}
            </div>
        </div>
    </div>
</div>
