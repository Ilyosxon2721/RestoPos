<div>
    {{-- Заголовок --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Безопасность</h1>
        <p class="text-sm text-gray-500 mt-1">Настройки безопасности</p>
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

        {{-- Настройки безопасности --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Параметры доступа</h2>
            <form wire:submit="save">
                <div class="space-y-6">

                    {{-- PIN-код для терминала --}}
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Требовать PIN-код для терминала</label>
                            <p class="text-xs text-gray-400 mt-0.5">Сотрудники должны вводить PIN при входе в терминал</p>
                        </div>
                        <button type="button" wire:click="$toggle('requirePin')"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $requirePin ? 'bg-indigo-600' : 'bg-gray-200' }}"
                                role="switch" aria-checked="{{ $requirePin ? 'true' : 'false' }}">
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $requirePin ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>

                    <hr class="border-gray-100">

                    {{-- Таймаут сессии --}}
                    <div>
                        <label for="sessionTimeout" class="block text-sm font-medium text-gray-700 mb-1">Таймаут сессии</label>
                        <div class="relative">
                            <input type="number" id="sessionTimeout" wire:model="sessionTimeout" min="5" max="480"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pr-16">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-400 text-sm">мин.</span>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Автоматический выход из системы при отсутствии активности</p>
                        @error('sessionTimeout') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <hr class="border-gray-100">

                    {{-- Требования к паролю --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Требования к паролю</label>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Минимум 8 символов
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Содержит буквы и цифры
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Рекомендуется использовать спецсимволы
                                </li>
                            </ul>
                        </div>
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

        {{-- Журнал активности --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Журнал входов</h2>
            <p class="text-sm text-gray-500 mb-4">Последние 20 событий входа в систему</p>

            @if ($loginLogs->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Пользователь</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP-адрес</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата и время</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($loginLogs as $log)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-800">{{ $log->user?->name ?? 'Неизвестный' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 font-mono">{{ $log->ip_address ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $log->created_at?->format('d.m.Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-sm text-gray-400">Записей о входах пока нет</p>
                </div>
            @endif
        </div>
    </div>
</div>
