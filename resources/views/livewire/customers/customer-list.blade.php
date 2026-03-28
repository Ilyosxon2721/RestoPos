<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Заголовок --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Клиенты</h1>
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
                placeholder="Поиск по имени, телефону или email..."
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Телефон</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Бонусы</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Заказов</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма покупок</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Скидка</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($customers as $customer)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $customer->first_name }} {{ $customer->last_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $customer->phone ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $customer->email ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($customer->bonus_balance ?? 0, 2, '.', ' ') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $customer->orders_count ?? $customer->orders()->count() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($customer->total_spent ?? 0, 2, '.', ' ') }} ₽
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $customer->discount_percent ?? 0 }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <button
                                        wire:click="openEditModal({{ $customer->id }})"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        Редактировать
                                    </button>
                                    <button
                                        wire:click="deleteCustomer({{ $customer->id }})"
                                        wire:confirm="Вы уверены, что хотите удалить клиента?"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Удалить
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    Клиенты не найдены
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Пагинация --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $customers->links() }}
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
                                {{ $editingId ? 'Редактировать клиента' : 'Новый клиент' }}
                            </h3>

                            <div class="space-y-4">
                                {{-- Имя --}}
                                <div>
                                    <label for="firstName" class="block text-sm font-medium text-gray-700">Имя *</label>
                                    <input
                                        type="text"
                                        id="firstName"
                                        wire:model="firstName"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                    @error('firstName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Фамилия --}}
                                <div>
                                    <label for="lastName" class="block text-sm font-medium text-gray-700">Фамилия</label>
                                    <input
                                        type="text"
                                        id="lastName"
                                        wire:model="lastName"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                    @error('lastName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Телефон --}}
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">Телефон *</label>
                                    <input
                                        type="text"
                                        id="phone"
                                        wire:model="phone"
                                        placeholder="+7 (999) 123-45-67"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                    @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <input
                                        type="email"
                                        id="email"
                                        wire:model="email"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Бонусный баланс --}}
                                <div>
                                    <label for="bonusBalance" class="block text-sm font-medium text-gray-700">Бонусный баланс</label>
                                    <input
                                        type="number"
                                        id="bonusBalance"
                                        wire:model="bonusBalance"
                                        step="0.01"
                                        min="0"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                    @error('bonusBalance') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                {{-- Скидка --}}
                                <div>
                                    <label for="discountPercent" class="block text-sm font-medium text-gray-700">Скидка (%)</label>
                                    <input
                                        type="number"
                                        id="discountPercent"
                                        wire:model="discountPercent"
                                        step="0.1"
                                        min="0"
                                        max="100"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                    @error('discountPercent') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
