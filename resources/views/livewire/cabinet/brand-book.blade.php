<div class="max-w-5xl mx-auto pb-16">

    {{-- ===== Обложка ===== --}}
    <div class="mb-16 pt-4">
        <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-12 md:p-16 text-center">
            <x-logo variant="icon" color="white" size="xl" class="mx-auto mb-6" />
            <h1 class="text-4xl md:text-5xl font-bold text-white tracking-tight mb-3">FORRIS POS</h1>
            <p class="text-indigo-300 text-lg">Руководство по фирменному стилю</p>
            <p class="text-indigo-400/60 text-sm mt-6">Версия 2.0 &middot; {{ date('Y') }}</p>
        </div>
    </div>

    {{-- ===== 1. Философия ===== --}}
    <section class="mb-16">
        <span class="text-xs font-semibold text-indigo-600 uppercase tracking-widest">01</span>
        <h2 class="text-2xl font-bold text-gray-900 mt-1 mb-6">Философия знака</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-4 text-gray-600 leading-relaxed">
                <p>
                    <strong class="text-gray-900">FORRIS</strong> восходит к латинскому <em>foris</em> — дверь, врата.
                    Для POS-системы это точная метафора: каждый ресторан проходит через нашу «дверь»,
                    чтобы управлять заказами, складом, финансами и командой.
                </p>
                <p>
                    Знак — это буква <strong class="text-gray-900">F</strong> в скруглённом квадрате.
                    Вертикальная ножка — опора, фундамент бизнеса.
                    Две горизонтали — структура и порядок.
                    Вместе они образуют архитектурный проём: вход в экосистему.
                </p>
                <p>
                    Никаких градиентов, теней, акцентных точек.
                    Только геометрия и пропорция. Знак должен работать
                    на визитке, на экране кассы и на фасаде заведения.
                </p>
            </div>
            <div class="flex items-center justify-center">
                <div class="relative">
                    {{-- Конструкционная сетка --}}
                    <svg class="w-56 h-56" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                        {{-- Фоновая сетка --}}
                        @for ($i = 0; $i <= 8; $i++)
                            <line x1="{{ $i * 25 }}" y1="0" x2="{{ $i * 25 }}" y2="200" stroke="#E0E7FF" stroke-width="0.5"/>
                            <line x1="0" y1="{{ $i * 25 }}" x2="200" y2="{{ $i * 25 }}" stroke="#E0E7FF" stroke-width="0.5"/>
                        @endfor

                        {{-- Контейнер --}}
                        <rect x="0" y="0" width="200" height="200" rx="50" fill="#1E1B4B" opacity="0.06"/>
                        <rect x="0" y="0" width="200" height="200" rx="50" fill="none" stroke="#1E1B4B" stroke-width="1.5" stroke-dasharray="6 4"/>

                        {{-- F --}}
                        <path d="M55 45H142.5V70H82.5V87.5H120V110H82.5V155H55V45Z" fill="#1E1B4B" opacity="0.12"/>
                        <path d="M55 45H142.5V70H82.5V87.5H120V110H82.5V155H55V45Z" fill="none" stroke="#4338CA" stroke-width="1.5"/>

                        {{-- Линии золотого сечения --}}
                        <line x1="0" y1="87.5" x2="200" y2="87.5" stroke="#EF4444" stroke-width="0.5" stroke-dasharray="4 3" opacity="0.6"/>
                        <text x="175" y="84" fill="#EF4444" font-size="8" opacity="0.7">φ</text>

                        {{-- Размерные линии --}}
                        <line x1="55" y1="165" x2="142.5" y2="165" stroke="#6366F1" stroke-width="0.8" opacity="0.5"/>
                        <line x1="55" y1="162" x2="55" y2="168" stroke="#6366F1" stroke-width="0.8" opacity="0.5"/>
                        <line x1="142.5" y1="162" x2="142.5" y2="168" stroke="#6366F1" stroke-width="0.8" opacity="0.5"/>
                        <text x="90" y="178" fill="#6366F1" font-size="8" text-anchor="middle" opacity="0.7">17.5</text>

                        <line x1="55" y1="165" x2="120" y2="165" stroke="#6366F1" stroke-width="0" opacity="0"/>
                        <line x1="150" y1="45" x2="150" y2="70" stroke="#6366F1" stroke-width="0.8" opacity="0.5"/>
                        <line x1="147" y1="45" x2="153" y2="45" stroke="#6366F1" stroke-width="0.8" opacity="0.5"/>
                        <line x1="147" y1="70" x2="153" y2="70" stroke="#6366F1" stroke-width="0.8" opacity="0.5"/>
                        <text x="162" y="60" fill="#6366F1" font-size="8" opacity="0.7">5</text>
                    </svg>
                    <p class="text-center text-xs text-gray-400 mt-3">Конструкция знака. Красная линия — золотое сечение</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== 2. Логотип ===== --}}
    <section class="mb-16">
        <span class="text-xs font-semibold text-indigo-600 uppercase tracking-widest">02</span>
        <h2 class="text-2xl font-bold text-gray-900 mt-1 mb-6">Логотип</h2>

        {{-- Основные варианты --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-2xl border border-gray-200 p-10 flex flex-col items-center justify-center min-h-[180px]">
                <p class="text-[10px] font-medium text-gray-400 uppercase tracking-widest mb-8">Светлый фон</p>
                <x-logo variant="full" color="default" size="xl" />
            </div>
            <div class="bg-gray-900 rounded-2xl p-10 flex flex-col items-center justify-center min-h-[180px]">
                <p class="text-[10px] font-medium text-gray-500 uppercase tracking-widest mb-8">Тёмный фон</p>
                <x-logo variant="full" color="light" size="xl" />
            </div>
        </div>

        {{-- Три варианта --}}
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Варианты</h3>
        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-xl border border-gray-200 p-8 flex flex-col items-center justify-center text-center">
                <x-logo variant="full" color="default" size="md" />
                <p class="text-[10px] text-gray-400 mt-4 uppercase tracking-wider">Полный</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-8 flex flex-col items-center justify-center text-center">
                <x-logo variant="icon" color="default" size="lg" />
                <p class="text-[10px] text-gray-400 mt-4 uppercase tracking-wider">Иконка</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-8 flex flex-col items-center justify-center text-center">
                <x-logo variant="text" color="default" size="md" />
                <p class="text-[10px] text-gray-400 mt-4 uppercase tracking-wider">Словесный</p>
            </div>
        </div>

        {{-- Размеры --}}
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Масштаб</h3>
        <div class="bg-white rounded-xl border border-gray-200 p-8 flex items-end justify-around">
            @foreach (['sm', 'md', 'lg', 'xl'] as $sz)
                <div class="text-center">
                    <x-logo variant="icon" color="default" :size="$sz" />
                    <p class="text-[10px] text-gray-400 mt-3 uppercase">{{ $sz }}</p>
                </div>
            @endforeach
        </div>

        {{-- Охранное поле --}}
        <div class="mt-8">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Охранное поле</h3>
            <div class="bg-white rounded-xl border border-gray-200 p-8 flex items-center justify-center">
                <div class="relative inline-block">
                    <div class="border-2 border-dashed border-indigo-200 rounded-xl p-8">
                        <x-logo variant="full" color="default" size="lg" />
                    </div>
                    <span class="absolute -top-2 left-1/2 -translate-x-1/2 bg-white px-2 text-[10px] text-indigo-400 uppercase tracking-wider">Минимум 1× высота иконки со всех сторон</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== 3. Цвета ===== --}}
    <section class="mb-16">
        <span class="text-xs font-semibold text-indigo-600 uppercase tracking-widest">03</span>
        <h2 class="text-2xl font-bold text-gray-900 mt-1 mb-6">Цветовая палитра</h2>

        {{-- Основные --}}
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Основная палитра</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            @php
                $brandColors = [
                    ['hex' => '#1E1B4B', 'name' => 'Indigo 950', 'role' => 'Знак, заголовки', 'ring' => 'ring-indigo-950', 'bg' => 'bg-[#1E1B4B]', 'textColor' => 'text-white/70'],
                    ['hex' => '#4338CA', 'name' => 'Indigo 700', 'role' => 'Знак на тёмном', 'ring' => 'ring-indigo-700', 'bg' => 'bg-[#4338CA]', 'textColor' => 'text-white/70'],
                    ['hex' => '#4F46E5', 'name' => 'Indigo 600', 'role' => 'Кнопки, ссылки', 'ring' => 'ring-indigo-600', 'bg' => 'bg-indigo-600', 'textColor' => 'text-white/70'],
                    ['hex' => '#E0E7FF', 'name' => 'Indigo 100', 'role' => 'Фоны, подложки', 'ring' => 'ring-indigo-100', 'bg' => 'bg-indigo-100', 'textColor' => 'text-indigo-600'],
                ];
            @endphp
            @foreach ($brandColors as $bc)
                <div>
                    <div class="h-28 rounded-xl {{ $bc['bg'] }} mb-3 flex items-end p-4">
                        <span class="text-xs font-mono {{ $bc['textColor'] }}">{{ $bc['hex'] }}</span>
                    </div>
                    <p class="text-sm font-medium text-gray-800">{{ $bc['name'] }}</p>
                    <p class="text-xs text-gray-400">{{ $bc['role'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Нейтральные --}}
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Нейтральные</h3>
        <div class="flex gap-1 mb-8 rounded-xl overflow-hidden">
            @foreach (['#0F172A', '#1E293B', '#334155', '#64748B', '#94A3B8', '#CBD5E1', '#E2E8F0', '#F1F5F9', '#F8FAFC'] as $i => $hex)
                <div class="flex-1 h-16 flex items-end justify-center pb-1.5" style="background-color: {{ $hex }}">
                    <span class="text-[8px] font-mono {{ $i < 4 ? 'text-white/50' : 'text-gray-400' }}">{{ $hex }}</span>
                </div>
            @endforeach
        </div>

        {{-- Функциональные --}}
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Функциональные</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $functional = [
                    ['bg' => 'bg-emerald-500', 'name' => 'Emerald 500', 'hex' => '#10B981', 'role' => 'Успех, оплачен, активен'],
                    ['bg' => 'bg-amber-500',   'name' => 'Amber 500',   'hex' => '#F59E0B', 'role' => 'Внимание, ожидание'],
                    ['bg' => 'bg-red-500',     'name' => 'Red 500',     'hex' => '#EF4444', 'role' => 'Ошибка, списание, удаление'],
                    ['bg' => 'bg-sky-500',     'name' => 'Sky 500',     'hex' => '#0EA5E9', 'role' => 'Информация, ссылки'],
                ];
            @endphp
            @foreach ($functional as $fc)
                <div>
                    <div class="h-14 rounded-xl {{ $fc['bg'] }} mb-2 flex items-end p-3">
                        <span class="text-[10px] font-mono text-white/70">{{ $fc['hex'] }}</span>
                    </div>
                    <p class="text-xs font-medium text-gray-700">{{ $fc['name'] }}</p>
                    <p class="text-[10px] text-gray-400">{{ $fc['role'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===== 4. Типографика ===== --}}
    <section class="mb-16">
        <span class="text-xs font-semibold text-indigo-600 uppercase tracking-widest">04</span>
        <h2 class="text-2xl font-bold text-gray-900 mt-1 mb-6">Типографика</h2>

        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="p-8 border-b border-gray-100">
                <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-3">Основной шрифт</p>
                <p class="text-5xl font-bold text-gray-900 tracking-tight">Inter</p>
                <p class="text-sm text-gray-400 mt-2">System UI fallback &middot; <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">font-sans</code></p>
            </div>

            <div class="p-8 border-b border-gray-100 space-y-5">
                <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-1">Иерархия</p>
                @php
                    $hierarchy = [
                        ['class' => 'text-3xl font-bold', 'label' => 'H1 — Заголовок страницы', 'spec' => '30px / Bold'],
                        ['class' => 'text-2xl font-bold', 'label' => 'H2 — Раздел', 'spec' => '24px / Bold'],
                        ['class' => 'text-lg font-semibold', 'label' => 'H3 — Карточка', 'spec' => '18px / Semibold'],
                        ['class' => 'text-sm font-medium', 'label' => 'Body — Основной текст', 'spec' => '14px / Medium'],
                        ['class' => 'text-xs text-gray-500', 'label' => 'Caption — Подпись', 'spec' => '12px / Regular'],
                    ];
                @endphp
                @foreach ($hierarchy as $h)
                    <div class="flex items-baseline justify-between gap-4">
                        <p class="{{ $h['class'] }} text-gray-900 truncate">{{ $h['label'] }}</p>
                        <span class="text-[10px] text-gray-300 font-mono whitespace-nowrap">{{ $h['spec'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="p-8">
                <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-3">Моноширинный</p>
                <p class="font-mono text-lg text-gray-700">0 1 2 3 4 5 6 7 8 9</p>
                <p class="text-xs text-gray-400 mt-2">Цены, артикулы, штрих-коды &middot; <code class="bg-gray-100 px-1.5 py-0.5 rounded text-[10px]">font-mono</code></p>
            </div>
        </div>
    </section>

    {{-- ===== 5. Правила ===== --}}
    <section class="mb-16">
        <span class="text-xs font-semibold text-indigo-600 uppercase tracking-widest">05</span>
        <h2 class="text-2xl font-bold text-gray-900 mt-1 mb-6">Правила использования</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Правильно --}}
            <div class="rounded-2xl border border-gray-200 overflow-hidden">
                <div class="bg-emerald-50 px-6 py-3 border-b border-emerald-100">
                    <h3 class="text-sm font-semibold text-emerald-800">Правильно</h3>
                </div>
                <div class="p-6 space-y-3 text-sm text-gray-600">
                    <p>Соблюдайте охранное поле (1× высота иконки)</p>
                    <p>Используйте только утверждённые цветовые схемы</p>
                    <p>На тёмном фоне — вариант <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">color="light"</code></p>
                    <p>Минимальный размер иконки: 24×24 px</p>
                    <p>Написание: <strong>FORRIS</strong> — всегда заглавными</p>
                    <p>«POS» отделяется пробелом, нормальная начертание</p>
                </div>
            </div>

            {{-- Неправильно --}}
            <div class="rounded-2xl border border-gray-200 overflow-hidden">
                <div class="bg-red-50 px-6 py-3 border-b border-red-100">
                    <h3 class="text-sm font-semibold text-red-800">Запрещено</h3>
                </div>
                <div class="p-6 space-y-3 text-sm text-gray-600">
                    <p>Менять пропорции, растягивать или сжимать</p>
                    <p>Добавлять тени, обводки, градиенты к знаку</p>
                    <p>Поворачивать или наклонять</p>
                    <p>Писать «Forris», «forris», «FORRIS pos»</p>
                    <p>Размещать на пёстром фоне без контрастной подложки</p>
                    <p>Использовать иконку без скруглённого контейнера</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== 6. Панели системы ===== --}}
    <section class="mb-16">
        <span class="text-xs font-semibold text-indigo-600 uppercase tracking-widest">06</span>
        <h2 class="text-2xl font-bold text-gray-900 mt-1 mb-6">Панели системы</h2>

        <p class="text-sm text-gray-500 mb-6">Каждый интерфейс имеет свой акцентный цвет сайдбара, но общий знак FORRIS.</p>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            @php
                $panels = [
                    ['name' => 'Кабинет',  'color' => 'bg-indigo-600', 'accent' => 'text-indigo-200', 'border' => 'border-indigo-500'],
                    ['name' => 'Менеджер', 'color' => 'bg-emerald-600', 'accent' => 'text-emerald-200', 'border' => 'border-emerald-500'],
                    ['name' => 'Кухня',    'color' => 'bg-gray-800',    'accent' => 'text-orange-300',  'border' => 'border-gray-700'],
                    ['name' => 'Склад',    'color' => 'bg-gray-900',    'accent' => 'text-amber-300',   'border' => 'border-gray-800'],
                    ['name' => 'Админ',    'color' => 'bg-red-900',     'accent' => 'text-red-200',     'border' => 'border-red-800'],
                ];
            @endphp
            @foreach ($panels as $panel)
                <div class="rounded-xl {{ $panel['color'] }} {{ $panel['border'] }} border p-5 text-center">
                    <x-logo variant="icon" color="light" size="sm" class="mx-auto" />
                    <p class="text-sm font-semibold text-white mt-3">{{ $panel['name'] }}</p>
                    <p class="text-[10px] {{ $panel['accent'] }} mt-0.5">{{ $panel['name'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===== 7. Экосистема ===== --}}
    <section class="mb-16">
        <span class="text-xs font-semibold text-indigo-600 uppercase tracking-widest">07</span>
        <h2 class="text-2xl font-bold text-gray-900 mt-1 mb-6">Экосистема FORRIS</h2>

        <p class="text-sm text-gray-500 mb-6">
            FORRIS POS — часть экосистемы FORRIS. Каждый продукт использует единую форму знака
            (F в скруглённом квадрате), но с собственным цветом контейнера.
        </p>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $ecosystem = [
                    ['name' => 'FORRIS POS',  'sub' => 'pos.forris.uz',  'bg' => '#1E1B4B', 'containerBg' => 'bg-indigo-50',  'containerBorder' => 'border-indigo-100', 'textColor' => 'text-indigo-700',  'subColor' => 'text-indigo-400'],
                    ['name' => 'FORRIS CRM',  'sub' => 'crm.forris.uz',  'bg' => '#065F46', 'containerBg' => 'bg-emerald-50', 'containerBorder' => 'border-emerald-100', 'textColor' => 'text-emerald-700', 'subColor' => 'text-emerald-400'],
                    ['name' => 'FORRIS ERP',  'sub' => 'erp.forris.uz',  'bg' => '#1E3A5F', 'containerBg' => 'bg-sky-50',     'containerBorder' => 'border-sky-100',     'textColor' => 'text-sky-700',     'subColor' => 'text-sky-400'],
                    ['name' => 'FORRIS HR',   'sub' => 'hr.forris.uz',   'bg' => '#4C1D95', 'containerBg' => 'bg-violet-50',  'containerBorder' => 'border-violet-100',  'textColor' => 'text-violet-700',  'subColor' => 'text-violet-400'],
                ];
            @endphp
            @foreach ($ecosystem as $product)
                <div class="text-center p-6 rounded-xl {{ $product['containerBg'] }} {{ $product['containerBorder'] }} border">
                    <svg class="w-12 h-12 mx-auto" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="40" height="40" rx="10" fill="{{ $product['bg'] }}"/>
                        <path d="M11 9H28.5V14H16.5V17.5H24V22H16.5V31H11V9Z" fill="white"/>
                    </svg>
                    <p class="text-sm font-bold {{ $product['textColor'] }} mt-3">{{ $product['name'] }}</p>
                    <p class="text-[10px] {{ $product['subColor'] }}">{{ $product['sub'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Футер --}}
    <div class="text-center text-xs text-gray-300 pt-8 border-t border-gray-100">
        &copy; {{ date('Y') }} FORRIS &middot; Все права защищены
    </div>
</div>
