<div>
    {{-- Заголовок --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Столы и залы</h1>
            <p class="mt-1 text-sm text-gray-500">Управление залами и столами филиала</p>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="createHall"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Добавить зал
            </button>
            @if ($selectedHall)
                <button wire:click="createTable"
                        class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Добавить стол
                </button>
            @endif
        </div>
    </div>

    {{-- Выбор зала --}}
    @if ($halls->isNotEmpty())
        <div class="mb-6 flex flex-wrap items-center gap-2">
            @foreach($halls as $hall)
                <div class="flex items-center gap-1">
                    <button
                        wire:click="selectHall({{ $hall->id }})"
                        @class([
                            'rounded-full px-5 py-2 text-sm font-medium transition-colors',
                            'bg-emerald-600 text-white shadow-sm' => $selectedHall == $hall->id,
                            'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' => $selectedHall != $hall->id,
                        ])
                    >
                        {{ $hall->name }}
                        <span class="ml-1 text-xs opacity-70">({{ $hall->tables()->count() }})</span>
                    </button>
                    <button wire:click="editHall({{ $hall->id }})"
                            class="p-1 text-gray-400 hover:text-indigo-600 transition" title="Редактировать зал">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </button>
                    <button wire:click="deleteHall({{ $hall->id }})"
                            wire:confirm="Удалить зал «{{ $hall->name }}» и все его столы?"
                            class="p-1 text-gray-400 hover:text-red-600 transition" title="Удалить зал">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            @endforeach
        </div>
    @else
        <div class="mb-6 rounded-xl bg-white border border-gray-200 px-6 py-12 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"/>
            </svg>
            <p class="text-gray-500 mb-3">Нет залов. Создайте первый зал, чтобы начать добавлять столы.</p>
            <button wire:click="createHall"
                    class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors">
                Создать зал
            </button>
        </div>
    @endif

    {{-- Сетка столов --}}
    @if ($selectedHall)
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            @forelse($tables as $table)
                @php
                    $cardStyles = match($table->status) {
                        'free' => 'border-green-300 bg-green-50',
                        'occupied' => 'border-red-300 bg-red-50',
                        'reserved' => 'border-yellow-300 bg-yellow-50',
                        default => 'border-gray-300 bg-gray-50',
                    };
                    $dotColor = match($table->status) {
                        'free' => 'bg-green-500',
                        'occupied' => 'bg-red-500',
                        'reserved' => 'bg-yellow-500',
                        default => 'bg-gray-500',
                    };
                    $statusLabel = match($table->status) {
                        'free' => 'Свободен',
                        'occupied' => 'Занят',
                        'reserved' => 'Забронирован',
                        default => $table->status,
                    };
                    $textColor = match($table->status) {
                        'free' => 'text-green-700',
                        'occupied' => 'text-red-700',
                        'reserved' => 'text-yellow-700',
                        default => 'text-gray-700',
                    };
                @endphp
                <div class="rounded-xl border-2 {{ $cardStyles }} p-5 transition-shadow hover:shadow-md group relative">
                    {{-- Кнопки управления --}}
                    <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button wire:click="editTable({{ $table->id }})"
                                class="p-1.5 rounded-lg bg-white/80 text-gray-500 hover:text-indigo-600 shadow-sm transition" title="Редактировать">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </button>
                        <button wire:click="deleteTable({{ $table->id }})"
                                wire:confirm="Удалить стол «{{ $table->name }}»?"
                                class="p-1.5 rounded-lg bg-white/80 text-gray-500 hover:text-red-600 shadow-sm transition" title="Удалить">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>

                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-bold text-gray-900">Стол {{ $table->name }}</h3>
                        <span class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 rounded-full {{ $dotColor }}"></span>
                            <span class="text-xs font-medium {{ $textColor }}">{{ $statusLabel }}</span>
                        </span>
                    </div>
                    @if ($table->name)
                        <p class="text-sm text-gray-600 mb-1">{{ $table->name }}</p>
                    @endif
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ $table->capacity ?? 4 }} мест
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-xl bg-white border border-gray-200 px-6 py-12 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5z"/>
                    </svg>
                    <p class="text-gray-500 mb-3">В этом зале нет столов</p>
                    <button wire:click="createTable"
                            class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors">
                        Добавить стол
                    </button>
                </div>
            @endforelse
        </div>
    @endif

    {{-- ===== Модалка: Зал ===== --}}
    @if ($showHallModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50" wire:click.self="$set('showHallModal', false)">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
                <form wire:submit="saveHall">
                    <div class="px-6 pt-6 pb-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            {{ $editingHallId ? 'Редактировать зал' : 'Новый зал' }}
                        </h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Название зала <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="hallName"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                                   placeholder="Например: Основной зал, Терраса, VIP">
                            @error('hallName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-end space-x-3">
                        <button type="button" wire:click="$set('showHallModal', false)"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            Отмена
                        </button>
                        <button type="submit"
                                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors">
                            {{ $editingHallId ? 'Сохранить' : 'Создать' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ===== Модалка: Стол ===== --}}
    @if ($showTableModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50" wire:click.self="$set('showTableModal', false)">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
                <form wire:submit="saveTable">
                    <div class="px-6 pt-6 pb-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            {{ $editingTableId ? 'Редактировать стол' : 'Новый стол' }}
                        </h3>

                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Название / Номер <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="tableName"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                                           placeholder="1, 2, VIP-1...">
                                    @error('tableName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Мест <span class="text-red-500">*</span></label>
                                    <input type="number" wire:model="tableCapacity" min="1" max="50"
                                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                                    @error('tableCapacity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Форма стола</label>
                                <select wire:model="tableShape"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                                    <option value="square">Квадрат</option>
                                    <option value="round">Круглый</option>
                                    <option value="rectangle">Прямоугольник</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-end space-x-3">
                        <button type="button" wire:click="$set('showTableModal', false)"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            Отмена
                        </button>
                        <button type="submit"
                                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors">
                            {{ $editingTableId ? 'Сохранить' : 'Создать' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
