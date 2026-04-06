<div>
    {{-- Заголовок --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Столы</h1>
        <p class="text-sm text-gray-500 mt-1">Настройки залов и столов</p>
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

                {{-- Включить бронирование --}}
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Включить бронирование столов</label>
                        <p class="text-xs text-gray-400 mt-0.5">Разрешить бронирование столов заранее</p>
                    </div>
                    <button type="button" wire:click="$toggle('reservationsEnabled')"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $reservationsEnabled ? 'bg-indigo-600' : 'bg-gray-200' }}"
                            role="switch" aria-checked="{{ $reservationsEnabled ? 'true' : 'false' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $reservationsEnabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>

                <hr class="border-gray-100">

                {{-- Автоосвобождение стола --}}
                <div>
                    <label for="autoReleaseMinutes" class="block text-sm font-medium text-gray-700 mb-1">Автоосвобождение стола через</label>
                    <div class="relative">
                        <input type="number" id="autoReleaseMinutes" wire:model="autoReleaseMinutes" min="5" max="1440"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pr-16">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-400 text-sm">мин.</span>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Стол будет автоматически освобождён, если заказ не активен указанное время</p>
                    @error('autoReleaseMinutes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Длительность бронирования по умолчанию --}}
                <div>
                    <label for="defaultReservationDuration" class="block text-sm font-medium text-gray-700 mb-1">Длительность бронирования по умолчанию</label>
                    <div class="relative">
                        <input type="number" id="defaultReservationDuration" wire:model="defaultReservationDuration" min="15" max="480"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pr-16">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-400 text-sm">мин.</span>
                        </div>
                    </div>
                    @error('defaultReservationDuration') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <hr class="border-gray-100">

                {{-- Обязательный выбор стола --}}
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Обязательный выбор стола для заказов «В зале»</label>
                        <p class="text-xs text-gray-400 mt-0.5">При создании заказа типа «В зале» необходимо выбрать стол</p>
                    </div>
                    <button type="button" wire:click="$toggle('requireTableForDineIn')"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $requireTableForDineIn ? 'bg-indigo-600' : 'bg-gray-200' }}"
                            role="switch" aria-checked="{{ $requireTableForDineIn ? 'true' : 'false' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $requireTableForDineIn ? 'translate-x-5' : 'translate-x-0' }}"></span>
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
