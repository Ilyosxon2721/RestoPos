<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="refresh" content="60">
    <title>{{ $title ?? 'FORRIS POS — Кухня' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body {
            background-color: #111827;
            color: #f9fafb;
            overflow-x: hidden;
        }
        /* Предотвращаем скроллбар мигание */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #1f2937;
        }
        ::-webkit-scrollbar-thumb {
            background: #4b5563;
            border-radius: 3px;
        }
    </style>
</head>
<body class="min-h-screen" x-data="{ currentTime: '' }" x-init="
    const updateTime = () => {
        const now = new Date();
        currentTime = now.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    };
    updateTime();
    setInterval(updateTime, 1000);

    // Wake Lock API — предотвращаем засыпание экрана
    async function requestWakeLock() {
        try {
            if ('wakeLock' in navigator) {
                let wakeLock = await navigator.wakeLock.request('screen');
                document.addEventListener('visibilitychange', async () => {
                    if (document.visibilityState === 'visible') {
                        wakeLock = await navigator.wakeLock.request('screen');
                    }
                });
            }
        } catch (err) {
            console.log('Wake Lock не поддерживается:', err);
        }
    }
    requestWakeLock();
">
    {{-- Верхняя панель --}}
    <header class="sticky top-0 z-50 flex h-14 items-center justify-between bg-gray-800 border-b border-gray-700 px-6 shadow-lg">
        <div class="flex items-center space-x-3">
            <x-logo variant="icon" color="light" size="sm" />
            <h1 class="text-xl font-bold text-white tracking-wide">Кухня</h1>
        </div>

        <div class="flex items-center space-x-6">
            <span class="text-2xl font-mono text-green-400 tabular-nums" x-text="currentTime"></span>
            <a href="/dashboard"
               class="inline-flex items-center rounded-md bg-gray-700 px-3 py-1.5 text-sm font-medium text-gray-200 hover:bg-gray-600 transition-colors">
                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Назад
            </a>
        </div>
    </header>

    {{-- Контент --}}
    <main class="p-4">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
