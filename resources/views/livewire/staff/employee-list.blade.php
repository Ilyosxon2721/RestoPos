<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Заголовок --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Персонал</h1>
            <button
                wire:click="openCreateModal"
                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Добавить
            </button>
        </div>

        {{-- Уведомления --}}
        @if (session()->has('success'))
            <div class="mb-4 rounded-md bg-green-50 p-4">
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Поиск --}}
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <input
                type="text"
                wire:model.live.debounce.300ms="searchQuery"
                placeholder="Поиск по имени, должности или телефону..."
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
        </div>

        {{-- Таблица --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Имя</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Должность</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Роль</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Телефон</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата найма</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Зарплата</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($employees as $employee)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $employee->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $employee->position ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $employee->role ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $employee->phone ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $employee->hire_date ? $employee->hire_date->format('d.m.Y') : '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($employee->salary ?? 0, 2, '.', ' ') }} ₽
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($employee->status === 'active')
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-800">
                                            Активен
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800">
                                            Неактивен
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <button
                                        wire:click="openEditModal({{ $employee->id }})"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        Редактировать
                                    </button>
                                    <button
                                        wire:click="deleteEmployee({{ $employee->id }})"
                                        wire:confirm="Вы уверены, что хотите удалить сотрудника?"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Удалить
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    Сотрудники не найдены
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Пагинация --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $employees->links() }}
            </div>
        </div>
    </div>

    {{-- Модальное окно создания/редактирования --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit="save">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                {{ $editingId ? 'Редактировать сотрудника' : 'Новый сотрудник' }}
                            </h3>

                            <div class="space-y-4">
                                {{-- Имя --}}
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Имя *</label>
                                    <input
                                        type="text"
                                        id="name"
                                        wire:model="name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Должность --}}
                                <div>
                                    <label for="position" class="block text-sm font-medium text-gray-700">Должность *</label>
                                    <input
                                        type="text"
                                        id="position"
                                        wire:model="position"
                                        placeholder="Официант, Повар, Бармен..."
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                    @error('position') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Роль --}}
                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700">Роль *</label>
                                    <select
                                        id="role"
                                        wire:model="role"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                        <option value="">Выберите роль</option>
                                        <option value="admin">Администратор</option>
                                        <option value="manager">Менеджер</option>
                                        <option value="waiter">Официант</option>
                                        <option value="chef">Повар</option>
                                        <option value="bartender">Бармен</option>
                                        <option value="cashier">Кассир</option>
                                    </select>
                                    @error('role') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Телефон --}}
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">Телефон</label>
                                    <input
                                        type="text"
                                        id="phone"
                                        wire:model="phone"
                                        placeholder="+7 (999) 123-45-67"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                    @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Дата найма --}}
                                <div>
                                    <label for="hireDate" class="block text-sm font-medium text-gray-700">Дата найма</label>
                                    <input
                                        type="date"
                                        id="hireDate"
                                        wire:model="hireDate"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                    @error('hireDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Зарплата --}}
                                <div>
                                    <label for="salary" class="block text-sm font-medium text-gray-700">Зарплата</label>
                                    <input
                                        type="number"
                                        id="salary"
                                        wire:model="salary"
                                        step="100"
                                        min="0"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                    @error('salary') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Статус --}}
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">Статус *</label>
                                    <select
                                        id="status"
                                        wire:model="status"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                        <option value="active">Активен</option>
                                        <option value="inactive">Неактивен</option>
                                    </select>
                                    @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button
                                type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                {{ $editingId ? 'Сохранить' : 'Добавить' }}
                            </button>
                            <button
                                type="button"
                                wire:click="closeModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Отмена
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
