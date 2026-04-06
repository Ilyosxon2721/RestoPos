<div>
    {{-- Заголовок --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Программа лояльности</h1>
        <p class="text-sm text-gray-500 mt-1">Настройка бонусной программы для клиентов</p>
    </div>

    {{-- Статистика --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Всего начислено</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format((float) $totalIssued, 0, '.', ' ') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Всего списано</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format((float) $totalSpent, 0, '.', ' ') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Текущий баланс</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format((float) $totalBalance, 0, '.', ' ') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Клиентов с бонусами</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $customersWithBonuses }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Настройки --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <form wire:submit="save">
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Настройки программы</h3>
                <p class="text-sm text-gray-500 mt-1">Параметры начисления и списания бонусов</p>
            </div>

            <div class="px-6 py-5 space-y-6">
                {{-- Включение/выключение --}}
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Программа лояльности</p>
                        <p class="text-sm text-gray-500">Включить бонусную программу для клиентов</p>
                    </div>
                    <button type="button"
                            wire:click="$toggle('loyaltyEnabled')"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 {{ $loyaltyEnabled ? 'bg-indigo-600' : 'bg-gray-200' }}"
                            role="switch"
                            aria-checked="{{ $loyaltyEnabled ? 'true' : 'false' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $loyaltyEnabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>

                <hr class="border-gray-200">

                {{-- Процент начисления --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Процент начисления
                            <span class="text-gray-400 font-normal">(% от суммы заказа)</span>
                        </label>
                        <div class="relative">
                            <input type="number" step="0.1" min="0" max="100" wire:model="earnRate"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pr-8">
                            <span class="absolute inset-y-0 right-3 flex items-center text-gray-400">%</span>
                        </div>
                        @error('earnRate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500">Сколько бонусов клиент получает с каждого заказа</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Макс. оплата бонусами
                            <span class="text-gray-400 font-normal">(% от заказа)</span>
                        </label>
                        <div class="relative">
                            <input type="number" step="0.1" min="0" max="100" wire:model="maxPayPercent"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pr-8">
                            <span class="absolute inset-y-0 right-3 flex items-center text-gray-400">%</span>
                        </div>
                        @error('maxPayPercent') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500">Максимальный % от заказа, который можно оплатить бонусами</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Приветственный бонус</label>
                        <input type="number" step="0.01" min="0" wire:model="welcomeBonus"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('welcomeBonus') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500">Бонусы, которые получает новый клиент при регистрации</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Срок действия бонусов
                            <span class="text-gray-400 font-normal">(дней)</span>
                        </label>
                        <input type="number" min="0" wire:model="bonusExpireDays"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('bonusExpireDays') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500">0 = бонусы не сгорают</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex items-center justify-between">
                <div>
                    @if ($saved)
                        <span class="text-sm text-green-600 font-medium" x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition>
                            Настройки сохранены
                        </span>
                    @endif
                </div>
                <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                    Сохранить настройки
                </button>
            </div>
        </form>
    </div>
</div>
