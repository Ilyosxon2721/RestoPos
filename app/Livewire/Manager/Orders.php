<?php

declare(strict_types=1);

namespace App\Livewire\Manager;

use App\Domain\Order\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.manager')]
class Orders extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $branchId = session('current_branch_id');
        $orders = Order::where('branch_id', $branchId)
            ->when($this->statusFilter, fn ($q, $s) => $q->where('status', $s))
            ->when($this->search, fn ($q, $s) => $q->where('order_number', 'like', "%{$s}%"))
            ->latest()
            ->paginate(20);

        return view('livewire.manager.orders', compact('orders'));
    }
}
