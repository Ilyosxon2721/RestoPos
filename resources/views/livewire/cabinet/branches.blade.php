<div>
    {{-- Заголовок --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Филиалы</h1>
            <p class="text-sm text-gray-500 mt-1">Управление филиалами организации</p>
        </div>
        <button wire:click="create"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
            <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Добавить филиал
        </button>
    </div>

    {{-- Таблица филиалов --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Адрес</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Телефон</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($branches as $branch)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $branch->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $branch->address ?? '---' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $branch->phone ?? '---' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <button wire:click="edit({{ $branch->id }})"
                                        class="text-indigo-600 hover:text-indigo-800 text-sm font-medium mr-3">
                                    Редактировать
                                </button>
                                <button wire:click="delete({{ $branch->id }})"
                                        wire:confirm="Вы уверены, что хотите удалить этот филиал?"
                                        class="text-red-600 hover:text-red-800 text-sm font-medium">
                                    Удалить
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">Нет филиалов. Добавьте первый филиал.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Модальное окно создания/редактирования --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Затемнение --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showModal', false)"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Модальное окно --}}
                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit="save">
                        <div class="bg-white px-6 pt-6 pb-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4" id="modal-title">
                                {{ $editingId ? 'Редактировать филиал' : 'Новый филиал' }}
                            </h3>

                            <div class="space-y-4">
                                {{-- Название --}}
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Название <span class="text-red-500">*</span></label>
                                    <input type="text" id="name" wire:model="name"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                           placeholder="Название филиала">
                                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                {{-- Адрес --}}
                                <div>
                                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Адрес</label>
                                    <input type="text" id="address" wire:model="address"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                           placeholder="Адрес филиала">
                                    @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                {{-- Телефон --}}
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
                                    <input type="text" id="phone" wire:model="phone"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                           placeholder="+998 XX XXX XX XX">
                                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                            <button type="button" wire:click="$set('showModal', false)"
                                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                                Отмена
                            </button>
                            <button type="submit"
                                    class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
                                {{ $editingId ? 'Сохранить' : 'Создать' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
