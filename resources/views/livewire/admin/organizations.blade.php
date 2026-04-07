<div>
    {{-- Заголовок --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Организации</h1>
            <p class="text-sm text-gray-500 mt-1">Управление организациями платформы</p>
        </div>
    </div>

    {{-- Фильтры --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-4 sm:mb-6">
        <div class="p-3 sm:p-4">
            <input wire:model.live.debounce.300ms="search"
                   type="text"
                   placeholder="Поиск по названию..."
                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 focus:outline-none transition">
        </div>
    </div>

    {{-- Мобильные карточки --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($organizations as $org)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-start justify-between mb-3">
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-gray-900 truncate">{{ $org->name }}</p>
                        @if ($org->legal_name)
                            <p class="text-xs text-gray-500 truncate">{{ $org->legal_name }}</p>
                        @endif
                    </div>
                    @if ($org->is_active)
                        <span class="ml-2 flex-shrink-0 inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Активна</span>
                    @else
                        <span class="ml-2 flex-shrink-0 inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Неактивна</span>
                    @endif
                </div>
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <div class="flex items-center gap-3">
                        <span>Филиалы: <strong class="text-gray-700">{{ $org->branches_count }}</strong></span>
                        <span>{{ $org->created_at?->format('d.m.Y') }}</span>
                    </div>
                    <button wire:click="toggleStatus({{ $org->id }})"
                            wire:confirm="Вы уверены, что хотите изменить статус организации?"
                            class="rounded-md px-3 py-1.5 text-xs font-medium transition-colors
                                   {{ $org->is_active ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                        {{ $org->is_active ? 'Деактивировать' : 'Активировать' }}
                    </button>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Организации не найдены
            </div>
        @endforelse
    </div>

    {{-- Десктопная таблица --}}
    <div class="hidden sm:block bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Филиалы</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Создана</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="px-4 lg:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($organizations as $org)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $org->name }}</div>
                                @if ($org->legal_name)
                                    <div class="text-xs text-gray-500">{{ $org->legal_name }}</div>
                                @endif
                            </td>
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-gray-500">
                                {{ $org->branches_count }}
                            </td>
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-gray-500">
                                {{ $org->created_at?->format('d.m.Y') }}
                            </td>
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                @if ($org->is_active)
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Активна</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Неактивна</span>
                                @endif
                            </td>
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-right">
                                <button wire:click="toggleStatus({{ $org->id }})"
                                        wire:confirm="Вы уверены, что хотите изменить статус организации?"
                                        class="inline-flex items-center rounded-md px-3 py-1.5 text-xs font-medium transition-colors
                                               {{ $org->is_active ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                                    {{ $org->is_active ? 'Деактивировать' : 'Активировать' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                Организации не найдены
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($organizations->hasPages())
            <div class="px-4 lg:px-6 py-4 border-t border-gray-200">
                {{ $organizations->links() }}
            </div>
        @endif
    </div>

    {{-- Мобильная пагинация --}}
    @if ($organizations->hasPages())
        <div class="sm:hidden mt-4">
            {{ $organizations->links() }}
        </div>
    @endif
</div>
