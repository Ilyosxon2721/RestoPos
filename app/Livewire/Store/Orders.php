<?php

declare(strict_types=1);

namespace App\Livewire\Store;

use App\Domain\Order\Models\Order;
use App\Domain\Store\Models\StoreSettings;
use Livewire\Component;
use Livewire\WithPagination;

final class Orders extends Component
{
    use WithPagination;

    public StoreSettings $store;

    public function mount(string $slug): void
    {
        $this->store = StoreSettings::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function getOrdersProperty()
    {
        return Order::query()
            ->withoutGlobalScope('branch')
            ->where('customer_id', auth('customer')->id())
            ->with(['items', 'payments'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.store.orders')
            ->layout('components.layouts.store', ['store' => $this->store]);
    }
}
