<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'RestoPOS' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-100">
    <div class="flex min-h-screen flex-col">
        {{-- Верхняя панель --}}
        <header class="sticky top-0 z-30 flex h-14 items-center justify-between border-b bg-white px-4 shadow-sm">
            <div class="flex items-center space-x-4">
                <a href="/cashier" class="flex items-center space-x-2">
                    <span class="text-xl font-bold text-gray-800">RestoPOS</span>
                </a>
                <span class="hidden sm:inline-block h-6 w-px bg-gray-300"></span>
                {{-- Информация о текущей смене --}}
                <div class="hidden sm:flex items-center space-x-2 text-sm text-gray-600">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Смена открыта</span>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">
                    {{ auth()->user()?->name ?? 'Кассир' }}
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                        <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Выйти
                    </button>
                </form>
            </div>
        </header>

        {{-- Контент --}}
        <main class="flex-1">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
