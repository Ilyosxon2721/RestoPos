<div>
    @if (! $showPinLogin)
        {{-- Форма входа по Email/Пароль --}}
        <form wire:submit="login" class="space-y-5">
            <div>
                @if ($tenantName)
                    <p class="text-indigo-600 text-sm font-semibold text-center mb-1">{{ $tenantName }}</p>
                @endif
                <h2 class="text-2xl font-bold text-gray-800 text-center">Вход в систему</h2>
                <p class="text-gray-500 text-sm text-center mt-1">Введите данные для входа</p>
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input wire:model="email"
                       type="email"
                       id="email"
                       placeholder="email@example.com"
                       autocomplete="email"
                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Пароль --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Пароль</label>
                <input wire:model="password"
                       type="password"
                       id="password"
                       placeholder="••••••••"
                       autocomplete="current-password"
                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Запомнить меня --}}
            <div class="flex items-center">
                <input wire:model="remember"
                       type="checkbox"
                       id="remember"
                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label for="remember" class="ml-2 text-sm text-gray-600">Запомнить меня</label>
            </div>

            {{-- Кнопка входа --}}
            <button type="submit"
                    wire:loading.attr="disabled"
                    class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition-colors">
                <span wire:loading.remove wire:target="login">Войти</span>
                <span wire:loading wire:target="login" class="inline-flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Вход...
                </span>
            </button>
        </form>

        {{-- Разделитель --}}
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="bg-white px-3 text-gray-500">или</span>
            </div>
        </div>

        {{-- Переключение на PIN --}}
        <button wire:click="togglePinLogin"
                class="w-full rounded-lg border-2 border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 hover:border-indigo-300 hover:text-indigo-600 transition-colors">
            Войти по PIN-коду
        </button>

        @if (! $tenantName)
            {{-- Ссылка на регистрацию --}}
            <p class="mt-4 text-center text-sm text-gray-500">
                Нет аккаунта?
                <a href="/register" class="font-medium text-indigo-600 hover:text-indigo-500">Зарегистрироваться</a>
            </p>
        @endif

    @else
        {{-- PIN-код --}}
        <div class="space-y-5">
            <div class="text-center">
                <h2 class="text-2xl font-bold text-gray-800">Вход по PIN</h2>
                <p class="text-gray-500 text-sm mt-1">Введите 4-значный PIN-код</p>
            </div>

            {{-- Индикатор PIN --}}
            <div class="flex justify-center space-x-3 py-4">
                @for ($i = 0; $i < 4; $i++)
                    <div class="h-4 w-4 rounded-full border-2 transition-colors {{ strlen($pin) > $i ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300' }}"></div>
                @endfor
            </div>

            @error('pin')
                <p class="text-sm text-red-600 text-center">{{ $message }}</p>
            @enderror

            {{-- Цифровая клавиатура --}}
            <div class="grid grid-cols-3 gap-3 max-w-xs mx-auto">
                @foreach (range(1, 9) as $digit)
                    <button wire:click="appendPin('{{ $digit }}')"
                            class="flex h-14 items-center justify-center rounded-xl bg-gray-100 text-xl font-semibold text-gray-800 hover:bg-indigo-100 hover:text-indigo-700 active:bg-indigo-200 transition-colors">
                        {{ $digit }}
                    </button>
                @endforeach
                <button wire:click="clearPin"
                        class="flex h-14 items-center justify-center rounded-xl bg-red-50 text-sm font-medium text-red-600 hover:bg-red-100 transition-colors">
                    Сброс
                </button>
                <button wire:click="appendPin('0')"
                        class="flex h-14 items-center justify-center rounded-xl bg-gray-100 text-xl font-semibold text-gray-800 hover:bg-indigo-100 hover:text-indigo-700 active:bg-indigo-200 transition-colors">
                    0
                </button>
                <button wire:click="backspacePin"
                        class="flex h-14 items-center justify-center rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l7-7 11 0a1 1 0 011 1v12a1 1 0 01-1 1H10l-7-7z"/>
                    </svg>
                </button>
            </div>

            {{-- Разделитель --}}
            <div class="relative my-4">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="bg-white px-3 text-gray-500">или</span>
                </div>
            </div>

            {{-- Переключение на Email --}}
            <button wire:click="togglePinLogin"
                    class="w-full rounded-lg border-2 border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 hover:border-indigo-300 hover:text-indigo-600 transition-colors">
                Войти по Email
            </button>
        </div>
    @endif
</div>
