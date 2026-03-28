<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'RestoPOS — Вход' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-900 via-purple-900 to-gray-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        {{-- Логотип --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/10 backdrop-blur-sm mb-4">
                <span class="text-3xl">🍽️</span>
            </div>
            <h1 class="text-3xl font-bold text-white tracking-wide">RestoPOS</h1>
            <p class="text-indigo-200 mt-1 text-sm">Система управления рестораном</p>
        </div>

        {{-- Контент --}}
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            {{ $slot }}
        </div>

        <p class="text-center text-indigo-300 text-xs mt-6">
            &copy; {{ date('Y') }} RestoPOS. Все права защищены.
        </p>
    </div>

    @livewireScripts
</body>
</html>
