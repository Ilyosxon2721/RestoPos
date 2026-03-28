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
                OrderItemStatus::SENT,
                OrderItemStatus::PREPARING,
                OrderItemStatus::READY,
            ])
            ->with(['order.table', 'product']);

        if ($this->filter !== 'all') {
            $statusMap = [
                'pending' => OrderItemStatus::SENT,
                'preparing' => OrderItemStatus::PREPARING,
                'ready' => OrderItemStatus::READY,
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
            'status' => OrderItemStatus::PREPARING,
        ]);

        unset($this->orders, $this->stats);
    }

    public function markReady(int $itemId): void
    {
        $item = OrderItem::findOrFail($itemId);
        $item->update([
            'status' => OrderItemStatus::READY,
            'ready_at' => now(),
        ]);

        unset($this->orders, $this->stats);
    }

    public function markServed(int $itemId): void
    {
        $item = OrderItem::findOrFail($itemId);
        $item->update([
            'status' => OrderItemStatus::SERVED,
        ]);

        unset($this->orders, $this->stats);
    }

    public function getStats(): array
    {
        $items = OrderItem::query()
            ->whereIn('status', [
                OrderItemStatus::SENT,
                OrderItemStatus::PREPARING,
                OrderItemStatus::READY,
            ])
            ->get();

        $avgPrepTime = $items
            ->filter(fn (OrderItem $item) => $item->sent_to_kitchen_at && $item->ready_at)
            ->avg(fn (OrderItem $item) => $item->ready_at->diffInMinutes($item->sent_to_kitchen_at));

        return [
            'total' => $items->count(),
            'sent' => $items->where('status', OrderItemStatus::SENT)->count(),
            'preparing' => $items->where('status', OrderItemStatus::PREPARING)->count(),
            'ready' => $items->where('status', OrderItemStatus::READY)->count(),
            'avg_prep_time' => round($avgPrepTime ?? 0),
        ];
    }

    public function render()
    {
        return view('livewire.kitchen.display');
    }
}
