<div>
    {{-- Заголовок --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Заказы</h1>
        <p class="mt-1 text-sm text-gray-500">Управление заказами</p>
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
                wire:model.live="search"
                type="text"
                placeholder="Поиск по номеру заказа..."
                class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
        </div>

        {{-- Фильтр по статусу --}}
        <div>
            <select
                wire:model.live="statusFilter"
                class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 px-4 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
                <option value="">Все</option>
                <option value="new">Новый</option>
                <option value="accepted">Принят</option>
                <option value="preparing">Готовится</option>
                <option value="ready">Готов</option>
                <option value="completed">Завершён</option>
                <option value="cancelled">Отменён</option>
            </select>
        </div>
    </div>

    {{-- Таблица заказов --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Номер</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Стол</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Сумма</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Дата</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                #{{ $order->order_number }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                {{ $order->table?->number ? 'Стол ' . $order->table->number : '---' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @php
                                    $statusStyles = match($order->status) {
                                        'new' => 'bg-emerald-100 text-emerald-700',
                                        'accepted' => 'bg-green-100 text-green-700',
                                        'preparing' => 'bg-yellow-100 text-yellow-700',
                                        'ready' => 'bg-emerald-100 text-emerald-700',
                                        'completed' => 'bg-green-100 text-green-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                    $statusLabel = match($order->status) {
                                        'new' => 'Новый',
                                        'accepted' => 'Принят',
                                        'preparing' => 'Готовится',
                                        'ready' => 'Готов',
                                        'completed' => 'Завершён',
                                        'cancelled' => 'Отменён',
                                        default => $order->status,
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusStyles }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                {{ number_format((float) $order->total_amount, 0, ',', ' ') }} сум
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                {{ $order->created_at?->format('d.m.Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                                Заказы не найдены
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Пагинация --}}
        @if($orders->hasPages())
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
