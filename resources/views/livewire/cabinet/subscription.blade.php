<div>
    {{-- Заголовок --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Подписка</h1>
        <p class="text-sm text-gray-500 mt-1">Управление тарифным планом</p>
    </div>

    {{-- Текущая подписка --}}
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Текущий план</h2>
        @if ($this->currentSubscription)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-xl">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">{{ $this->currentSubscription->plan?->name ?? 'Без плана' }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $this->currentSubscription->plan?->description ?? '' }}</p>
                    </div>
                    @switch($this->currentSubscription->status)
                        @case('active')
                            <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800">Активна</span>
                            @break
                        @case('trial')
                            <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800">Триал</span>
                            @break
                        @case('cancelled')
                            <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-800">Отменена</span>
                            @break
                        @case('expired')
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-800">Истекла</span>
                            @break
                        @default
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-800">{{ $this->currentSubscription->status }}</span>
                    @endswitch
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Цена</p>
                        <p class="font-medium text-gray-900">{{ number_format($this->currentSubscription->plan?->price ?? 0, 0, '.', ' ') }} сум/мес</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Действует до</p>
                        <p class="font-medium text-gray-900">{{ $this->currentSubscription->ends_at?->format('d.m.Y') ?? '---' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Начало</p>
                        <p class="font-medium text-gray-900">{{ $this->currentSubscription->starts_at?->format('d.m.Y') ?? $this->currentSubscription->created_at?->format('d.m.Y') }}</p>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 max-w-xl">
                <div class="flex">
                    <svg class="h-6 w-6 text-yellow-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-yellow-800">Нет активной подписки</h3>
                        <p class="text-sm text-yellow-700 mt-1">Выберите тарифный план ниже, чтобы начать работу.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Доступные планы --}}
    <div>
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Доступные планы</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($this->availablePlans as $plan)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col {{ $this->currentSubscription?->plan_id === $plan->id ? 'ring-2 ring-indigo-500' : '' }}">
                    @if ($this->currentSubscription?->plan_id === $plan->id)
                        <div class="mb-3">
                            <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">Текущий план</span>
                        </div>
                    @endif
                    <h3 class="text-lg font-bold text-gray-900">{{ $plan->name }}</h3>
                    <p class="text-sm text-gray-500 mt-1 flex-1">{{ $plan->description ?? '' }}</p>
                    <div class="mt-4">
                        <p class="text-3xl font-bold text-gray-900">
                            {{ number_format($plan->price, 0, '.', ' ') }}
                            <span class="text-base font-normal text-gray-500">сум/мес</span>
                        </p>
                    </div>
                    <div class="mt-6">
                        @if ($this->currentSubscription?->plan_id === $plan->id)
                            <button disabled
                                    class="w-full inline-flex justify-center items-center rounded-lg bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-400 cursor-not-allowed">
                                Текущий план
                            </button>
                        @else
                            <a href="#"
                               class="w-full inline-flex justify-center items-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
                                Выбрать план
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-8 text-gray-500">Нет доступных планов</div>
            @endforelse
        </div>
    </div>
</div>
