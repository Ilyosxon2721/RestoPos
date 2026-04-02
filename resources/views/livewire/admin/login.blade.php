<div>
    <form wire:submit="login" class="space-y-5">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 text-center">Админ-панель FORRIS POS</h2>
            <p class="text-gray-500 text-sm text-center mt-1">Вход для администратора платформы</p>
        </div>

        {{-- Ошибка авторизации --}}
        @if ($error)
            <div class="rounded-lg bg-red-50 border border-red-200 p-3">
                <p class="text-sm text-red-700 text-center">{{ $error }}</p>
            </div>
        @endif

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input wire:model="email"
                   type="email"
                   id="email"
                   placeholder="admin@forris.uz"
                   autocomplete="email"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 focus:outline-none transition @error('email') border-red-500 @enderror">
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
                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 focus:outline-none transition @error('password') border-red-500 @enderror">
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Запомнить меня --}}
        <div class="flex items-center">
            <input wire:model="remember"
                   type="checkbox"
                   id="remember"
                   class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
            <label for="remember" class="ml-2 text-sm text-gray-600">Запомнить меня</label>
        </div>

        {{-- Кнопка входа --}}
        <button type="submit"
                wire:loading.attr="disabled"
                class="w-full rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 disabled:opacity-50 transition-colors">
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

    {{-- Ссылка на обычный вход --}}
    <div class="mt-6 text-center">
        <a href="{{ route('login') }}" class="text-sm text-orange-600 hover:text-orange-700 font-medium">
            &larr; Вернуться к обычному входу
        </a>
    </div>
</div>
