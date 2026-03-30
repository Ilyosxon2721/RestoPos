<div>
    {{-- Заголовок --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Склады</h1>
            <p class="text-sm text-gray-500 mt-1">Управление складскими помещениями по филиалам</p>
        </div>
        <button wire:click="create"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
            <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Добавить склад
        </button>
    </div>

    {{-- Карточки складов --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($warehouses as $warehouse)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center
                            {{ match($warehouse->type) {
                                'main' => 'bg-blue-100 text-blue-600',
                                'kitchen' => 'bg-orange-100 text-orange-600',
                                'bar' => 'bg-purple-100 text-purple-600',
                                'freezer' => 'bg-cyan-100 text-cyan-600',
                                default => 'bg-gray-100 text-gray-600',
                            } }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $warehouse->name }}</h3>
                            <p class="text-xs text-gray-400">{{ $warehouse->branch?->name }}</p>
                        </div>
                    </div>
                    @if ($warehouse->is_default)
                        <span class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-800">По умолч.</span>
                    @endif
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        @php
                            $typeLabel = match($warehouse->type) {
                                'main' => 'Основной',
                                'kitchen' => 'Кухня',
                                'bar' => 'Бар',
                                'freezer' => 'Морозилка',
                                default => $warehouse->type,
                            };
                        @endphp
                        <span class="text-xs text-gray-500">{{ $typeLabel }}</span>
                        @if ($warehouse->is_active)
                            <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Активен</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">Неактивен</span>
                        @endif
                    </div>
                    <div class="flex items-center space-x-2">
                        <button wire:click="edit({{ $warehouse->id }})" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Изменить</button>
                        <button wire:click="toggleActive({{ $warehouse->id }})"
                                class="text-sm font-medium {{ $warehouse->is_active ? 'text-amber-600 hover:text-amber-800' : 'text-green-600 hover:text-green-800' }}">
                            {{ $warehouse->is_active ? 'Откл.' : 'Вкл.' }}
                        </button>
                        <button wire:click="deleteWarehouse({{ $warehouse->id }})"
                                wire:confirm="Удалить склад {{ $warehouse->name }}?"
                                class="text-red-600 hover:text-red-800 text-sm font-medium">Удалить</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <p class="text-gray-500">Склады не найдены</p>
                <p class="text-sm text-gray-400 mt-1">Добавьте склад для начала учёта</p>
            </div>
        @endforelse
    </div>

    {{-- ===== Модалка ===== --}}
    @if ($showModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
                <form wire:submit="save">
                    <div class="px-6 pt-6 pb-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            {{ $editingId ? 'Редактировать склад' : 'Новый склад' }}
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Название <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="name" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Основной склад">
                                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Филиал <span class="text-red-500">*</span></label>
                                <select wire:model="branchId" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Выберите филиал...</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @error('branchId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Тип</label>
                                <select wire:model="type" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="main">Основной</option>
                                    <option value="kitchen">Кухня</option>
                                    <option value="bar">Бар</option>
                                    <option value="freezer">Морозилка</option>
                                </select>
                            </div>
                            <div class="flex items-center space-x-6">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model="isDefault" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm text-gray-700">По умолчанию</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" wire:model="isActive" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm text-gray-700">Активен</span>
                                </label>
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
