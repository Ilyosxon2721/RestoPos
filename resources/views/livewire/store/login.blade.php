<div class="min-h-[70vh] flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-sm">
        @if(auth('customer')->check())
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">Вы уже авторизованы</h2>
                <p class="text-gray-500 mb-4">{{ auth('customer')->user()->full_name }}</p>
                <a href="{{ route('shop.home', ['slug' => $store->slug]) }}" class="inline-flex items-center px-6 py-2.5 bg-primary text-white font-medium rounded-xl hover:opacity-90 transition">
                    На главную
                </a>
            </div>
        @else
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Вход</h1>
                <p class="text-gray-500 mt-1">Введите номер телефона</p>
            </div>

            {{-- Шаг 1: Телефон --}}
            @if($step === 'phone')
                <form wire:submit="sendCode" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Номер телефона</label>
                        <input
                            type="tel"
                            wire:model="phone"
                            placeholder="+998 90 123 45 67"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-lg focus:ring-2 ring-primary focus:border-transparent"
                            autofocus
                        >
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="w-full py-3 bg-primary text-white font-medium rounded-xl hover:opacity-90 transition" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="sendCode">Получить код</span>
                        <span wire:loading wire:target="sendCode">Отправка...</span>
                    </button>
                </form>
            @endif

            {{-- Шаг 2: Код подтверждения --}}
            @if($step === 'code')
                <form wire:submit="verifyCode" class="space-y-4">
                    <div class="text-center mb-2">
                        <p class="text-sm text-gray-600">Код отправлен на</p>
                        <p class="font-medium">{{ $phone }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Код из SMS</label>
                        <input
                            type="text"
                            wire:model="code"
                            placeholder="000000"
                            maxlength="6"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-2xl text-center tracking-[0.5em] font-mono focus:ring-2 ring-primary focus:border-transparent"
                            autofocus
                        >
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if($error)
                        <p class="text-sm text-red-600 text-center">{{ $error }}</p>
                    @endif

                    <button type="submit" class="w-full py-3 bg-primary text-white font-medium rounded-xl hover:opacity-90 transition" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="verifyCode">Подтвердить</span>
                        <span wire:loading wire:target="verifyCode">Проверка...</span>
                    </button>

                    <div class="flex items-center justify-between text-sm">
                        <button type="button" wire:click="$set('step', 'phone')" class="text-gray-500 hover:text-gray-700">
                            Изменить номер
                        </button>
                        <button type="button" wire:click="resendCode" class="text-primary hover:opacity-80">
                            Отправить ещё раз
                        </button>
                    </div>
                </form>
            @endif

            {{-- Шаг 3: Имя --}}
            @if($step === 'name')
                <form wire:submit="completeName" class="space-y-4">
                    <div class="text-center mb-2">
                        <p class="text-gray-600">Как вас зовут?</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Имя *</label>
                        <input
                            type="text"
                            wire:model="firstName"
                            placeholder="Имя"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 ring-primary focus:border-transparent"
                            autofocus
                        >
                        @error('firstName')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Фамилия</label>
                        <input
                            type="text"
                            wire:model="lastName"
                            placeholder="Фамилия"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 ring-primary focus:border-transparent"
                        >
                    </div>

                    <button type="submit" class="w-full py-3 bg-primary text-white font-medium rounded-xl hover:opacity-90 transition">
                        Продолжить
                    </button>
                </form>
            @endif
        @endif
    </div>
</div>
