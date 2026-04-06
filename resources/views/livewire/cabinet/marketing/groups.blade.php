<div>
    {{-- Заголовок --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Группы клиентов</h1>
            <p class="text-sm text-gray-500 mt-1">Управление группами клиентов и скидками</p>
        </div>
        <button wire:click="create"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
            <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Добавить группу
        </button>
    </div>

    {{-- Поиск --}}
    <div class="mb-6">
        <div class="relative max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search"
                   class="block w-full rounded-lg border-gray-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   placeholder="Поиск по названию группы...">
        </div>
    </div>

    {{-- Таблица групп --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Группа</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Скидка</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Бонус %</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Мин. трата</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Клиентов</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($groups as $group)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full mr-3 flex-shrink-0" style="background-color: {{ $group->color ?? '#6366f1' }}"></span>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $group->name }}</div>
                                        @if ($group->description)
                                            <div class="text-xs text-gray-500">{{ Str::limit($group->description, 50) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                    {{ $group->discount_percent }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                {{ $group->bonus_earn_percent }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                {{ number_format((float) $group->min_spent_to_join, 0, '.', ' ') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">
                                    {{ $group->customers_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($group->is_active ?? true)
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Активна</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Неактивна</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                <button wire:click="edit({{ $group->id }})"
                                        class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                    Изменить
                                </button>
                                <button wire:click="confirmDelete({{ $group->id }})"
                                        class="text-red-600 hover:text-red-800 text-sm font-medium">
                                    Удалить
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Группы клиентов не найдены
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($groups->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $groups->links() }}
            </div>
        @endif
    </div>

    {{-- ===== Модалка: Создание / Редактирование группы ===== --}}
    @if ($showModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                <form wire:submit="save">
                    <div class="px-6 pt-6 pb-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            {{ $editingId ? 'Редактировать группу' : 'Новая группа' }}
                        </h3>

                        <div class="space-y-4">
                            {{-- Название --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Название <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="name"
                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                       placeholder="Название группы">
                                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Описание --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
                                <textarea wire:model="description" rows="2"
                                          class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                          placeholder="Описание группы"></textarea>
                                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Скидка и Бонус --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Скидка (%) <span class="text-red-500">*</span></label>
                                    <input type="number" step="0.01" min="0" max="100" wire:model="discountPercent"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('discountPercent') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Бонус начисление (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" wire:model="bonusEarnPercent"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('bonusEarnPercent') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- Мин. трата и Цвет --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Мин. трата для вступления</label>
                                    <input type="number" step="0.01" min="0" wire:model="minSpentToJoin"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('minSpentToJoin') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Цвет</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" wire:model="color"
                                               class="h-10 w-14 rounded-lg border-gray-300 cursor-pointer">
                                        <input type="text" wire:model="color"
                                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono"
                                               maxlength="7">
                                    </div>
                                </div>
                            </div>

                            {{-- Активность --}}
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="isActive" id="groupActive"
                                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="groupActive" class="ml-2 text-sm text-gray-700">Группа активна</label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-end space-x-3">
                        <button type="button" wire:click="$set('showModal', false)"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            Отмена
                        </button>
                        <button type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                            {{ $editingId ? 'Сохранить' : 'Создать' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ===== Модалка подтверждения удаления ===== --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50" wire:click.self="$set('showDeleteModal', false)">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
                <div class="px-6 pt-6 pb-4">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Удаление группы</h3>
                    </div>
                    <p class="text-sm text-gray-600">
                        Вы уверены, что хотите удалить группу <strong>{{ $deletingName }}</strong>? Все клиенты будут убраны из этой группы.
                    </p>
                </div>
                <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-end space-x-3">
                    <button wire:click="$set('showDeleteModal', false)"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        Отмена
                    </button>
                    <button wire:click="delete"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                        Удалить
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
