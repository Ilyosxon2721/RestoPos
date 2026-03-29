<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Настройки платформы</h1>
        <p class="text-sm text-gray-500">Общие настройки системы</p>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border p-6 max-w-2xl">
        <form wire:submit="save" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Название платформы</label>
                <input type="text" wire:model="siteName" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                @error('siteName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email поддержки</label>
                <input type="email" wire:model="supportEmail" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                @error('supportEmail') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Пробный период (дней)</label>
                <input type="number" wire:model="trialDays" min="0" max="90" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                @error('trialDays') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center">
                <input type="checkbox" wire:model="registrationEnabled" id="regEnabled" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                <label for="regEnabled" class="ml-2 text-sm text-gray-700">Разрешить регистрацию новых организаций</label>
            </div>

            <div class="pt-4 border-t">
                <button type="submit" class="px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Сохранить настройки
                </button>
            </div>
        </form>
    </div>
</div>
