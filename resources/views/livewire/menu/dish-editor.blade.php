<div>
    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ url('/cabinet/menu/items') }}" class="p-2 rounded-lg hover:bg-gray-100" title="Назад">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $productId ? 'Редактирование тех. карты' : 'Новая тех. карта' }}
            </h1>
        </div>
        @if ($productId)
            <a href="{{ url('/cabinet/menu/dishes/'.$productId.'/print') }}" target="_blank"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Распечатать
            </a>
        @endif
    </div>

    <form wire:submit="save" class="space-y-6">

        {{-- Basics card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-[160px_1fr] gap-4 items-start">
                <label class="text-sm text-gray-700 pt-2">Название</label>
                <div>
                    <input type="text" wire:model="name"
                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <label class="text-sm text-gray-700 pt-2">Категория</label>
                <select wire:model="categoryId" class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="">— не выбрано —</option>
                    @foreach ($this->categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>

                <label class="text-sm text-gray-700 pt-2">Цех приготовления</label>
                <div>
                    <select wire:model="workshopId" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">— не выбрано —</option>
                        @foreach ($this->workshops as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Выберите цех, чтобы печатать на него бегунки и правильно списывать ингредиенты с разных складов.</p>
                </div>

                <label class="text-sm text-gray-700 pt-2">Налог</label>
                <select wire:model="taxId" class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="">Налог родительской категории</option>
                    @foreach ($this->taxes as $tax)
                        <option value="{{ $tax->id }}">{{ $tax->name }} ({{ $tax->rate }}%)</option>
                    @endforeach
                </select>

                <label class="text-sm text-gray-700 pt-2">Обложка</label>
                <div class="flex items-center gap-3">
                    @if ($newImage)
                        <img src="{{ $newImage->temporaryUrl() }}" class="w-20 h-20 rounded-lg object-cover">
                    @elseif ($existingImage)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingImage) }}" class="w-20 h-20 rounded-lg object-cover">
                    @else
                        <div class="w-20 h-20 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                    <input type="file" wire:model="newImage" accept="image/*" class="text-sm">
                </div>

                <label class="text-sm text-gray-700 pt-2">Опции</label>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" wire:model="isWeighable" class="rounded text-indigo-600">
                        Весовая тех. карта
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" wire:model="excludedFromDiscounts" class="rounded text-indigo-600">
                        Не участвует в скидках
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" wire:model="isVisible" class="rounded text-indigo-600">
                        Видна в меню
                    </label>
                </div>

                <label class="text-sm text-gray-700 pt-2">Цена</label>
                <div class="flex items-center gap-4 flex-wrap">
                    <div class="flex items-center">
                        <input type="number" step="0.01" wire:model.live.debounce.300ms="price"
                               class="w-32 rounded-l-lg border-gray-300 text-sm">
                        <span class="rounded-r-lg border border-l-0 border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-600">СУМ</span>
                    </div>
                    <div class="text-sm text-gray-500">
                        Наценка до налога: <span class="font-semibold text-gray-800">{{ number_format($this->markupPercent, 2) }}%</span>
                    </div>
                    <div class="text-sm text-gray-500">
                        Себестоимость: <span class="font-semibold text-gray-800">{{ number_format($this->totalCost, 2) }} СУМ</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Composition --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Состав</h2>
                <p class="text-sm text-gray-500">Ингредиенты и полуфабрикаты, из которых состоит тех. карта.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-3 py-2 text-left">Продукт</th>
                            <th class="px-3 py-2 text-left w-44">Метод приготовления</th>
                            <th class="px-3 py-2 text-right w-32">Нетто</th>
                            <th class="px-3 py-2 text-right w-24">Потери %</th>
                            <th class="px-3 py-2 text-right w-32">Брутто</th>
                            <th class="px-3 py-2 text-right w-32">Себестоимость</th>
                            <th class="px-3 py-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($items as $i => $row)
                            @php
                                $qty = (float) ($row['quantity'] ?: 0);
                                $loss = (float) ($row['loss_percent'] ?: 0);
                                $gross = $qty * (1 + $loss / 100);
                            @endphp
                            <tr>
                                <td class="px-3 py-2">
                                    <div class="flex gap-2">
                                        <select wire:model.live="items.{{ $i }}.kind" class="rounded-lg border-gray-300 text-xs w-32">
                                            <option value="ingredient">Ингредиент</option>
                                            <option value="semi_finished">Полуфабрикат</option>
                                        </select>
                                        <select wire:model.live="items.{{ $i }}.ref_id" class="flex-1 rounded-lg border-gray-300 text-xs">
                                            <option value="">— выбрать —</option>
                                            @if (($row['kind'] ?? 'ingredient') === 'ingredient')
                                                @foreach ($this->ingredients as $ing)
                                                    <option value="{{ $ing->id }}">{{ $ing->name }}</option>
                                                @endforeach
                                            @else
                                                @foreach ($this->semiFinishedProducts as $sf)
                                                    <option value="{{ $sf->id }}">{{ $sf->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <select wire:model="items.{{ $i }}.preparation_method_id" class="w-full rounded-lg border-gray-300 text-xs">
                                        <option value="">—</option>
                                        @foreach ($this->preparationMethods as $pm)
                                            <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" step="0.0001" wire:model.live.debounce.300ms="items.{{ $i }}.quantity"
                                           class="w-full rounded-lg border-gray-300 text-xs text-right">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" wire:model.live.debounce.300ms="items.{{ $i }}.loss_percent"
                                           class="w-full rounded-lg border-gray-300 text-xs text-right">
                                </td>
                                <td class="px-3 py-2 text-right text-gray-600 font-mono">
                                    {{ number_format($gross, 4) }}
                                </td>
                                <td class="px-3 py-2 text-right text-gray-700 font-mono">
                                    {{ number_format($this->lineCost($i), 2) }} СУМ
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <button type="button" wire:click="removeItem({{ $i }})" class="text-red-600 hover:text-red-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a2 2 0 012-2h2a2 2 0 012 2v3"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center px-3 py-6 text-gray-400">Состав пуст</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="5" class="px-3 py-2 text-right text-sm text-gray-700">Итого:</td>
                            <td class="px-3 py-2 text-right font-semibold text-gray-900 font-mono">{{ number_format($this->totalCost, 2) }} СУМ</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-4 flex items-center justify-between flex-wrap gap-3">
                <button type="button" wire:click="addItem" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    + Добавить ингредиент
                </button>
                <div class="flex items-center gap-2 text-sm text-gray-700">
                    <span>Выход:</span>
                    <input type="number" step="0.001" wire:model="outputQuantity" class="w-24 rounded-lg border-gray-300 text-sm text-right">
                    <select wire:model="outputUnitId" class="rounded-lg border-gray-300 text-sm">
                        <option value="">—</option>
                        @foreach ($this->units as $u)
                            <option value="{{ $u->id }}">{{ $u->short_name ?? $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Modifiers --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Модификаторы</h2>
                <p class="text-sm text-gray-500">Выбор среди разновидностей или возможность добавить дополнительные ингредиенты.</p>
            </div>

            @forelse ($modifierGroups as $i => $mg)
                @php $group = $this->attachedModifierGroups[$mg['group_id']] ?? null; @endphp
                @if ($group)
                    <div class="mb-3 rounded-lg border border-gray-200 px-4 py-3 flex items-center justify-between">
                        <div>
                            <div class="font-medium text-gray-900">{{ $group->name }}</div>
                            <div class="text-xs text-gray-500">{{ $group->modifiers->count() }} вариантов</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-1.5 text-sm text-gray-700">
                                <input type="checkbox" wire:model="modifierGroups.{{ $i }}.is_required" class="rounded text-indigo-600">
                                Обязательный
                            </label>
                            <button type="button" wire:click="removeModifierGroup({{ $i }})" class="text-red-600 text-sm">Убрать</button>
                        </div>
                    </div>
                @endif
            @empty
                <p class="text-sm text-gray-400 mb-3">Нет привязанных групп.</p>
            @endforelse

            @if ($this->modifierGroupOptions->isNotEmpty())
                <div class="flex items-center gap-2">
                    <select id="add-mg" class="rounded-lg border-gray-300 text-sm">
                        @foreach ($this->modifierGroupOptions as $opt)
                            <option value="{{ $opt->id }}">{{ $opt->name }}</option>
                        @endforeach
                    </select>
                    <button type="button"
                            x-on:click="$wire.addModifierGroup(parseInt(document.getElementById('add-mg').value))"
                            class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                        + Добавить набор модификаторов
                    </button>
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ url('/cabinet/menu/items') }}" class="rounded-lg px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Отмена</a>
            <button type="submit" class="rounded-lg bg-green-600 px-6 py-2 text-sm font-medium text-white hover:bg-green-700">
                Сохранить
            </button>
        </div>
    </form>
</div>
