<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Заголовок --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">QR-меню</h1>
                <p class="text-sm text-gray-500 mt-1">Электронное меню для гостей по QR-коду</p>
            </div>

            <button
                wire:click="create"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Создать QR-меню
            </button>
        </div>

        {{-- Список QR-меню --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($this->qrMenus as $menu)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    {{-- Превью цветов --}}
                    <div class="h-2" style="background-color: {{ $menu->primary_color }}"></div>

                    <div class="p-5">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $menu->name }}</h3>
                                @if($menu->branch)
                                    <p class="text-sm text-gray-500">{{ $menu->branch->name }}</p>
                                @else
                                    <p class="text-sm text-gray-500">Все филиалы</p>
                                @endif
                            </div>
                            <button
                                wire:click="toggleActive({{ $menu->id }})"
                                title="Нажмите для переключения"
                            >
                                @if($menu->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 cursor-pointer hover:bg-green-200 transition-colors">
                                        Активно
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 cursor-pointer hover:bg-gray-200 transition-colors">
                                        Выключено
                                    </span>
                                @endif
                            </button>
                        </div>

                        @if($menu->description)
                            <p class="text-sm text-gray-600 mb-3">{{ $menu->description }}</p>
                        @endif

                        {{-- QR код --}}
                        <div class="bg-gray-50 rounded-lg p-4 mb-4 text-center">
                            <img
                                src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($menu->getPublicUrl()) }}"
                                alt="QR код"
                                class="w-40 h-40 mx-auto mb-2"
                                loading="lazy"
                            >
                            <p class="text-xs text-gray-500 break-all">{{ $menu->getPublicUrl() }}</p>
                        </div>

                        {{-- Настройки --}}
                        <div class="flex flex-wrap gap-2 mb-4">
                            @if($menu->show_images)
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-50 text-blue-700">Фото</span>
                            @endif
                            @if($menu->show_descriptions)
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-50 text-blue-700">Описания</span>
                            @endif
                            @if($menu->show_calories)
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-50 text-blue-700">КБЖУ</span>
                            @endif
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-gray-100 text-gray-600">{{ $menu->currency }}</span>
                        </div>

                        {{-- Действия --}}
                        <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                            <a
                                href="{{ $menu->getPublicUrl() }}"
                                target="_blank"
                                class="flex-1 inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
                            >
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Открыть
                            </a>
                            <button
                                wire:click="edit({{ $menu->id }})"
                                class="flex-1 inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                            >
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Изменить
                            </button>
                            <button
                                wire:click="delete({{ $menu->id }})"
                                wire:confirm="Вы уверены, что хотите удалить это QR-меню?"
                                class="p-2 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors"
                                title="Удалить"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">Нет QR-меню</h3>
                    <p class="text-sm text-gray-500 mb-4">Создайте электронное меню и поделитесь QR-кодом с гостями</p>
                    <button
                        wire:click="create"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        Создать первое QR-меню
                    </button>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Модальное окно создания/редактирования --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50 transition-opacity" wire:click="$set('showModal', false)"></div>

                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 z-10 max-h-[90vh] overflow-y-auto">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">
                        {{ $editingId ? 'Редактировать QR-меню' : 'Новое QR-меню' }}
                    </h2>

                    <form wire:submit="save" class="space-y-4">
                        {{-- Название --}}
                        <div>
                            <label for="menuName" class="block text-sm font-medium text-gray-700 mb-1">Название меню</label>
                            <input
                                type="text"
                                id="menuName"
                                wire:model="name"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Основное меню"
                            >
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Филиал --}}
                        <div>
                            <label for="menuBranch" class="block text-sm font-medium text-gray-700 mb-1">Филиал</label>
                            <select
                                id="menuBranch"
                                wire:model="branchId"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Все филиалы</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Описание --}}
                        <div>
                            <label for="menuDescription" class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
                            <textarea
                                id="menuDescription"
                                wire:model="description"
                                rows="2"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Краткое описание заведения..."
                            ></textarea>
                        </div>

                        {{-- Логотип --}}
                        <div>
                            <label for="menuLogo" class="block text-sm font-medium text-gray-700 mb-1">URL логотипа</label>
                            <input
                                type="url"
                                id="menuLogo"
                                wire:model="logo"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="https://example.com/logo.png"
                            >
                            @error('logo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Цвета --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="menuBgColor" class="block text-sm font-medium text-gray-700 mb-1">Цвет фона</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        id="menuBgColor"
                                        wire:model="backgroundColor"
                                        class="w-10 h-10 rounded border-gray-300 cursor-pointer"
                                    >
                                    <input
                                        type="text"
                                        wire:model="backgroundColor"
                                        class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        placeholder="#ffffff"
                                    >
                                </div>
                            </div>
                            <div>
                                <label for="menuPrimaryColor" class="block text-sm font-medium text-gray-700 mb-1">Основной цвет</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        id="menuPrimaryColor"
                                        wire:model="primaryColor"
                                        class="w-10 h-10 rounded border-gray-300 cursor-pointer"
                                    >
                                    <input
                                        type="text"
                                        wire:model="primaryColor"
                                        class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        placeholder="#4f46e5"
                                    >
                                </div>
                            </div>
                        </div>

                        {{-- Валюта --}}
                        <div>
                            <label for="menuCurrency" class="block text-sm font-medium text-gray-700 mb-1">Валюта</label>
                            <input
                                type="text"
                                id="menuCurrency"
                                wire:model="currency"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="сум"
                            >
                        </div>

                        {{-- Переключатели --}}
                        <div class="space-y-3 pt-2">
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="showImages" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                                <span class="text-sm font-medium text-gray-700">Показывать фото блюд</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="showDescriptions" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                                <span class="text-sm font-medium text-gray-700">Показывать описания блюд</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="showCalories" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                                <span class="text-sm font-medium text-gray-700">Показывать КБЖУ</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="isActive" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                                <span class="text-sm font-medium text-gray-700">Активно</span>
                            </div>
                        </div>

                        {{-- Кнопки --}}
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                            <button
                                type="button"
                                wire:click="$set('showModal', false)"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                            >
                                Отмена
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                            >
                                {{ $editingId ? 'Сохранить' : 'Создать' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
