<?php

declare(strict_types=1);

namespace App\Livewire\Orders;

use App\Domain\Order\Models\Order;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\PaymentStatus;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class OrderList extends Component
{
    use WithPagination;

    #[Url]
    public string $filterStatus = '';

    #[Url]
    public string $filterDate = '';

    #[Url]
    public string $searchQuery = '';

    public bool $showDetailModal = false;
    public ?Order $selectedOrder = null;

    public function mount(): void
    {
        $this->filterDate = '';
        $this->filterStatus = '';
        $this->searchQuery = '';
    }

    public function updatedSearchQuery(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterDate(): void
    {
        $this->resetPage();
    }

    public function getOrders()
    {
        $query = Order::query()
            ->with(['table', 'waiter', 'items'])
            ->latest();

        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterDate !== '') {
            $query->whereDate('created_at', $this->filterDate);
        }

        if ($this->searchQuery !== '') {
            $query->where(function ($q) {
                $q->where('order_number', 'like', '%' . $this->searchQuery . '%')
                    ->orWhereHas('waiter', function ($wq) {
                        $wq->where('name', 'like', '%' . $this->searchQuery . '%');
                    });
            });
        }

        return $query->paginate(15);
    }

    public function viewOrder(int $id): void
    {
        $this->selectedOrder = Order::with(['table', 'waiter', 'items.menuItem'])->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedOrder = null;
    }

    public function cancelOrder(int $id): void
    {
        $order = Order::findOrFail($id);

        if ($order->status === OrderStatus::Completed->value || $order->status === OrderStatus::Cancelled->value) {
            session()->flash('error', 'Невозможно отменить этот заказ.');
            return;
        }

        $order->update([
            'status' => OrderStatus::Cancelled->value,
        ]);

        session()->flash('success', 'Заказ успешно отменён.');
    }

    public function render()
    {
        return view('livewire.orders.order-list', [
            'orders' => $this->getOrders(),
            'statuses' => OrderStatus::cases(),
        ]);
    }
}
