<div class="py-6">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Заголовок --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Импорт меню из Poster (CSV)</h1>
            <p class="mt-1 text-sm text-gray-600">
                Загружайте файлы по очереди: <strong>1) Ингредиенты</strong> → <strong>2) Товары</strong> → <strong>3) Тех. карты</strong>.
                Порядок важен: тех-карты ссылаются на ингредиенты и блюда.
            </p>
        </div>

        {{-- Вкладки --}}
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-6">
                @foreach ([
                    'ingredients' => '1. Ингредиенты',
                    'products'    => '2. Товары',
                    'tech-cards'  => '3. Тех. карты',
                ] as $key => $label)
                    <button
                        type="button"
                        wire:click="selectTab('{{ $key }}')"
                        class="whitespace-nowrap py-3 px-1 border-b-2 text-sm font-medium
                            {{ $tab === $key
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- Форма загрузки --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">
                    @switch($tab)
                        @case('ingredients') Загрузка ингредиентов @break
                        @case('products')    Загрузка товаров @break
                        @case('tech-cards')  Загрузка тех. карт @break
                    @endswitch
                </h2>

                <button
                    type="button"
                    wire:click="downloadSample('{{ $tab }}')"
                    class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Скачать образец CSV
                </button>
            </div>

            {{-- Подсказка по колонкам --}}
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded text-xs text-blue-900">
                <strong>Поддерживаемые колонки</strong> (на русском или английском, в любом порядке):<br>
                @switch($tab)
                    @case('ingredients')
                        <code>Название</code>, <code>Артикул</code>, <code>Категория</code>, <code>Ед. изм.</code>,
                        <code>Себестоимость</code>, <code>Минимальный остаток</code>, <code>Срок хранения</code>, <code>% потерь</code>
                        @break
                    @case('products')
                        <code>Название</code>, <code>Артикул</code>, <code>Категория</code>, <code>Цех</code>, <code>Тип</code>
                        (Блюдо/Напиток/Заготовка/Товар/Услуга), <code>Ед. изм.</code>, <code>Цена</code>,
                        <code>Себестоимость</code>, <code>Вес</code>, <code>Калории</code>, <code>Штрихкод</code>, <code>Скрыто</code>
                        @break
                    @case('tech-cards')
                        <code>Артикул блюда</code> или <code>Название блюда</code>, <code>Артикул ингредиента</code> или
                        <code>Название ингредиента</code>, <code>Кол-во</code> (или <code>Брутто</code>), <code>% потерь</code>,
                        <code>Выход</code>. Одна строка на пару (блюдо, ингредиент).
                        @break
                @endswitch
            </div>

            <form wire:submit.prevent="process">
                {{-- Файл --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">CSV-файл</label>
                    <input
                        type="file"
                        wire:model="file"
                        accept=".csv,text/csv"
                        class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                    >
                    @error('file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                    <div wire:loading wire:target="file" class="mt-1 text-xs text-gray-500">Загрузка файла…</div>
                </div>

                @if ($tab === 'products')
                    {{-- Филиал (для создания цехов) --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Филиал (для авто-создания цехов)</label>
                        <select
                            wire:model="branchId"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                        >
                            <option value="">— не создавать цеха —</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Dry-run --}}
                <div class="mb-6 flex items-center">
                    <input
                        type="checkbox"
                        wire:model="dryRun"
                        id="dryRun"
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                    <label for="dryRun" class="ml-2 text-sm text-gray-700">
                        Тестовый прогон (dry-run) — без записи в БД
                    </label>
                </div>

                {{-- Кнопка --}}
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="process"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50"
                >
                    <svg wire:loading wire:target="process" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="process">Запустить импорт</span>
                    <span wire:loading wire:target="process">Обработка…</span>
                </button>
            </form>
        </div>

        {{-- Ошибка целиком --}}
        @if ($errorMessage)
            <div class="mt-6 bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-800">
                <strong>Ошибка:</strong> {{ $errorMessage }}
            </div>
        @endif

        {{-- Результат --}}
        @if ($result)
            <div class="mt-6 bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Результат</h3>
                    @if ($dryRun)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            DRY-RUN (изменения не сохранены)
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Сохранено
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-4">
                    <div class="bg-green-50 rounded p-3 text-center">
                        <div class="text-2xl font-bold text-green-700">{{ $result['created'] }}</div>
                        <div class="text-xs text-gray-600">Создано</div>
                    </div>
                    <div class="bg-blue-50 rounded p-3 text-center">
                        <div class="text-2xl font-bold text-blue-700">{{ $result['updated'] }}</div>
                        <div class="text-xs text-gray-600">Обновлено</div>
                    </div>
                    <div class="bg-gray-50 rounded p-3 text-center">
                        <div class="text-2xl font-bold text-gray-700">{{ $result['skipped'] }}</div>
                        <div class="text-xs text-gray-600">Пропущено</div>
                    </div>
                    <div class="bg-indigo-50 rounded p-3 text-center">
                        <div class="text-2xl font-bold text-indigo-700">{{ $result['total'] }}</div>
                        <div class="text-xs text-gray-600">Всего</div>
                    </div>
                    <div class="bg-red-50 rounded p-3 text-center">
                        <div class="text-2xl font-bold text-red-700">{{ $result['errorCnt'] }}</div>
                        <div class="text-xs text-gray-600">Ошибок</div>
                    </div>
                </div>

                @if ($result['errorCnt'] > 0)
                    <details class="mt-4">
                        <summary class="cursor-pointer text-sm font-medium text-red-700 hover:text-red-900">
                            Показать ошибки ({{ $result['errorCnt'] }})
                        </summary>
                        <div class="mt-2 max-h-64 overflow-y-auto border border-red-200 rounded p-2 bg-red-50">
                            <ul class="space-y-1 text-xs text-red-900 font-mono">
                                @foreach ($result['errors'] as $err)
                                    <li>строка {{ $err['line'] }}: {{ $err['message'] }}</li>
                                @endforeach
                                @if ($result['errorCnt'] > count($result['errors']))
                                    <li class="text-red-600">… и ещё {{ $result['errorCnt'] - count($result['errors']) }}</li>
                                @endif
                            </ul>
                        </div>
                    </details>
                @endif
            </div>
        @endif
    </div>
</div>
