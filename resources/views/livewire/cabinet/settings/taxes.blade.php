<div>
    {{-- Заголовок --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Налоги</h1>
        <p class="text-sm text-gray-500 mt-1">Настройки налогов и сервисного сбора</p>
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

    <div class="space-y-6 max-w-2xl">

        {{-- Секция: Налог --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Налог (НДС)</h2>
            <form wire:submit="save">
                <div class="space-y-6">

                    {{-- Включить налог --}}
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Включить налог</label>
                            <p class="text-xs text-gray-400 mt-0.5">Применять налог к заказам</p>
                        </div>
                        <button type="button" wire:click="$toggle('taxEnabled')"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $taxEnabled ? 'bg-indigo-600' : 'bg-gray-200' }}"
                                role="switch" aria-checked="{{ $taxEnabled ? 'true' : 'false' }}">
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $taxEnabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>

                    {{-- Ставка налога --}}
                    <div>
                        <label for="taxRate" class="block text-sm font-medium text-gray-700 mb-1">Ставка налога</label>
                        <div class="relative">
                            <input type="number" id="taxRate" wire:model="taxRate" step="0.1" min="0" max="100"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pr-10">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-400 text-sm">%</span>
                            </div>
                        </div>
                        @error('taxRate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Налог включён в цену --}}
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Налог включён в цену</label>
                            <p class="text-xs text-gray-400 mt-0.5">Цены в меню уже включают налог</p>
                        </div>
                        <button type="button" wire:click="$toggle('taxIncludedInPrice')"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $taxIncludedInPrice ? 'bg-indigo-600' : 'bg-gray-200' }}"
                                role="switch" aria-checked="{{ $taxIncludedInPrice ? 'true' : 'false' }}">
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $taxIncludedInPrice ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>
                </div>

                <hr class="my-6 border-gray-100">

                {{-- Секция: Сервисный сбор --}}
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Сервисный сбор</h2>
                <div class="space-y-6">

                    {{-- Включить сервисный сбор --}}
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Включить сервисный сбор</label>
                            <p class="text-xs text-gray-400 mt-0.5">Добавлять сервисный сбор к заказам</p>
                        </div>
                        <button type="button" wire:click="$toggle('serviceChargeEnabled')"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $serviceChargeEnabled ? 'bg-indigo-600' : 'bg-gray-200' }}"
                                role="switch" aria-checked="{{ $serviceChargeEnabled ? 'true' : 'false' }}">
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $serviceChargeEnabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>

                    {{-- Процент сервисного сбора --}}
                    <div>
                        <label for="serviceChargePercent" class="block text-sm font-medium text-gray-700 mb-1">Процент сервисного сбора</label>
                        <div class="relative">
                            <input type="number" id="serviceChargePercent" wire:model="serviceChargePercent" step="0.5" min="0" max="100"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pr-10">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-400 text-sm">%</span>
                            </div>
                        </div>
                        @error('serviceChargePercent') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
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
</div>
