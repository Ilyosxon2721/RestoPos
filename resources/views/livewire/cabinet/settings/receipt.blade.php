<div>
    {{-- Заголовок --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Чек</h1>
        <p class="text-sm text-gray-500 mt-1">Настройки печати чеков</p>
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

                {{-- Заголовок чека --}}
                <div>
                    <label for="headerText" class="block text-sm font-medium text-gray-700 mb-1">Заголовок чека</label>
                    <textarea id="headerText" wire:model="headerText" rows="3"
                              class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                              placeholder="Название ресторана, адрес, телефон..."></textarea>
                    <p class="mt-1 text-xs text-gray-400">Отображается в верхней части чека (название, адрес, контакты)</p>
                    @error('headerText') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Подвал чека --}}
                <div>
                    <label for="footerText" class="block text-sm font-medium text-gray-700 mb-1">Подвал чека</label>
                    <textarea id="footerText" wire:model="footerText" rows="2"
                              class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                              placeholder="Спасибо за визит!"></textarea>
                    <p class="mt-1 text-xs text-gray-400">Текст благодарности или дополнительная информация в конце чека</p>
                    @error('footerText') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <hr class="border-gray-100">

                {{-- Логотип на чеке --}}
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Показывать логотип на чеке</label>
                        <p class="text-xs text-gray-400 mt-0.5">Логотип организации будет напечатан в шапке чека</p>
                    </div>
                    <button type="button" wire:click="$toggle('showLogo')"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $showLogo ? 'bg-indigo-600' : 'bg-gray-200' }}"
                            role="switch" aria-checked="{{ $showLogo ? 'true' : 'false' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $showLogo ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>

                <hr class="border-gray-100">

                {{-- Автопечать чека после оплаты --}}
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Автопечать чека после оплаты</label>
                        <p class="text-xs text-gray-400 mt-0.5">Чек будет автоматически отправлен на печать после проведения оплаты</p>
                    </div>
                    <button type="button" wire:click="$toggle('autoPrintReceipt')"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $autoPrintReceipt ? 'bg-indigo-600' : 'bg-gray-200' }}"
                            role="switch" aria-checked="{{ $autoPrintReceipt ? 'true' : 'false' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $autoPrintReceipt ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>

                {{-- Автопечать кухонного тикета --}}
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Автопечать кухонного тикета</label>
                        <p class="text-xs text-gray-400 mt-0.5">Тикет для кухни печатается автоматически при создании заказа</p>
                    </div>
                    <button type="button" wire:click="$toggle('autoPrintKitchenTicket')"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $autoPrintKitchenTicket ? 'bg-indigo-600' : 'bg-gray-200' }}"
                            role="switch" aria-checked="{{ $autoPrintKitchenTicket ? 'true' : 'false' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $autoPrintKitchenTicket ? 'translate-x-5' : 'translate-x-0' }}"></span>
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
