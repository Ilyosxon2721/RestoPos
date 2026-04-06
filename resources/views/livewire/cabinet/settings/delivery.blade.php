<div>
    {{-- Заголовок --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Доставка</h1>
        <p class="text-sm text-gray-500 mt-1">Настройки доставки</p>
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

                {{-- Включить доставку --}}
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Включить доставку</label>
                        <p class="text-xs text-gray-400 mt-0.5">Разрешить приём заказов на доставку</p>
                    </div>
                    <button type="button" wire:click="$toggle('enabled')"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $enabled ? 'bg-indigo-600' : 'bg-gray-200' }}"
                            role="switch" aria-checked="{{ $enabled ? 'true' : 'false' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $enabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>

                <hr class="border-gray-100">

                {{-- Минимальная сумма заказа --}}
                <div>
                    <label for="minimumOrderAmount" class="block text-sm font-medium text-gray-700 mb-1">Минимальная сумма заказа для доставки</label>
                    <div class="relative">
                        <input type="number" id="minimumOrderAmount" wire:model="minimumOrderAmount" step="1000" min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pr-16">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-400 text-sm">{{ config('forris.currency.symbol', 'сум') }}</span>
                        </div>
                    </div>
                    @error('minimumOrderAmount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Время доставки по умолчанию --}}
                <div>
                    <label for="defaultDeliveryTime" class="block text-sm font-medium text-gray-700 mb-1">Время доставки по умолчанию</label>
                    <div class="relative">
                        <input type="number" id="defaultDeliveryTime" wire:model="defaultDeliveryTime" min="1" max="1440"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pr-16">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-400 text-sm">мин.</span>
                        </div>
                    </div>
                    @error('defaultDeliveryTime') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Стоимость доставки --}}
                <div>
                    <label for="deliveryFee" class="block text-sm font-medium text-gray-700 mb-1">Стоимость доставки</label>
                    <div class="relative">
                        <input type="number" id="deliveryFee" wire:model="deliveryFee" step="1000" min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pr-16">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-400 text-sm">{{ config('forris.currency.symbol', 'сум') }}</span>
                        </div>
                    </div>
                    @error('deliveryFee') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Порог бесплатной доставки --}}
                <div>
                    <label for="freeDeliveryThreshold" class="block text-sm font-medium text-gray-700 mb-1">Бесплатная доставка от суммы</label>
                    <div class="relative">
                        <input type="number" id="freeDeliveryThreshold" wire:model="freeDeliveryThreshold" step="1000" min="0"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pr-16">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-400 text-sm">{{ config('forris.currency.symbol', 'сум') }}</span>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Укажите 0, если бесплатная доставка не предусмотрена</p>
                    @error('freeDeliveryThreshold') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
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
