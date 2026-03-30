<div>
    {{-- Заголовок --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Движение товаров</h1>
        <p class="text-sm text-gray-500 mt-1">История всех складских операций</p>
    </div>

    {{-- Фильтры --}}
    <div class="mb-6 flex flex-wrap items-center gap-4">
        <div class="relative max-w-md flex-1">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search"
                   class="block w-full rounded-lg border-gray-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   placeholder="Поиск по ингредиенту...">
        </div>
        <select wire:model.live="typeFilter"
                class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">Все типы</option>
            <option value="supply">Поступление</option>
            <option value="sale">Продажа</option>
            <option value="write_off">Списание</option>
            <option value="transfer_in">Приход (перемещение)</option>
            <option value="transfer_out">Расход (перемещение)</option>
            <option value="production">Производство</option>
            <option value="inventory">Инвентаризация</option>
            <option value="return">Возврат</option>
        </select>
    </div>

    {{-- Таблица --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тип</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ингредиент</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Склад</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Кол-во</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Цена</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сотрудник</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Примечание</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($movements as $mv)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 whitespace-nowrap text-gray-500 text-xs">{{ $mv->created_at->format('d.m.Y H:i') }}</td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                @php
                                    $typeColor = match($mv->type->value) {
                                        'supply', 'transfer_in', 'return' => 'bg-green-100 text-green-800',
                                        'sale', 'write_off', 'transfer_out', 'production' => 'bg-red-100 text-red-800',
                                        'inventory' => 'bg-blue-100 text-blue-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $typeColor }}">
                                    {{ $mv->type->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-gray-700">{{ $mv->ingredient?->name }}</td>
                            <td class="px-6 py-3 whitespace-nowrap text-gray-500 text-xs">{{ $mv->warehouse?->name }}</td>
                            <td class="px-6 py-3 whitespace-nowrap text-right font-mono text-sm {{ (float) $mv->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ (float) $mv->quantity > 0 ? '+' : '' }}{{ number_format((float) $mv->quantity, 3) }}
                                <span class="text-gray-400 text-xs">{{ $mv->ingredient?->unit?->short_name }}</span>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-right text-gray-500">
                                {{ $mv->cost_price ? number_format((float) $mv->cost_price, 0, '.', ' ') : '—' }}
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-gray-500 text-xs">{{ $mv->user?->first_name }}</td>
                            <td class="px-6 py-3 text-gray-400 text-xs max-w-[200px] truncate">{{ $mv->notes ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">Движения не найдены</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($movements->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">{{ $movements->links() }}</div>
        @endif
    </div>
</div>
