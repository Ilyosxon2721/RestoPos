<?php

declare(strict_types=1);

namespace App\Livewire\Kitchen;

use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\OrderItem;
use App\Support\Enums\OrderItemStatus;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.kitchen')]
final class Display extends Component
{
    public string $filter = 'all';

    public function mount(): void
    {
        // Начальная загрузка происходит через computed property
    }

    #[Computed]
    public function orders(): Collection
    {
        return $this->getOrders();
    }

    #[Computed]
    public function stats(): array
    {
        return $this->getStats();
    }

    public function getOrders(): Collection
    {
        $query = OrderItem::query()
            ->whereIn('status', [
                OrderItemStatus::Sent,
                OrderItemStatus::Preparing,
                OrderItemStatus::Ready,
            ])
            ->with(['order.table', 'product']);

        if ($this->filter !== 'all') {
            $statusMap = [
                'pending' => OrderItemStatus::Sent,
                'preparing' => OrderItemStatus::Preparing,
                'ready' => OrderItemStatus::Ready,
            ];

            if (isset($statusMap[$this->filter])) {
                $query->where('status', $statusMap[$this->filter]);
            }
        }

        $items = $query->orderBy('created_at', 'asc')->get();

        return $items->groupBy('order_id')->map(function (Collection $items) {
            $order = $items->first()->order;

            return (object) [
                'id' => $order->id,
                'order_number' => $order->order_number ?? $order->id,
                'table_number' => $order->table?->number ?? '—',
                'created_at' => $order->created_at,
                'items' => $items,
            ];
        })->values();
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        unset($this->orders, $this->stats);
    }

    public function startPreparing(int $itemId): void
    {
        $item = OrderItem::findOrFail($itemId);
        $item->update([
            'status' => OrderItemStatus::Preparing,
            'preparing_at' => now(),
        ]);

        unset($this->orders, $this->stats);
    }

    public function markReady(int $itemId): void
    {
        $item = OrderItem::findOrFail($itemId);
        $item->update([
            'status' => OrderItemStatus::Ready,
            'ready_at' => now(),
        ]);

        unset($this->orders, $this->stats);
    }

    public function markServed(int $itemId): void
    {
        $item = OrderItem::findOrFail($itemId);
        $item->update([
            'status' => OrderItemStatus::Served,
            'served_at' => now(),
        ]);

        unset($this->orders, $this->stats);
    }

    public function getStats(): array
    {
        $items = OrderItem::query()
            ->whereIn('status', [
                OrderItemStatus::Sent,
                OrderItemStatus::Preparing,
                OrderItemStatus::Ready,
            ])
            ->get();

        $avgPrepTime = $items
            ->filter(fn (OrderItem $item) => $item->preparing_at && $item->ready_at)
            ->avg(fn (OrderItem $item) => $item->ready_at->diffInMinutes($item->preparing_at));

        return [
            'total' => $items->count(),
            'sent' => $items->where('status', OrderItemStatus::Sent)->count(),
            'preparing' => $items->where('status', OrderItemStatus::Preparing)->count(),
            'ready' => $items->where('status', OrderItemStatus::Ready)->count(),
            'avg_prep_time' => round($avgPrepTime ?? 0),
        ];
    }

    public function render()
    {
        return view('livewire.kitchen.display');
    }
}
