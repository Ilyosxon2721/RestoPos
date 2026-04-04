<div class="max-w-2xl mx-auto px-4 py-6 pb-24 md:pb-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Мои заказы</h1>

    @forelse($this->orders as $order)
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm mb-3">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <span class="text-sm font-medium text-gray-900">Заказ #{{ $order->order_number }}</span>
                    <span class="text-xs text-gray-500 ml-2">{{ $order->created_at->format('d.m.Y H:i') }}</span>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ match($order->status->value ?? $order->status) {
                        'new' => 'bg-blue-100 text-blue-800',
                        'accepted' => 'bg-yellow-100 text-yellow-800',
                        'preparing' => 'bg-orange-100 text-orange-800',
                        'ready' => 'bg-green-100 text-green-800',
                        'completed' => 'bg-gray-100 text-gray-600',
                        'cancelled' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-600',
                    } }}">
                    {{ match($order->status->value ?? $order->status) {
                        'new' => 'Новый',
                        'accepted' => 'Принят',
                        'preparing' => 'Готовится',
                        'ready' => 'Готов',
                        'served' => 'Выдан',
                        'completed' => 'Завершён',
                        'cancelled' => 'Отменён',
                        default => $order->status->value ?? $order->status,
                    } }}
                </span>
            </div>

            {{-- Позиции --}}
            <div class="space-y-1.5 mb-3">
                @foreach($order->items as $item)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-700">{{ $item->name }} x{{ (int)$item->quantity }}</span>
                        <span class="text-gray-900 font-medium" x-text="formatPrice({{ $item->total_price }})"></span>
                    </div>
                @endforeach
            </div>

            {{-- Тип и итого --}}
            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                <span class="text-xs text-gray-500">
                    {{ match($order->type->value ?? $order->type) {
                        'delivery' => 'Доставка',
                        'takeaway' => 'Самовывоз',
                        'dine_in' => 'В зале',
                        default => $order->type->value ?? $order->type,
                    } }}
                    @if($order->service_charge > 0)
                        (доставка: <span x-text="formatPrice({{ $order->service_charge }})"></span>)
                    @endif
                </span>
                <span class="text-base font-bold text-gray-900" x-text="formatPrice({{ $order->total_amount }})"></span>
            </div>

            {{-- Оплата --}}
            <div class="mt-2">
                <span class="inline-flex items-center text-xs {{ ($order->payment_status->value ?? $order->payment_status) === 'paid' ? 'text-green-600' : 'text-orange-600' }}">
                    {{ match($order->payment_status->value ?? $order->payment_status) {
                        'unpaid' => 'Не оплачен',
                        'partial' => 'Частично оплачен',
                        'paid' => 'Оплачен',
                        'refunded' => 'Возврат',
                        default => $order->payment_status->value ?? $order->payment_status,
                    } }}
                </span>
            </div>
        </div>
    @empty
        <div class="text-center py-16">
            <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Нет заказов</h2>
            <p class="text-gray-500 mb-6">Ваши заказы появятся здесь</p>
            <a href="{{ route('shop.home', ['slug' => $store->slug]) }}" class="inline-flex items-center px-6 py-2.5 bg-primary text-white font-medium rounded-xl hover:opacity-90 transition">
                Перейти в каталог
            </a>
        </div>
    @endforelse

    @if($this->orders->hasPages())
        <div class="mt-4">{{ $this->orders->links() }}</div>
    @endif
</div>
