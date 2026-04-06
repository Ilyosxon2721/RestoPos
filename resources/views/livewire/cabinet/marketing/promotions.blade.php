<div>
    {{-- Заголовок --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Акции</h1>
            <p class="text-sm text-gray-500 mt-1">Управление акциями и спецпредложениями</p>
        </div>
        <button wire:click="create"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
            <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Создать акцию
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
                   placeholder="Поиск по названию акции...">
        </div>
    </div>

    {{-- Таблица акций --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Акция</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тип</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Значение</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Период</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дни</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($promotions as $promo)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $promo->name }}</div>
                                @if ($promo->promo_code)
                                    <span class="inline-flex items-center rounded bg-gray-100 px-1.5 py-0.5 text-xs font-mono text-gray-600 mt-1">{{ $promo->promo_code }}</span>
                                @endif
                                @if ($promo->description)
                                    <div class="text-xs text-gray-500 mt-0.5">{{ Str::limit($promo->description, 60) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800">
                                    {{ $typeLabels[$promo->type] ?? $promo->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                @if ($promo->discount_value)
                                    @if ($promo->discount_type === 'percent')
                                        {{ $promo->discount_value }}%
                                    @else
                                        {{ number_format((float) $promo->discount_value, 0, '.', ' ') }}
                                    @endif
                                @else
                                    <span class="text-gray-400">--</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                @if ($promo->start_date || $promo->end_date)
                                    <div>{{ $promo->start_date ? $promo->start_date->format('d.m.Y') : '...' }}</div>
                                    <div>{{ $promo->end_date ? $promo->end_date->format('d.m.Y') : '...' }}</div>
                                @else
                                    <span class="text-gray-400">Бессрочно</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($promo->active_days && count($promo->active_days) > 0 && count($promo->active_days) < 7)
                                    @php
                                        $dayNames = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
                                    @endphp
                                    <div class="flex gap-0.5">
                                        @foreach ($dayNames as $idx => $day)
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded text-xs {{ in_array($idx + 1, $promo->active_days) ? 'bg-indigo-100 text-indigo-700 font-medium' : 'bg-gray-50 text-gray-300' }}">
                                                {{ $day }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">Все дни</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($promo->is_active)
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Активна</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Неактивна</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                <button wire:click="toggleActive({{ $promo->id }})"
                                        class="text-sm font-medium {{ $promo->is_active ? 'text-amber-600 hover:text-amber-800' : 'text-green-600 hover:text-green-800' }}">
                                    {{ $promo->is_active ? 'Откл.' : 'Вкл.' }}
                                </button>
                                <button wire:click="edit({{ $promo->id }})"
                                        class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                    Изменить
                                </button>
                                <button wire:click="confirmDelete({{ $promo->id }})"
                                        class="text-red-600 hover:text-red-800 text-sm font-medium">
                                    Удалить
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                Акции не найдены
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($promotions->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $promotions->links() }}
            </div>
        @endif
    </div>

    {{-- ===== Модалка: Создание / Редактирование акции ===== --}}
    @if ($showModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                <form wire:submit="save">
                    <div class="px-6 pt-6 pb-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            {{ $editingId ? 'Редактировать акцию' : 'Новая акция' }}
                        </h3>

                        <div class="space-y-4">
                            {{-- Название --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Название <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="name"
                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                       placeholder="Название акции">
                                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Описание --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
                                <textarea wire:model="description" rows="2"
                                          class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                          placeholder="Описание акции"></textarea>
                                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Тип и Значение --}}
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Тип акции <span class="text-red-500">*</span></label>
                                    <select wire:model="type"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="discount">Скидка</option>
                                        <option value="bonus_multiply">Множитель бонусов</option>
                                        <option value="gift">Подарок</option>
                                        <option value="combo">Комбо</option>
                                        <option value="happy_hour">Счастливые часы</option>
                                        <option value="buy_x_get_y">Купи X -- получи Y</option>
                                    </select>
                                    @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Тип скидки</label>
                                    <select wire:model="discountType"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="percent">Процент (%)</option>
                                        <option value="fixed">Фиксированная сумма</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Значение</label>
                                    <input type="number" step="0.01" min="0" wire:model="discountValue"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                           placeholder="0">
                                    @error('discountValue') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <hr class="border-gray-200">

                            {{-- Период действия --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Дата начала</label>
                                    <input type="date" wire:model="startDate"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('startDate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Дата окончания</label>
                                    <input type="date" wire:model="endDate"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('endDate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- Дни недели --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Дни действия</label>
                                <div class="flex flex-wrap gap-2">
                                    @php
                                        $dayLabels = [1 => 'Понедельник', 2 => 'Вторник', 3 => 'Среда', 4 => 'Четверг', 5 => 'Пятница', 6 => 'Суббота', 7 => 'Воскресенье'];
                                    @endphp
                                    @foreach ($dayLabels as $dayNum => $dayLabel)
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" wire:model="activeDays" value="{{ $dayNum }}"
                                                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="ml-1.5 text-sm text-gray-700">{{ $dayLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Если не выбрано ни одного дня, акция действует каждый день</p>
                            </div>

                            {{-- Часы действия --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Время начала</label>
                                    <input type="time" wire:model="activeHoursFrom"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('activeHoursFrom') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Время окончания</label>
                                    <input type="time" wire:model="activeHoursTo"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('activeHoursTo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <hr class="border-gray-200">

                            {{-- Доп. параметры --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Мин. сумма заказа</label>
                                    <input type="number" step="0.01" min="0" wire:model="minOrderAmount"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                           placeholder="0">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Макс. скидка</label>
                                    <input type="number" step="0.01" min="0" wire:model="maxDiscountAmount"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                           placeholder="Без ограничения">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Промокод</label>
                                    <input type="text" wire:model="promoCode"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono uppercase"
                                           placeholder="PROMO2024" maxlength="50">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Лимит использований</label>
                                    <input type="number" min="0" wire:model="usageLimit"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                           placeholder="Без ограничения">
                                </div>
                            </div>

                            {{-- Активность --}}
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="isActive" id="promoActive"
                                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="promoActive" class="ml-2 text-sm text-gray-700">Акция активна</label>
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
                        <h3 class="text-lg font-semibold text-gray-900">Удаление акции</h3>
                    </div>
                    <p class="text-sm text-gray-600">
                        Вы уверены, что хотите удалить акцию <strong>{{ $deletingName }}</strong>?
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
