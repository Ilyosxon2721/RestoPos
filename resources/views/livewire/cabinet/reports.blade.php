<div>
    {{-- Заголовок --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Отчёты</h1>
        <p class="text-sm text-gray-500 mt-1">Аналитика и отчёты по вашему бизнесу</p>
    </div>

    {{-- Карточки отчётов --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        {{-- Отчёт по продажам --}}
        <a href="#" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-start">
                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">Отчёт по продажам</h3>
                    <p class="text-sm text-gray-500 mt-1">Выручка, средний чек, динамика продаж</p>
                </div>
            </div>
        </a>

        {{-- Отчёт по товарам --}}
        <a href="#" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-start">
                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">Отчёт по товарам</h3>
                    <p class="text-sm text-gray-500 mt-1">Топ продаж, ABC-анализ, маржинальность</p>
                </div>
            </div>
        </a>

        {{-- Отчёт по сотрудникам --}}
        <a href="#" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-start">
                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">Отчёт по сотрудникам</h3>
                    <p class="text-sm text-gray-500 mt-1">Производительность, продажи по официантам</p>
                </div>
            </div>
        </a>

        {{-- Финансовый отчёт --}}
        <a href="#" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-start">
                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-orange-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">Финансовый отчёт</h3>
                    <p class="text-sm text-gray-500 mt-1">P&L, движение денежных средств</p>
                </div>
            </div>
        </a>
    </div>
</div>
