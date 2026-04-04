<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Интернет-магазин</h1>
                <p class="text-sm text-gray-500 mt-1">Настройки сайта для клиентов</p>
            </div>
            @if($storeSettings)
                <a href="{{ $storeSettings->getStoreUrl() }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Открыть сайт
                </a>
            @endif
        </div>

        @if($saved)
            <div class="mb-4 p-3 bg-green-50 text-green-700 text-sm rounded-lg">Настройки сохранены!</div>
        @endif

        <form wire:submit="saveSettings" class="space-y-6">
            {{-- Основные настройки --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Основные</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Название магазина</label>
                        <input type="text" wire:model="storeName" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Мой ресторан">
                        @error('storeName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
                        <textarea wire:model="description" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Вкусная еда с доставкой"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL логотипа</label>
                            <input type="url" wire:model="logo" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL обложки</label>
                            <input type="url" wire:model="coverImage" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Основной цвет</label>
                            <div class="flex items-center gap-2">
                                <input type="color" wire:model="primaryColor" class="w-10 h-10 rounded cursor-pointer">
                                <input type="text" wire:model="primaryColor" class="flex-1 rounded-lg border-gray-300 shadow-sm text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Валюта</label>
                            <input type="text" wire:model="currency" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Напиток дня --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Напиток дня</h2>
                <select wire:model="drinkOfDayProductId" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">— Не выбран —</option>
                    @foreach($this->products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Заказы и доставка --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Заказы и доставка</h2>
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="deliveryEnabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                        <span class="text-sm font-medium text-gray-700">Доставка</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="pickupEnabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                        <span class="text-sm font-medium text-gray-700">Самовывоз</span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Минимальная сумма заказа</label>
                        <input type="number" wire:model="minOrderAmount" min="0" step="1000" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            {{-- Контакты --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Контакты</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
                        <input type="tel" wire:model="phone" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="+998 90 123 45 67">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
                            <input type="text" wire:model="instagram" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="@restaurant">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Telegram</label>
                            <input type="text" wire:model="telegram" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="@restaurant">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Часы работы</label>
                        <input type="text" wire:model="workingHoursText" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Пн-Вс: 10:00-23:00">
                    </div>
                </div>
            </div>

            {{-- Активность --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="isActive" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                    <span class="text-sm font-medium text-gray-700">Магазин активен</span>
                </div>
                @if($storeSettings)
                    <p class="text-sm text-gray-500 mt-2">Ссылка: <code class="bg-gray-100 px-2 py-0.5 rounded text-xs">{{ $storeSettings->getStoreUrl() }}</code></p>
                @endif
            </div>

            <button type="submit" class="w-full py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                Сохранить настройки
            </button>
        </form>

        {{-- Баннеры --}}
        <div class="mt-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">Баннеры</h2>
                <button wire:click="createBanner" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Добавить
                </button>
            </div>

            <div class="space-y-3">
                @forelse($this->banners as $banner)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center gap-4">
                        <img src="{{ $banner->image }}" alt="{{ $banner->title }}" class="w-24 h-14 rounded-lg object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 text-sm truncate">{{ $banner->title ?: 'Без названия' }}</p>
                            <span class="text-xs {{ $banner->is_active ? 'text-green-600' : 'text-gray-400' }}">
                                {{ $banner->is_active ? 'Активен' : 'Выключен' }}
                            </span>
                        </div>
                        <div class="flex gap-1">
                            <button wire:click="editBanner({{ $banner->id }})" class="p-2 text-gray-400 hover:text-blue-600 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button wire:click="deleteBanner({{ $banner->id }})" wire:confirm="Удалить баннер?" class="p-2 text-gray-400 hover:text-red-600 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-6">Нет баннеров</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Модальное окно баннера --}}
    @if($showBannerModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" wire:click="$set('showBannerModal', false)"></div>
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 z-10">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">{{ $editingBannerId ? 'Редактировать баннер' : 'Новый баннер' }}</h2>
                    <form wire:submit="saveBanner" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL изображения *</label>
                            <input type="url" wire:model="bannerImage" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" placeholder="https://...">
                            @error('bannerImage') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Заголовок</label>
                            <input type="text" wire:model="bannerTitle" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" placeholder="Акция дня!">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
                            <input type="text" wire:model="bannerDescription" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" placeholder="Скидка 20% на всё">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ссылка при клике</label>
                            <input type="url" wire:model="bannerLink" class="w-full rounded-lg border-gray-300 shadow-sm text-sm" placeholder="https://...">
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="bannerIsActive" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                            <span class="text-sm text-gray-700">Активен</span>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" wire:click="$set('showBannerModal', false)" class="flex-1 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition">Отмена</button>
                            <button type="submit" class="flex-1 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
