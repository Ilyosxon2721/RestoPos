<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Настройки</h1>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        {{-- Организация --}}
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Организация</h2>
                <p class="text-sm text-gray-500 mt-1">Основные данные о вашей организации</p>
            </div>
            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="organizationName" class="block text-sm font-medium text-gray-700 mb-1">
                        Название организации
                    </label>
                    <input type="text" id="organizationName" wire:model="organizationName"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="ООО Ресторан" />
                    @error('organizationName')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                        Телефон
                    </label>
                    <input type="text" id="phone" wire:model="phone"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="+7 (999) 123-45-67" />
                    @error('phone')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Филиал --}}
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Филиал</h2>
                <p class="text-sm text-gray-500 mt-1">Данные текущего филиала</p>
            </div>
            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="branchName" class="block text-sm font-medium text-gray-700 mb-1">
                        Название филиала
                    </label>
                    <input type="text" id="branchName" wire:model="branchName"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Основной зал" />
                    @error('branchName')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                        Адрес
                    </label>
                    <input type="text" id="address" wire:model="address"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="г. Москва, ул. Примерная, д. 1" />
                    @error('address')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Системные настройки --}}
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Система</h2>
                <p class="text-sm text-gray-500 mt-1">Валюта, часовой пояс и налоги</p>
            </div>
            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">
                        Валюта
                    </label>
                    <select id="currency" wire:model="currency"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="RUB">Российский рубль (RUB)</option>
                        <option value="USD">Доллар США (USD)</option>
                        <option value="EUR">Евро (EUR)</option>
                        <option value="KZT">Казахстанский тенге (KZT)</option>
                        <option value="UAH">Украинская гривна (UAH)</option>
                        <option value="BYN">Белорусский рубль (BYN)</option>
                        <option value="UZS">Узбекский сум (UZS)</option>
                    </select>
                    @error('currency')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="timezone" class="block text-sm font-medium text-gray-700 mb-1">
                        Часовой пояс
                    </label>
                    <select id="timezone" wire:model="timezone"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="Europe/Moscow">Москва (UTC+3)</option>
                        <option value="Europe/Kaliningrad">Калининград (UTC+2)</option>
                        <option value="Europe/Samara">Самара (UTC+4)</option>
                        <option value="Asia/Yekaterinburg">Екатеринбург (UTC+5)</option>
                        <option value="Asia/Omsk">Омск (UTC+6)</option>
                        <option value="Asia/Krasnoyarsk">Красноярск (UTC+7)</option>
                        <option value="Asia/Irkutsk">Иркутск (UTC+8)</option>
                        <option value="Asia/Yakutsk">Якутск (UTC+9)</option>
                        <option value="Asia/Vladivostok">Владивосток (UTC+10)</option>
                        <option value="Asia/Kamchatka">Камчатка (UTC+12)</option>
                        <option value="Asia/Almaty">Алматы (UTC+6)</option>
                        <option value="Asia/Tashkent">Ташкент (UTC+5)</option>
                        <option value="Europe/Minsk">Минск (UTC+3)</option>
                        <option value="Europe/Kyiv">Киев (UTC+2)</option>
                    </select>
                    @error('timezone')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="taxRate" class="block text-sm font-medium text-gray-700 mb-1">
                        Ставка налога (%)
                    </label>
                    <input type="number" id="taxRate" wire:model="taxRate" step="0.01" min="0" max="100"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="20" />
                    @error('taxRate')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="serviceChargePercent" class="block text-sm font-medium text-gray-700 mb-1">
                        Сервисный сбор (%)
                    </label>
                    <input type="number" id="serviceChargePercent" wire:model="serviceChargePercent" step="0.01" min="0" max="100"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="10" />
                    @error('serviceChargePercent')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Кнопка сохранения --}}
        <div class="flex justify-end">
            <button type="submit"
                class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                <span wire:loading.remove wire:target="save">Сохранить настройки</span>
                <span wire:loading wire:target="save">Сохранение...</span>
            </button>
        </div>
    </form>
</div>
