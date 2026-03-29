<div>
    {{-- Заголовок --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Остатки на складе</h1>
        <p class="mt-1 text-sm text-gray-500">Текущие остатки товаров и ингредиентов</p>
    </div>

    {{-- Фильтры --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center">
        {{-- Поиск --}}
        <div class="relative flex-1 max-w-sm">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Поиск по названию товара..."
                class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
        </div>

        {{-- Фильтр низкого остатка --}}
        <label class="inline-flex cursor-pointer items-center gap-2">
            <input
                wire:model.live="lowStockOnly"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
            >
            <span class="text-sm font-medium text-gray-700">Только низкий остаток</span>
        </label>
    </div>

    {{-- Таблица остатков --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Товар</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Кол-во</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Мин. кол-во</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Ед. изм.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Статус</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($stocks as $stock)
                        @php
                            $isLow = $stock->min_quantity && $stock->quantity <= $stock->min_quantity;
                            $isEmpty = $stock->quantity <= 0;
                        @endphp
                        <tr @class([
                            'transition-colors',
                            'bg-red-50' => $isEmpty,
                            'bg-amber-50' => $isLow && !$isEmpty,
                            'hover:bg-gray-50' => !$isLow && !$isEmpty,
                        ])>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                {{ $stock->product?->name ?? '---' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                {{ number_format((float) $stock->quantity, 2, ',', ' ') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                {{ $stock->min_quantity ? number_format((float) $stock->min_quantity, 2, ',', ' ') : '---' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                {{ $stock->unit ?? $stock->product?->unit ?? '---' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @if($isEmpty)
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                        Нет в наличии
                                    </span>
                                @elseif($isLow)
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                                        Низкий остаток
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                                        В наличии
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                                Товары не найдены
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Пагинация --}}
        @if($stocks->hasPages())
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $stocks->links() }}
            </div>
        @endif
    </div>
</div>
