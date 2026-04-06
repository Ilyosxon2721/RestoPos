<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Аккаунт не активирован — FORRIS POS</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-900 via-purple-900 to-gray-900 flex items-center justify-center p-4">
    <div class="w-full max-w-lg">
        {{-- Логотип --}}
        <div class="text-center mb-8">
            <div class="mb-4">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white/10 backdrop-blur-sm">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            @if (isset($organizationName))
                <h1 class="text-2xl font-bold text-white tracking-wide">{{ $organizationName }}</h1>
            @endif
        </div>

        {{-- Карточка --}}
        <div class="bg-white rounded-2xl shadow-2xl p-8 text-center">
            {{-- Иконка статуса --}}
            <div class="mx-auto mb-6 flex items-center justify-center w-16 h-16 rounded-full bg-amber-100">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>

            <h2 class="text-2xl font-bold text-gray-900 mb-3">Аккаунт ещё не активирован</h2>

            <p class="text-gray-600 mb-6 leading-relaxed">
                Ваша организация зарегистрирована, но пока не активирована администратором платформы.
                Обычно это занимает до 24 часов.
            </p>

            {{-- Шаги --}}
            <div class="bg-gray-50 rounded-xl p-5 mb-6 text-left">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wider">Что дальше?</h3>
                <ul class="space-y-3">
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold">1</span>
                        <span class="text-sm text-gray-600">Регистрация получена</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </span>
                        <span class="text-sm text-gray-800 font-medium">Проверка администратором</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center text-xs font-bold">3</span>
                        <span class="text-sm text-gray-400">Активация и доступ к системе</span>
                    </li>
                </ul>
            </div>

            {{-- Контакты --}}
            <div class="border-t border-gray-200 pt-5">
                <p class="text-sm text-gray-500 mb-3">Есть вопросы? Свяжитесь с нами:</p>
                <div class="flex items-center justify-center gap-4">
                    <a href="mailto:support@forris.uz" class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                        support@forris.uz
                    </a>
                    <a href="https://t.me/forris_support" class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                        </svg>
                        Telegram
                    </a>
                </div>
            </div>
        </div>

        {{-- Ссылка назад --}}
        <div class="text-center mt-6">
            <a href="https://pos.forris.uz" class="text-indigo-300 hover:text-white text-sm transition-colors">
                &larr; Вернуться на главную
            </a>
        </div>

        <p class="text-center text-indigo-400/50 text-xs mt-4">
            &copy; {{ date('Y') }} FORRIS POS. Все права защищены.
        </p>
    </div>
</body>
</html>
