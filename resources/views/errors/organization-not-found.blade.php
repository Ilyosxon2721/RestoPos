<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Организация не найдена — FORRIS POS</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-900 via-purple-900 to-gray-900 flex items-center justify-center p-4">
    <div class="w-full max-w-lg">
        {{-- Логотип --}}
        <div class="text-center mb-8">
            <div class="mb-4">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white/10 backdrop-blur-sm">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Карточка --}}
        <div class="bg-white rounded-2xl shadow-2xl p-8 text-center">
            <div class="mx-auto mb-6 flex items-center justify-center w-16 h-16 rounded-full bg-red-100">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>

            <h2 class="text-2xl font-bold text-gray-900 mb-3">Организация не найдена</h2>

            <p class="text-gray-600 mb-6 leading-relaxed">
                По данному адресу не зарегистрирована ни одна организация.
                Проверьте правильность ссылки или обратитесь к администратору.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="https://pos.forris.uz"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    На главную
                </a>
                <a href="https://pos.forris.uz/register"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg border-2 border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-700 hover:border-indigo-300 hover:text-indigo-600 transition-colors">
                    Зарегистрироваться
                </a>
            </div>
        </div>

        <p class="text-center text-indigo-400/50 text-xs mt-6">
            &copy; {{ date('Y') }} FORRIS POS. Все права защищены.
        </p>
    </div>
</body>
</html>
