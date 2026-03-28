<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Заголовок --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Заказы</h1>
        </div>

        {{-- Уведомления --}}
        @if (session()->has('success'))
            <div class="mb-4 rounded-md bg-green-50 p-4">
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 rounded-md bg-red-50 p-4">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Фильтры --}}
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Поиск --}}
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Поиск</label>
                    <input
                        type="text"
                        id="search"
                        wire:model.live.debounce.300ms="searchQuery"
                        placeholder="Номер заказа или официант..."
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                </div>

                {{-- Статус --}}
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Статус</label>
                    <select
                        id="status"
                        wire:model.live="filterStatus"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                        <option value="">Все статусы</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Дата --}}
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Дата</label>
                    <input
                        type="date"
                        id="date"
                        wire:model.live="filterDate"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                </div>
            </div>
        </div>

        {{-- Таблица заказов --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">№ Заказа</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Стол</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Официант</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Позиций</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Оплата</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Создан</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $order->order_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $order->table?->name ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $order->waiter?->name ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $order->items->count() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ number_format($order->total_amount, 2, '.', ' ') }} ₽
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusEnum = \App\Support\Enums\OrderStatus::tryFrom($order->status);
                                        $color = $statusEnum ? $statusEnum->color() : 'gray';
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        bg-{{ $color }}-100 text-{{ $color }}-800">
                                        {{ $statusEnum?->label() ?? $order->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $paymentEnum = \App\Support\Enums\PaymentStatus::tryFrom($order->payment_status);
                                        $payColor = $paymentEnum ? $paymentEnum->color() : 'gray';
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        bg-{{ $payColor }}-100 text-{{ $payColor }}-800">
                                        {{ $paymentEnum?->label() ?? $order->payment_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $order->created_at->format('d.m.Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <button
                                        wire:click="viewOrder({{ $order->id }})"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        Просмотр
                                    </button>
                                    @if (!in_array($order->status, [\App\Support\Enums\OrderStatus::Completed->value, \App\Support\Enums\OrderStatus::Cancelled->value]))
                                        <button
                                            wire:click="cancelOrder({{ $order->id }})"
                                            wire:confirm="Вы уверены, что хотите отменить заказ?"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Отменить
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                    Заказы не найдены
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Пагинация --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $orders->links() }}
            </div>
        </div>
    </div>

    {{-- Модальное окно деталей заказа --}}
    @if ($showDetailModal && $selectedOrder)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeDetailModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Заказ {{ $selectedOrder->order_number }}
                        </h3>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Стол:</span>
                                <span class="font-medium">{{ $selectedOrder->table?->name ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Официант:</span>
                                <span class="font-medium">{{ $selectedOrder->waiter?->name ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Дата:</span>
                                <span class="font-medium">{{ $selectedOrder->created_at->format('d.m.Y H:i') }}</span>
                            </div>

                            <hr class="my-3">

                            <h4 class="font-medium text-gray-900">Позиции:</h4>
                            @foreach ($selectedOrder->items as $item)
                                <div class="flex justify-between">
                                    <span>{{ $item->menuItem?->name ?? $item->name }} x{{ $item->quantity }}</span>
                                    <span class="font-medium">{{ number_format($item->total_price, 2, '.', ' ') }} ₽</span>
                                </div>
                            @endforeach

                            <hr class="my-3">

                            <div class="flex justify-between text-base font-bold">
                                <span>Итого:</span>
                                <span>{{ number_format($selectedOrder->total_amount, 2, '.', ' ') }} ₽</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button
                            type="button"
                            wire:click="closeDetailModal"
                            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Закрыть
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
