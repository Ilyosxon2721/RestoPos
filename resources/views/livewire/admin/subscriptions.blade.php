<div>
    {{-- Заголовок --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Подписки</h1>
            <p class="text-sm text-gray-500 mt-1">Управление подписками организаций</p>
        </div>
    </div>

    {{-- Фильтры --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-4 sm:mb-6">
        <div class="p-3 sm:p-4">
            <select wire:model.live="statusFilter"
                    class="w-full sm:w-64 rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 focus:outline-none transition">
                <option value="">Все статусы</option>
                <option value="active">Активные</option>
                <option value="trial">Триал</option>
                <option value="cancelled">Отмененные</option>
                <option value="expired">Истекшие</option>
            </select>
        </div>
    </div>

    {{-- Мобильные карточки --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($subscriptions as $sub)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-start justify-between mb-2">
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-gray-900 truncate">{{ $sub->organization?->name ?? '---' }}</p>
                        <p class="text-xs text-gray-500">{{ $sub->plan?->name ?? '---' }}</p>
                    </div>
                    @switch($sub->status)
                        @case('active')
                            <span class="ml-2 flex-shrink-0 inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Активна</span>
                            @break
                        @case('trial')
                            <span class="ml-2 flex-shrink-0 inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">Триал</span>
                            @break
                        @case('cancelled')
                            <span class="ml-2 flex-shrink-0 inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Отменена</span>
                            @break
                        @case('expired')
                            <span class="ml-2 flex-shrink-0 inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">Истекла</span>
                            @break
                        @default
                            <span class="ml-2 flex-shrink-0 inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">{{ $sub->status }}</span>
                    @endswitch
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-xs text-gray-500">
                        {{ $sub->starts_at?->format('d.m.Y') ?? $sub->created_at?->format('d.m.Y') ?? '---' }}
                        &mdash;
                        {{ $sub->ends_at?->format('d.m.Y') ?? '---' }}
                    </div>
                    <div class="flex items-center space-x-2">
                        @if ($sub->status === 'active' || $sub->status === 'trial')
                            <button wire:click="cancel({{ $sub->id }})"
                                    wire:confirm="Вы уверены, что хотите отменить эту подписку?"
                                    class="rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 transition-colors">
                                Отменить
                            </button>
                        @endif
                        @if ($sub->status === 'cancelled' || $sub->status === 'expired')
                            <button wire:click="activate({{ $sub->id }})"
                                    wire:confirm="Вы уверены, что хотите активировать эту подписку?"
                                    class="rounded-md bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700 hover:bg-green-100 transition-colors">
                                Активировать
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Подписки не найдены
            </div>
        @endforelse
    </div>

    {{-- Десктопная таблица --}}
    <div class="hidden sm:block bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Организация</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тариф</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="hidden lg:table-cell px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Начало</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Окончание</th>
                        <th class="px-4 lg:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($subscriptions as $sub)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $sub->organization?->name ?? '---' }}</div>
                            </td>
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-gray-500">
                                {{ $sub->plan?->name ?? '---' }}
                            </td>
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                @switch($sub->status)
                                    @case('active')
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Активна</span>
                                        @break
                                    @case('trial')
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">Триал</span>
                                        @break
                                    @case('cancelled')
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Отменена</span>
                                        @break
                                    @case('expired')
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">Истекла</span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">{{ $sub->status }}</span>
                                @endswitch
                            </td>
                            <td class="hidden lg:table-cell px-4 lg:px-6 py-4 whitespace-nowrap text-gray-500">
                                {{ $sub->starts_at?->format('d.m.Y') ?? $sub->created_at?->format('d.m.Y') ?? '---' }}
                            </td>
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-gray-500">
                                {{ $sub->ends_at?->format('d.m.Y') ?? '---' }}
                            </td>
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    @if ($sub->status === 'active' || $sub->status === 'trial')
                                        <button wire:click="cancel({{ $sub->id }})"
                                                wire:confirm="Вы уверены, что хотите отменить эту подписку?"
                                                class="inline-flex items-center rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 transition-colors">
                                            Отменить
                                        </button>
                                    @endif
                                    @if ($sub->status === 'cancelled' || $sub->status === 'expired')
                                        <button wire:click="activate({{ $sub->id }})"
                                                wire:confirm="Вы уверены, что хотите активировать эту подписку?"
                                                class="inline-flex items-center rounded-md bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700 hover:bg-green-100 transition-colors">
                                            Активировать
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Подписки не найдены
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($subscriptions->hasPages())
            <div class="px-4 lg:px-6 py-4 border-t border-gray-200">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>

    @if ($subscriptions->hasPages())
        <div class="sm:hidden mt-4">
            {{ $subscriptions->links() }}
        </div>
    @endif
</div>
