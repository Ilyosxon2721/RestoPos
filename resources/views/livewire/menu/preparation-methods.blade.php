<div>
    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Методы приготовления</h1>
            <p class="text-sm text-gray-500 mt-1">Жарка, варка, запекание и т.д. с процентами потерь по умолчанию.</p>
        </div>
        <button wire:click="create"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Добавить метод
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Название</th>
                    <th class="px-4 py-3 text-right w-40">Потери % по умолчанию</th>
                    <th class="px-4 py-3 text-left w-24">Статус</th>
                    <th class="px-4 py-3 w-32"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($this->methods as $pm)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $pm->name }}</td>
                        <td class="px-4 py-3 text-right font-mono text-gray-700">{{ number_format((float) $pm->default_loss_percent, 2) }}%</td>
                        <td class="px-4 py-3">
                            @if ($pm->is_active)
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700">Активен</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">Откл.</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="edit({{ $pm->id }})" class="text-sm text-indigo-600 hover:text-indigo-800 mr-3">Изменить</button>
                            <button wire:click="delete({{ $pm->id }})" wire:confirm="Удалить метод '{{ $pm->name }}'?"
                                    class="text-sm text-red-600 hover:text-red-800">Удалить</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Методов нет</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ $editingId ? 'Редактировать метод' : 'Новый метод' }}
                </h2>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Название</label>
                        <input type="text" wire:model="name" class="w-full rounded-lg border-gray-300 text-sm">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Потери % по умолчанию</label>
                        <input type="number" step="0.01" wire:model="defaultLossPercent" class="w-full rounded-lg border-gray-300 text-sm">
                        @error('defaultLossPercent') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" wire:model="isActive" class="rounded text-indigo-600">
                        Активен
                    </label>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)"
                                class="rounded-lg px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Отмена</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700">
                            Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
