<div class="min-h-screen bg-gray-50 pb-6">
    {{-- Заголовок --}}
    <div class="bg-white px-4 py-4 shadow-sm">
        <h1 class="text-xl font-bold text-gray-900">Мои заказы</h1>
    </div>

    {{-- Табы статусов --}}
    <div class="flex border-b border-gray-200 bg-white px-4">
        <button
            wire:click="$set('statusFilter', 'active')"
            @class([
                'border-b-2 px-4 py-3 text-sm font-medium transition-colors',
                'border-emerald-600 text-emerald-600' => $statusFilter === 'active',
                'border-transparent text-gray-500 hover:text-gray-700' => $statusFilter !== 'active',
            ])
        >
            Активные
        </button>
        <button
            wire:click="$set('statusFilter', 'completed')"
            @class([
                'border-b-2 px-4 py-3 text-sm font-medium transition-colors',
                'border-emerald-600 text-emerald-600' => $statusFilter === 'completed',
                'border-transparent text-gray-500 hover:text-gray-700' => $statusFilter !== 'completed',
            ])
        >
            Завершённые
        </button>
    </div>

    {{-- Список заказов --}}
    <div class="space-y-3 px-4 pt-4">
        @forelse($orders as $order)
            @php
                $statusStyles = match($order->status) {
                    'new', 'pending' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'Новый'],
                    'in_progress', 'preparing' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'label' => 'Готовится'],
                    'ready' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Готов'],
                    'completed' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'label' => 'Завершён'],
                    'cancelled' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'label' => 'Отменён'],
                    default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'label' => $order->status],
                };
            @endphp

            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                {{-- Заголовок карточки --}}
                <div class="mb-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-lg font-bold text-gray-900">#{{ $order->order_number ?? $order->id }}</span>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusStyles['bg'] }} {{ $statusStyles['text'] }}">
                            {{ $statusStyles['label'] }}
                        </span>
                    </div>
                    <span class="text-sm text-gray-500">{{ $order->created_at?->format('H:i') }}</span>
                </div>

                {{-- Информация --}}
                <div class="flex items-center gap-4 text-sm text-gray-600">
                    {{-- Стол --}}
                    <div class="flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5z"/>
                        </svg>
                        <span>Стол {{ $order->table?->number ?? '---' }}</span>
                    </div>

                    {{-- Позиции --}}
                    <div class="flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                        </svg>
                        <span>{{ $order->items_count ?? $order->items?->count() ?? 0 }} поз.</span>
                    </div>
                </div>

                {{-- Сумма --}}
                <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3">
                    <span class="text-sm text-gray-500">Сумма</span>
                    <span class="text-lg font-bold text-gray-900">{{ number_format((float) $order->total_amount, 0, ',', ' ') }} сум</span>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-20 text-gray-400">
                <svg class="mb-3 h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                </svg>
                <p class="text-sm">
                    @if($statusFilter === 'active')
                        Нет активных заказов
                    @else
                        Нет завершённых заказов
                    @endif
                </p>
            </div>
        @endforelse
    </div>
</div>
