<div>
    <form wire:submit="register" class="space-y-5">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 text-center">Регистрация</h2>
            <p class="text-gray-500 text-sm text-center mt-1">Создайте аккаунт для вашего заведения</p>
        </div>

        {{-- Название организации --}}
        <div>
            <label for="organizationName" class="block text-sm font-medium text-gray-700 mb-1">Название заведения</label>
            <input wire:model.live.debounce.300ms="organizationName"
                   type="text"
                   id="organizationName"
                   placeholder="Например: LoloTea"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition @error('organizationName') border-red-500 @enderror">
            @error('organizationName')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Субдомен --}}
        <div>
            <label for="subdomain" class="block text-sm font-medium text-gray-700 mb-1">Адрес вашей системы</label>
            <div class="flex items-center">
                <input wire:model.live.debounce.300ms="subdomain"
                       type="text"
                       id="subdomain"
                       placeholder="lolotea"
                       class="flex-1 rounded-l-lg border border-r-0 border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition @error('subdomain') border-red-500 @enderror">
                <span class="inline-flex items-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-500">
                    .{{ config('forris.base_domain') }}
                </span>
            </div>
            @error('subdomain')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @if ($subdomain && !$errors->has('subdomain'))
                <p class="mt-1 text-sm text-green-600">
                    {{ $subdomain }}.{{ config('forris.base_domain') }}
                </p>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-3">
            {{-- Имя --}}
            <div>
                <label for="firstName" class="block text-sm font-medium text-gray-700 mb-1">Имя</label>
                <input wire:model="firstName"
                       type="text"
                       id="firstName"
                       placeholder="Иван"
                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition @error('firstName') border-red-500 @enderror">
                @error('firstName')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Фамилия --}}
            <div>
                <label for="lastName" class="block text-sm font-medium text-gray-700 mb-1">Фамилия</label>
                <input wire:model="lastName"
                       type="text"
                       id="lastName"
                       placeholder="Иванов"
                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition @error('lastName') border-red-500 @enderror">
                @error('lastName')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input wire:model="email"
                   type="email"
                   id="email"
                   placeholder="ivan@example.com"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition @error('email') border-red-500 @enderror">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Телефон --}}
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Телефон <span class="text-gray-400">(необязательно)</span></label>
            <input wire:model="phone"
                   type="tel"
                   id="phone"
                   placeholder="+998 90 123 45 67"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition @error('phone') border-red-500 @enderror">
            @error('phone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Пароль --}}
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Пароль</label>
            <input wire:model="password"
                   type="password"
                   id="password"
                   placeholder="Минимум 8 символов"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition @error('password') border-red-500 @enderror">
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Подтверждение пароля --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Подтвердите пароль</label>
            <input wire:model="password_confirmation"
                   type="password"
                   id="password_confirmation"
                   placeholder="Повторите пароль"
                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition">
        </div>

        {{-- Кнопка регистрации --}}
        <button type="submit"
                wire:loading.attr="disabled"
                class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition-colors">
            <span wire:loading.remove wire:target="register">Создать аккаунт</span>
            <span wire:loading wire:target="register" class="inline-flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Создание...
            </span>
        </button>

        {{-- Ссылка на вход --}}
        <p class="text-center text-sm text-gray-500">
            Уже есть аккаунт?
            <a href="/login" class="font-medium text-indigo-600 hover:text-indigo-500">Войти</a>
        </p>
    </form>

    {{-- Информация --}}
    <div class="mt-6 rounded-lg bg-gray-50 p-3">
        <p class="text-xs text-gray-500 text-center">
            14 дней бесплатного пробного периода. Без привязки карты.
        </p>
    </div>
</div>
