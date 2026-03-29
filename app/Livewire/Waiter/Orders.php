<?php

declare(strict_types=1);

namespace App\Livewire\Waiter;

use App\Domain\Order\Models\Order;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.waiter')]
class Orders extends Component
{
    public string $statusFilter = 'active';

    public function render()
    {
        $branchId = session('current_branch_id');
        $orders = Order::where('branch_id', $branchId)
            ->when($this->statusFilter === 'active', fn($q) => $q->whereNotIn('status', ['completed', 'cancelled']))
            ->when($this->statusFilter === 'completed', fn($q) => $q->where('status', 'completed'))
            ->latest()
            ->take(50)
            ->get();

        return view('livewire.waiter.orders', compact('orders'));
    }
}
