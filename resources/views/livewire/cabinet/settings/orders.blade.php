<div>
    {{-- Заголовок --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Настройки заказов</h1>
        <p class="text-sm text-gray-500 mt-1">Настройки обработки заказов</p>
    </div>

    {{-- Уведомление об успешном сохранении --}}
    @if (session('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-2xl">
        <form wire:submit="save">
            <div class="space-y-6">

                {{-- Автоприём заказов --}}
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Автоматически принимать заказы</label>
                        <p class="text-xs text-gray-400 mt-0.5">Новые заказы будут автоматически приняты без подтверждения</p>
                    </div>
                    <button type="button" wire:click="$toggle('autoAccept')"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $autoAccept ? 'bg-indigo-600' : 'bg-gray-200' }}"
                            role="switch" aria-checked="{{ $autoAccept ? 'true' : 'false' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $autoAccept ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>

                <hr class="border-gray-100">

                {{-- Формат номера заказа --}}
                <div>
                    <label for="numberFormat" class="block text-sm font-medium text-gray-700 mb-1">Формат номера заказа</label>
                    <input type="text" id="numberFormat" wire:model="numberFormat"
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           placeholder="Ymd-{sequence}">
                    <p class="mt-1 text-xs text-gray-400">Используйте {sequence} для порядкового номера. Пример: Ymd-{sequence} = 20260406-0001</p>
                    @error('numberFormat') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Тип заказа по умолчанию --}}
                <div>
                    <label for="defaultType" class="block text-sm font-medium text-gray-700 mb-1">Тип заказа по умолчанию</label>
                    <select id="defaultType" wire:model="defaultType"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="dine_in">В зале</option>
                        <option value="takeaway">Навынос</option>
                        <option value="delivery">Доставка</option>
                    </select>
                    @error('defaultType') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Источник заказа по умолчанию --}}
                <div>
                    <label for="defaultSource" class="block text-sm font-medium text-gray-700 mb-1">Источник заказа по умолчанию</label>
                    <select id="defaultSource" wire:model="defaultSource"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="pos">POS-терминал</option>
                        <option value="online">Онлайн</option>
                        <option value="phone">Телефон</option>
                    </select>
                    @error('defaultSource') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <hr class="border-gray-100">

                {{-- Требовать открытую кассовую смену --}}
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Требовать открытую кассовую смену</label>
                        <p class="text-xs text-gray-400 mt-0.5">Нельзя принимать заказы без открытой кассовой смены</p>
                    </div>
                    <button type="button" wire:click="$toggle('requireCashShift')"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $requireCashShift ? 'bg-indigo-600' : 'bg-gray-200' }}"
                            role="switch" aria-checked="{{ $requireCashShift ? 'true' : 'false' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $requireCashShift ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>
            </div>

            {{-- Кнопка сохранения --}}
            <div class="mt-6 flex justify-end">
                <button type="submit"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
                    <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Сохранить
                </button>
            </div>
        </form>
    </div>
</div>
