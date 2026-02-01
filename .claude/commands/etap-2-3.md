# Этап 2.3: Kitchen Display System (KDS)

Создай модуль кухонного дисплея для поваров.

## Задачи:

### 1. Livewire: KitchenDisplay

Создай `app/Livewire/Kitchen/Display.php`:

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Kitchen;

use App\Models\OrderItem;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.kitchen')]
final class Display extends Component
{
    public string $filter = 'pending'; // pending, preparing, ready

    #[Computed]
    public function orderItems()
    {
        return OrderItem::query()
            ->with(['order.table', 'menuItem'])
            ->whereHas('order', fn($q) => $q->whereNotIn('status', ['paid', 'cancelled']))
            ->when($this->filter === 'pending', fn($q) => $q->where('status', 'pending'))
            ->when($this->filter === 'preparing', fn($q) => $q->where('status', 'preparing'))
            ->when($this->filter === 'ready', fn($q) => $q->where('status', 'ready'))
            ->orderBy('created_at')
            ->get()
            ->groupBy('order_id');
    }

    #[Computed]
    public function stats()
    {
        return [
            'pending' => OrderItem::whereHas('order', fn($q) => $q->active())->where('status', 'pending')->count(),
            'preparing' => OrderItem::whereHas('order', fn($q) => $q->active())->where('status', 'preparing')->count(),
            'ready' => OrderItem::whereHas('order', fn($q) => $q->active())->where('status', 'ready')->count(),
        ];
    }

    public function startPreparing(int $itemId): void
    {
        $item = OrderItem::findOrFail($itemId);
        $item->update([
            'status' => 'preparing',
            'cook_id' => auth()->id(),
            'sent_to_kitchen_at' => now(),
        ]);
    }

    public function markReady(int $itemId): void
    {
        $item = OrderItem::findOrFail($itemId);
        $item->update([
            'status' => 'ready',
            'ready_at' => now(),
        ]);

        // Проверяем все ли позиции заказа готовы
        $order = $item->order;
        $allReady = $order->items()->where('status', '!=', 'ready')->doesntExist();
        
        if ($allReady) {
            $order->update(['status' => 'ready', 'ready_at' => now()]);
        }
    }

    public function markServed(int $itemId): void
    {
        OrderItem::findOrFail($itemId)->update(['status' => 'served']);
    }

    public function render(): View
    {
        return view('livewire.kitchen.display');
    }
}
```

### 2. View: Kitchen Display

Создай `resources/views/livewire/kitchen/display.blade.php`:

```blade
<div class="min-h-screen bg-gray-900 text-white p-4" wire:poll.5s>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">🍳 Кухня</h1>
        <div class="flex items-center gap-4">
            <div class="flex gap-2">
                <button 
                    wire:click="$set('filter', 'pending')"
                    class="px-4 py-2 rounded-lg {{ $filter === 'pending' ? 'bg-yellow-500' : 'bg-gray-700' }}"
                >
                    Ожидают ({{ $this->stats['pending'] }})
                </button>
                <button 
                    wire:click="$set('filter', 'preparing')"
                    class="px-4 py-2 rounded-lg {{ $filter === 'preparing' ? 'bg-blue-500' : 'bg-gray-700' }}"
                >
                    Готовятся ({{ $this->stats['preparing'] }})
                </button>
                <button 
                    wire:click="$set('filter', 'ready')"
                    class="px-4 py-2 rounded-lg {{ $filter === 'ready' ? 'bg-green-500' : 'bg-gray-700' }}"
                >
                    Готовы ({{ $this->stats['ready'] }})
                </button>
            </div>
            <span class="text-gray-400">{{ now()->format('H:i') }}</span>
        </div>
    </div>

    <!-- Orders Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($this->orderItems as $orderId => $items)
            @php $order = $items->first()->order; @endphp
            <div class="bg-gray-800 rounded-xl overflow-hidden">
                <!-- Order Header -->
                <div class="bg-gray-700 p-3 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-lg">#{{ $order->order_number }}</span>
                        @if($order->table)
                            <span class="ml-2 text-gray-400">{{ $order->table->name }}</span>
                        @endif
                    </div>
                    <div class="text-sm text-gray-400">
                        {{ $order->created_at->diffForHumans(short: true) }}
                    </div>
                </div>

                <!-- Items -->
                <div class="p-3 space-y-2">
                    @foreach($items as $item)
                        <div class="flex items-center justify-between p-2 bg-gray-700/50 rounded-lg">
                            <div class="flex-1">
                                <div class="font-medium">
                                    <span class="text-yellow-400">{{ (int) $item->quantity }}×</span>
                                    {{ $item->name }}
                                </div>
                                @if($item->notes)
                                    <div class="text-sm text-orange-400">📝 {{ $item->notes }}</div>
                                @endif
                            </div>
                            <div class="flex gap-1">
                                @if($item->status === 'pending')
                                    <button 
                                        wire:click="startPreparing({{ $item->id }})"
                                        class="p-2 bg-blue-600 rounded-lg hover:bg-blue-700"
                                        title="Начать готовить"
                                    >
                                        🍳
                                    </button>
                                @elseif($item->status === 'preparing')
                                    <button 
                                        wire:click="markReady({{ $item->id }})"
                                        class="p-2 bg-green-600 rounded-lg hover:bg-green-700"
                                        title="Готово"
                                    >
                                        ✅
                                    </button>
                                @elseif($item->status === 'ready')
                                    <button 
                                        wire:click="markServed({{ $item->id }})"
                                        class="p-2 bg-purple-600 rounded-lg hover:bg-purple-700"
                                        title="Подано"
                                    >
                                        🍽️
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($order->notes)
                    <div class="px-3 pb-3">
                        <div class="p-2 bg-yellow-500/20 rounded-lg text-yellow-200 text-sm">
                            📋 {{ $order->notes }}
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="col-span-full text-center py-20 text-gray-500">
                <div class="text-6xl mb-4">👨‍🍳</div>
                <p class="text-xl">Нет заказов в работе</p>
            </div>
        @endforelse
    </div>
</div>
```

### 3. Layout: Kitchen

Создай `resources/views/components/layouts/kitchen.blade.php`:

```blade
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Кухня - RestoPOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        /* Prevent sleep on tablets */
        body { -webkit-touch-callout: none; user-select: none; }
    </style>
</head>
<body class="antialiased">
    {{ $slot }}
    @livewireScripts
</body>
</html>
```

### 4. Route

Добавь в `routes/web.php`:

```php
Route::middleware('auth')->group(function () {
    // Kitchen Display
    Route::get('/kitchen', \App\Livewire\Kitchen\Display::class)->name('kitchen');
});
```

### 5. Kitchen Sound Notifications (опционально)

Добавь в `resources/js/app.js`:

```javascript
// Kitchen notification sound
window.playKitchenAlert = function() {
    const audio = new Audio('/sounds/kitchen-bell.mp3');
    audio.play();
}
```

## Проверка

Открой `http://127.0.0.1:8001/kitchen`

Этап 2.3 завершён!
