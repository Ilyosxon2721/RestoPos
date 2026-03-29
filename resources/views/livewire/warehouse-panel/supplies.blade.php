<div>
    {{-- Заголовок --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Поставки</h1>
            <p class="mt-1 text-sm text-gray-500">История приёмки товаров</p>
        </div>
    </div>

    {{-- Таблица поставок --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Номер</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Поставщик</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Дата</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Сумма</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Статус</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($supplies as $supply)
                        @php
                            $statusConfig = match($supply->status ?? 'pending') {
                                'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'label' => 'Ожидает'],
                                'received', 'completed' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Принята'],
                                'cancelled' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'label' => 'Отменена'],
                                'partial' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'Частично'],
                                default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'label' => $supply->status ?? '---'],
                            };
                        @endphp

                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                #{{ $supply->number ?? $supply->id }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                {{ $supply->supplier?->name ?? '---' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                {{ $supply->created_at?->format('d.m.Y H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                {{ number_format((float) ($supply->total_amount ?? 0), 0, ',', ' ') }} сум
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                    {{ $statusConfig['label'] }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <button class="text-emerald-600 hover:text-emerald-800 font-medium transition-colors">
                                    Подробнее
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                                Поставок пока нет
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Пагинация --}}
        @if($supplies->hasPages())
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $supplies->links() }}
            </div>
        @endif
    </div>
</div>
