<div>
    {{-- Заголовок --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Тарифы</h1>
            <p class="text-sm text-gray-500 mt-1">Управление тарифными планами платформы</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button wire:click="create"
                    class="inline-flex items-center rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Добавить тариф
            </button>
        </div>
    </div>

    {{-- Таблица --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Цена</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Период</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Лимиты</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($plans as $plan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $plan->name }}</div>
                                <div class="text-xs text-gray-500">{{ $plan->slug }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-semibold text-gray-900">{{ number_format((float) $plan->price, 0, '.', ' ') }}</span>
                                <span class="text-xs text-gray-500">сум</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                @if ($plan->billing_period === 'monthly')
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">Ежемесячно</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">Ежегодно</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col space-y-1 text-xs text-gray-600">
                                    <span>Филиалы: <strong>{{ $plan->max_branches }}</strong></span>
                                    <span>Пользователи: <strong>{{ $plan->max_users }}</strong></span>
                                    <span>Товары: <strong>{{ $plan->max_products }}</strong></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($plan->is_active)
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Активен</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Неактивен</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <button wire:click="edit({{ $plan->id }})"
                                            class="inline-flex items-center rounded-md bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                                        <svg class="mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Изменить
                                    </button>
                                    <button wire:click="delete({{ $plan->id }})"
                                            wire:confirm="Вы уверены, что хотите удалить этот тариф?"
                                            class="inline-flex items-center rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 transition-colors">
                                        <svg class="mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Удалить
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                Тарифы не найдены
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Модальное окно создания/редактирования --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Затемнение фона --}}
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity" wire:click="$set('showModal', false)"></div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                {{-- Содержимое модального окна --}}
                <div class="relative inline-block transform overflow-hidden rounded-xl bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <form wire:submit="save">
                        <div class="bg-white px-6 pt-6 pb-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4" id="modal-title">
                                {{ $editingId ? 'Редактировать тариф' : 'Новый тариф' }}
                            </h3>

                            <div class="space-y-4">
                                {{-- Название --}}
                                <div>
                                    <label for="plan-name" class="block text-sm font-medium text-gray-700 mb-1">Название</label>
                                    <input wire:model="name"
                                           type="text"
                                           id="plan-name"
                                           placeholder="Стартовый"
                                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 focus:outline-none transition @error('name') border-red-500 @enderror">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Slug --}}
                                <div>
                                    <label for="plan-slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                                    <input wire:model="slug"
                                           type="text"
                                           id="plan-slug"
                                           placeholder="starter"
                                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 focus:outline-none transition @error('slug') border-red-500 @enderror">
                                    @error('slug')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Описание --}}
                                <div>
                                    <label for="plan-description" class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
                                    <textarea wire:model="description"
                                              id="plan-description"
                                              rows="2"
                                              placeholder="Описание тарифа..."
                                              class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 focus:outline-none transition @error('description') border-red-500 @enderror"></textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Цена и Период --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="plan-price" class="block text-sm font-medium text-gray-700 mb-1">Цена (сум)</label>
                                        <input wire:model="price"
                                               type="number"
                                               id="plan-price"
                                               step="0.01"
                                               min="0"
                                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 focus:outline-none transition @error('price') border-red-500 @enderror">
                                        @error('price')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="plan-billing" class="block text-sm font-medium text-gray-700 mb-1">Период</label>
                                        <select wire:model="billing_period"
                                                id="plan-billing"
                                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 focus:outline-none transition">
                                            <option value="monthly">Ежемесячно</option>
                                            <option value="yearly">Ежегодно</option>
                                        </select>
                                        @error('billing_period')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Лимиты --}}
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label for="plan-branches" class="block text-sm font-medium text-gray-700 mb-1">Филиалы</label>
                                        <input wire:model="max_branches"
                                               type="number"
                                               id="plan-branches"
                                               min="1"
                                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 focus:outline-none transition @error('max_branches') border-red-500 @enderror">
                                        @error('max_branches')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="plan-users" class="block text-sm font-medium text-gray-700 mb-1">Юзеры</label>
                                        <input wire:model="max_users"
                                               type="number"
                                               id="plan-users"
                                               min="1"
                                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 focus:outline-none transition @error('max_users') border-red-500 @enderror">
                                        @error('max_users')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="plan-products" class="block text-sm font-medium text-gray-700 mb-1">Товары</label>
                                        <input wire:model="max_products"
                                               type="number"
                                               id="plan-products"
                                               min="1"
                                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 focus:outline-none transition @error('max_products') border-red-500 @enderror">
                                        @error('max_products')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Порядок сортировки и Активность --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="plan-sort" class="block text-sm font-medium text-gray-700 mb-1">Порядок сортировки</label>
                                        <input wire:model="sort_order"
                                               type="number"
                                               id="plan-sort"
                                               min="0"
                                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 focus:outline-none transition @error('sort_order') border-red-500 @enderror">
                                        @error('sort_order')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="flex items-end pb-1">
                                        <label class="flex items-center cursor-pointer">
                                            <input wire:model="is_active"
                                                   type="checkbox"
                                                   class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                            <span class="ml-2 text-sm text-gray-700">Активен</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                            <button type="button"
                                    wire:click="$set('showModal', false)"
                                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                                Отмена
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 disabled:opacity-50 transition-colors">
                                <span wire:loading.remove wire:target="save">{{ $editingId ? 'Сохранить' : 'Создать' }}</span>
                                <span wire:loading wire:target="save" class="inline-flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    Сохранение...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
