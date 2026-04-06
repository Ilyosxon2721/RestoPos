<div class="max-w-5xl mx-auto">
    {{-- Заголовок --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">FORRIS POS — Брендбук</h1>
        <p class="text-gray-500 mt-2">Руководство по фирменному стилю экосистемы FORRIS</p>
    </div>

    {{-- ===== 1. Логотип ===== --}}
    <section class="mb-12">
        <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200">1. Логотип</h2>

        {{-- Основной логотип --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-2xl border border-gray-200 p-8 flex flex-col items-center justify-center">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-6">На светлом фоне</p>
                <x-logo variant="full" color="default" size="xl" />
            </div>
            <div class="bg-gray-900 rounded-2xl border border-gray-700 p-8 flex flex-col items-center justify-center">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-6">На тёмном фоне</p>
                <x-logo variant="full" color="light" size="xl" />
            </div>
        </div>

        {{-- Варианты --}}
        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-3">Варианты использования</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6 flex flex-col items-center text-center">
                <x-logo variant="full" color="default" size="md" />
                <p class="text-xs text-gray-400 mt-3">Полный (по умолч.)</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-6 flex flex-col items-center text-center">
                <x-logo variant="icon" color="default" size="lg" />
                <p class="text-xs text-gray-400 mt-3">Только иконка</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-6 flex flex-col items-center text-center">
                <x-logo variant="text" color="default" size="md" />
                <p class="text-xs text-gray-400 mt-3">Только текст</p>
            </div>
            <div class="bg-indigo-600 rounded-xl p-6 flex flex-col items-center text-center">
                <x-logo variant="full" color="white" size="md" />
                <p class="text-xs text-indigo-200 mt-3">На цветном фоне</p>
            </div>
        </div>

        {{-- Размеры --}}
        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-3">Размеры</h3>
        <div class="bg-white rounded-xl border border-gray-200 p-6 flex items-end justify-around">
            <div class="text-center">
                <x-logo variant="full" color="default" size="sm" />
                <p class="text-xs text-gray-400 mt-2">SM</p>
            </div>
            <div class="text-center">
                <x-logo variant="full" color="default" size="md" />
                <p class="text-xs text-gray-400 mt-2">MD</p>
            </div>
            <div class="text-center">
                <x-logo variant="full" color="default" size="lg" />
                <p class="text-xs text-gray-400 mt-2">LG</p>
            </div>
            <div class="text-center">
                <x-logo variant="full" color="default" size="xl" />
                <p class="text-xs text-gray-400 mt-2">XL</p>
            </div>
        </div>
    </section>

    {{-- ===== 2. Цветовая палитра ===== --}}
    <section class="mb-12">
        <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200">2. Цветовая палитра</h2>

        {{-- Основные цвета --}}
        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-3">Основные цвета</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div>
                <div class="h-24 rounded-xl bg-indigo-600 shadow-sm mb-2 flex items-end p-3">
                    <span class="text-xs text-white/80 font-mono">#4F46E5</span>
                </div>
                <p class="text-sm font-medium text-gray-700">Indigo 600</p>
                <p class="text-xs text-gray-400">Primary — основной</p>
            </div>
            <div>
                <div class="h-24 rounded-xl bg-indigo-500 shadow-sm mb-2 flex items-end p-3">
                    <span class="text-xs text-white/80 font-mono">#6366F1</span>
                </div>
                <p class="text-sm font-medium text-gray-700">Indigo 500</p>
                <p class="text-xs text-gray-400">Accent — акцентный</p>
            </div>
            <div>
                <div class="h-24 rounded-xl bg-indigo-100 shadow-sm mb-2 flex items-end p-3">
                    <span class="text-xs text-indigo-600 font-mono">#E0E7FF</span>
                </div>
                <p class="text-sm font-medium text-gray-700">Indigo 100</p>
                <p class="text-xs text-gray-400">Light — фоны</p>
            </div>
            <div>
                <div class="h-24 rounded-xl bg-gray-900 shadow-sm mb-2 flex items-end p-3">
                    <span class="text-xs text-white/80 font-mono">#111827</span>
                </div>
                <p class="text-sm font-medium text-gray-700">Gray 900</p>
                <p class="text-xs text-gray-400">Dark — сайдбар</p>
            </div>
            <div>
                <div class="h-24 rounded-xl bg-white border border-gray-200 shadow-sm mb-2 flex items-end p-3">
                    <span class="text-xs text-gray-400 font-mono">#FFFFFF</span>
                </div>
                <p class="text-sm font-medium text-gray-700">White</p>
                <p class="text-xs text-gray-400">Карточки, фон</p>
            </div>
        </div>

        {{-- Функциональные цвета --}}
        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-3">Функциональные цвета</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div>
                <div class="h-16 rounded-xl bg-green-500 shadow-sm mb-2 flex items-end p-3">
                    <span class="text-xs text-white/80 font-mono">#22C55E</span>
                </div>
                <p class="text-sm font-medium text-gray-700">Green 500</p>
                <p class="text-xs text-gray-400">Успех, активен, оплачен</p>
            </div>
            <div>
                <div class="h-16 rounded-xl bg-amber-500 shadow-sm mb-2 flex items-end p-3">
                    <span class="text-xs text-white/80 font-mono">#F59E0B</span>
                </div>
                <p class="text-sm font-medium text-gray-700">Amber 500</p>
                <p class="text-xs text-gray-400">Внимание, ожидание</p>
            </div>
            <div>
                <div class="h-16 rounded-xl bg-red-500 shadow-sm mb-2 flex items-end p-3">
                    <span class="text-xs text-white/80 font-mono">#EF4444</span>
                </div>
                <p class="text-sm font-medium text-gray-700">Red 500</p>
                <p class="text-xs text-gray-400">Ошибка, удаление, списание</p>
            </div>
            <div>
                <div class="h-16 rounded-xl bg-orange-500 shadow-sm mb-2 flex items-end p-3">
                    <span class="text-xs text-white/80 font-mono">#F97316</span>
                </div>
                <p class="text-sm font-medium text-gray-700">Orange 500</p>
                <p class="text-xs text-gray-400">Кухня, готовка</p>
            </div>
        </div>

        {{-- Цвета панелей --}}
        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-3">Цвета по панелям</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="rounded-xl bg-indigo-600 p-4 text-center">
                <span class="text-sm font-bold text-white">Кабинет</span>
                <p class="text-xs text-indigo-200 mt-1">Indigo</p>
            </div>
            <div class="rounded-xl bg-emerald-600 p-4 text-center">
                <span class="text-sm font-bold text-white">Менеджер</span>
                <p class="text-xs text-emerald-200 mt-1">Emerald</p>
            </div>
            <div class="rounded-xl bg-orange-500 p-4 text-center">
                <span class="text-sm font-bold text-white">Кухня</span>
                <p class="text-xs text-orange-200 mt-1">Orange</p>
            </div>
            <div class="rounded-xl bg-amber-500 p-4 text-center">
                <span class="text-sm font-bold text-white">Склад</span>
                <p class="text-xs text-amber-200 mt-1">Amber</p>
            </div>
            <div class="rounded-xl bg-red-600 p-4 text-center">
                <span class="text-sm font-bold text-white">Админ</span>
                <p class="text-xs text-red-200 mt-1">Red</p>
            </div>
        </div>
    </section>

    {{-- ===== 3. Типографика ===== --}}
    <section class="mb-12">
        <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200">3. Типографика</h2>

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Основной шрифт</p>
                <p class="text-4xl font-bold text-gray-900">Inter / System UI</p>
                <p class="text-sm text-gray-500 mt-1">Используется Tailwind CSS default — <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">font-sans</code></p>
            </div>

            <div class="border-t border-gray-100 pt-6">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-3">Иерархия заголовков</p>
                <div class="space-y-3">
                    <div class="flex items-baseline gap-4">
                        <code class="text-xs text-gray-400 w-20 flex-shrink-0">text-3xl</code>
                        <p class="text-3xl font-bold text-gray-900">Заголовок страницы</p>
                    </div>
                    <div class="flex items-baseline gap-4">
                        <code class="text-xs text-gray-400 w-20 flex-shrink-0">text-2xl</code>
                        <p class="text-2xl font-bold text-gray-800">Заголовок раздела</p>
                    </div>
                    <div class="flex items-baseline gap-4">
                        <code class="text-xs text-gray-400 w-20 flex-shrink-0">text-lg</code>
                        <p class="text-lg font-semibold text-gray-700">Заголовок карточки</p>
                    </div>
                    <div class="flex items-baseline gap-4">
                        <code class="text-xs text-gray-400 w-20 flex-shrink-0">text-sm</code>
                        <p class="text-sm font-medium text-gray-600">Подзаголовок / метка</p>
                    </div>
                    <div class="flex items-baseline gap-4">
                        <code class="text-xs text-gray-400 w-20 flex-shrink-0">text-xs</code>
                        <p class="text-xs text-gray-400">Мета-информация / подпись</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-6">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-3">Моноширинный шрифт</p>
                <p class="font-mono text-sm text-gray-700">0123456789 — для цен, артикулов, штрих-кодов</p>
                <p class="text-xs text-gray-400 mt-1"><code class="bg-gray-100 px-1.5 py-0.5 rounded">font-mono</code></p>
            </div>
        </div>
    </section>

    {{-- ===== 4. Компоненты UI ===== --}}
    <section class="mb-12">
        <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200">4. UI компоненты</h2>

        {{-- Кнопки --}}
        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-3">Кнопки</h3>
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <div class="flex flex-wrap items-center gap-3">
                <button class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
                    Primary
                </button>
                <button class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    Secondary
                </button>
                <button class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 transition-colors">
                    Danger
                </button>
                <button class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 transition-colors">
                    Success
                </button>
                <button class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                    Link
                </button>
            </div>
            <p class="text-xs text-gray-400 mt-3">Скругление: <code class="bg-gray-100 px-1 rounded">rounded-lg</code> | Размер: <code class="bg-gray-100 px-1 rounded">px-4 py-2 text-sm</code></p>
        </div>

        {{-- Бейджи --}}
        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-3">Статус-бейджи</h3>
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Активен</span>
                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Неактивен</span>
                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">Ожидает</span>
                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">В процессе</span>
                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">Черновик</span>
                <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">По умолч.</span>
                <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800">Премиум</span>
                <span class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-800">Готовится</span>
            </div>
            <p class="text-xs text-gray-400 mt-3">Форма: <code class="bg-gray-100 px-1 rounded">rounded-full</code> | Палитра: <code class="bg-gray-100 px-1 rounded">bg-{color}-100 text-{color}-800</code></p>
        </div>

        {{-- Карточки --}}
        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-3">Карточки</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h4 class="font-semibold text-gray-800 mb-1">Стандартная карточка</h4>
                <p class="text-sm text-gray-500">rounded-xl shadow-sm border</p>
            </div>
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
                <h4 class="font-semibold text-gray-800 mb-1">Акцентная карточка</h4>
                <p class="text-sm text-gray-500">rounded-2xl shadow-md</p>
            </div>
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-xl shadow-lg p-6">
                <h4 class="font-semibold text-white mb-1">Промо карточка</h4>
                <p class="text-sm text-indigo-200">gradient + shadow-lg</p>
            </div>
        </div>
    </section>

    {{-- ===== 5. Иконография ===== --}}
    <section class="mb-12">
        <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200">5. Иконография</h2>
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-4">Используется <strong>Heroicons</strong> (outline, stroke-width 2) — встроены в Tailwind UI</p>
            <div class="flex flex-wrap gap-4">
                @foreach([
                    'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z',
                    'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                    'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                    'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                    'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                ] as $path)
                    <div class="w-12 h-12 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}"/>
                        </svg>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===== 6. Правила использования логотипа ===== --}}
    <section class="mb-12">
        <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200">6. Правила использования</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-green-50 rounded-xl border border-green-200 p-6">
                <h3 class="font-semibold text-green-800 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Правильно
                </h3>
                <ul class="text-sm text-green-700 space-y-2">
                    <li>Используйте логотип с достаточным отступом</li>
                    <li>Соблюдайте пропорции иконки и текста</li>
                    <li>Используйте утверждённые цветовые схемы</li>
                    <li>На тёмном фоне — вариант <code class="bg-green-100 px-1 rounded">color="light"</code></li>
                    <li>Минимальный размер иконки: 24x24px (SM)</li>
                    <li>FORRIS — заглавными, POS — заглавными</li>
                </ul>
            </div>
            <div class="bg-red-50 rounded-xl border border-red-200 p-6">
                <h3 class="font-semibold text-red-800 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Неправильно
                </h3>
                <ul class="text-sm text-red-700 space-y-2">
                    <li>Не изменяйте пропорции логотипа</li>
                    <li>Не используйте другие цвета для иконки</li>
                    <li>Не поворачивайте и не наклоняйте логотип</li>
                    <li>Не пишите «Forris», «forris», «FORRIS pos»</li>
                    <li>Не размещайте на пёстром фоне без подложки</li>
                    <li>Не добавляйте тени или обводки к логотипу</li>
                </ul>
            </div>
        </div>
    </section>

    {{-- ===== 7. Экосистема FORRIS ===== --}}
    <section class="mb-12">
        <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200">7. Экосистема FORRIS</h2>
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-4">FORRIS POS является частью экосистемы FORRIS. Каждый продукт имеет свой акцентный цвет, но общий стиль иконки (шестиугольник + буква).</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-4 rounded-xl bg-indigo-50 border border-indigo-100">
                    <x-logo variant="icon" color="default" size="lg" class="mx-auto" />
                    <p class="text-sm font-bold text-indigo-700 mt-2">FORRIS POS</p>
                    <p class="text-xs text-indigo-400">pos.forris.uz</p>
                </div>
                <div class="text-center p-4 rounded-xl bg-emerald-50 border border-emerald-100">
                    <div class="w-10 h-10 mx-auto">
                        <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 6L32.66 13V27L20 34L7.34 27V13L20 6Z" fill="#10B981"/>
                            <path d="M14 12H26V15.5H18V18.5H24.5V22H18V28H14V12Z" fill="white"/>
                            <circle cx="28" cy="12" r="2.5" fill="#34D399"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-emerald-700 mt-2">FORRIS CRM</p>
                    <p class="text-xs text-emerald-400">crm.forris.uz</p>
                </div>
                <div class="text-center p-4 rounded-xl bg-blue-50 border border-blue-100">
                    <div class="w-10 h-10 mx-auto">
                        <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 6L32.66 13V27L20 34L7.34 27V13L20 6Z" fill="#3B82F6"/>
                            <path d="M14 12H26V15.5H18V18.5H24.5V22H18V28H14V12Z" fill="white"/>
                            <circle cx="28" cy="12" r="2.5" fill="#60A5FA"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-blue-700 mt-2">FORRIS ERP</p>
                    <p class="text-xs text-blue-400">erp.forris.uz</p>
                </div>
                <div class="text-center p-4 rounded-xl bg-violet-50 border border-violet-100">
                    <div class="w-10 h-10 mx-auto">
                        <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 6L32.66 13V27L20 34L7.34 27V13L20 6Z" fill="#8B5CF6"/>
                            <path d="M14 12H26V15.5H18V18.5H24.5V22H18V28H14V12Z" fill="white"/>
                            <circle cx="28" cy="12" r="2.5" fill="#A78BFA"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-violet-700 mt-2">FORRIS HR</p>
                    <p class="text-xs text-violet-400">hr.forris.uz</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Футер ===== --}}
    <div class="text-center text-xs text-gray-400 pb-8">
        &copy; {{ date('Y') }} FORRIS. Все права защищены. Версия брендбука 1.0
    </div>
</div>
