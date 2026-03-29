<div>
    {{-- Заголовок --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">План зала</h1>
        <p class="mt-1 text-sm text-gray-500">Управление столами и залами</p>
    </div>

    {{-- Выбор зала --}}
    <div class="mb-6 flex flex-wrap gap-2">
        @foreach($halls as $hall)
            <button
                wire:click="selectHall({{ $hall->id }})"
                @class([
                    'rounded-full px-5 py-2 text-sm font-medium transition-colors',
                    'bg-emerald-600 text-white shadow-sm' => $selectedHall == $hall->id,
                    'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' => $selectedHall != $hall->id,
                ])
            >
                {{ $hall->name }}
            </button>
        @endforeach
    </div>

    {{-- Сетка столов --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @forelse($tables as $table)
            @php
                $cardStyles = match($table->status) {
                    'free' => 'border-green-300 bg-green-50',
                    'occupied' => 'border-red-300 bg-red-50',
                    'reserved' => 'border-yellow-300 bg-yellow-50',
                    default => 'border-gray-300 bg-gray-50',
                };
                $dotColor = match($table->status) {
                    'free' => 'bg-green-500',
                    'occupied' => 'bg-red-500',
                    'reserved' => 'bg-yellow-500',
                    default => 'bg-gray-500',
                };
                $statusLabel = match($table->status) {
                    'free' => 'Свободен',
                    'occupied' => 'Занят',
                    'reserved' => 'Забронирован',
                    default => $table->status,
                };
                $textColor = match($table->status) {
                    'free' => 'text-green-700',
                    'occupied' => 'text-red-700',
                    'reserved' => 'text-yellow-700',
                    default => 'text-gray-700',
                };
            @endphp
            <div class="rounded-xl border-2 {{ $cardStyles }} p-5 transition-shadow hover:shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-bold text-gray-900">Стол {{ $table->number }}</h3>
                    <span class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full {{ $dotColor }}"></span>
                        <span class="text-xs font-medium {{ $textColor }}">{{ $statusLabel }}</span>
                    </span>
                </div>
                <div class="flex items-center text-sm text-gray-600">
                    <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ $table->seats }} мест
                </div>
            </div>
        @empty
            <div class="col-span-4 rounded-xl bg-white border border-gray-200 px-6 py-12 text-center text-sm text-gray-500">
                В этом зале нет столов
            </div>
        @endforelse
    </div>
</div>
