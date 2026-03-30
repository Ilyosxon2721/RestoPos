<div>
    {{-- Заголовок --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Поставщики</h1>
            <p class="text-sm text-gray-500 mt-1">Управление поставщиками ингредиентов и товаров</p>
        </div>
        <button wire:click="create"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
            <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Добавить поставщика
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
                   placeholder="Поиск по названию, контакту или телефону...">
        </div>
    </div>

    {{-- Таблица --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Поставщик</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Контакт</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Телефон</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ИНН</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Отсрочка</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($suppliers as $supplier)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $supplier->name }}</div>
                                @if ($supplier->legal_name)
                                    <div class="text-xs text-gray-400">{{ $supplier->legal_name }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $supplier->contact_person ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $supplier->phone ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500 font-mono text-xs">{{ $supplier->inn ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                {{ $supplier->payment_terms > 0 ? $supplier->payment_terms . ' дн.' : '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if ($supplier->is_active)
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Активен</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Неактивен</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                <button wire:click="edit({{ $supplier->id }})" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Изменить</button>
                                <button wire:click="toggleActive({{ $supplier->id }})"
                                        class="text-sm font-medium {{ $supplier->is_active ? 'text-amber-600 hover:text-amber-800' : 'text-green-600 hover:text-green-800' }}">
                                    {{ $supplier->is_active ? 'Откл.' : 'Вкл.' }}
                                </button>
                                <button wire:click="deleteSupplier({{ $supplier->id }})"
                                        wire:confirm="Удалить поставщика {{ $supplier->name }}?"
                                        class="text-red-600 hover:text-red-800 text-sm font-medium">Удалить</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">Поставщики не найдены</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($suppliers->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">{{ $suppliers->links() }}</div>
        @endif
    </div>

    {{-- ===== Модалка ===== --}}
    @if ($showModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                <form wire:submit="save">
                    <div class="px-6 pt-6 pb-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            {{ $editingId ? 'Редактировать поставщика' : 'Новый поставщик' }}
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Название <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="name" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="ООО «Поставщик»">
                                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Юр. название</label>
                                    <input type="text" wire:model="legalName" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ИНН</label>
                                    <input type="text" wire:model="inn" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="123456789">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Контактное лицо</label>
                                <input type="text" wire:model="contactPerson" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
                                    <input type="text" wire:model="phone" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="+998 XX XXX XX XX">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                    <input type="email" wire:model="email" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Адрес</label>
                                <textarea wire:model="address" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Отсрочка платежа (дни)</label>
                                    <input type="number" min="0" wire:model="paymentTerms" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="flex items-center space-x-3 mt-7">
                                        <input type="checkbox" wire:model="isActive" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-gray-700">Активен</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Заметки</label>
                                <textarea wire:model="notes" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-end space-x-3">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">Отмена</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">{{ $editingId ? 'Сохранить' : 'Создать' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
