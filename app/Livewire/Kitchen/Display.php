<?php

declare(strict_types=1);

namespace App\Livewire\Kitchen;

use App\Domain\Auth\Models\User;
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
    // PIN-авторизация оператора
    public bool $pinLocked = true;
    public string $pin = '';
    public ?int $operatorId = null;
    public ?string $operatorName = null;

    public string $filter = 'all';

    public function mount(): void
    {
        // Если оператор уже сохранён в сессии — восстановить
        $sessionOperator = session('kitchen_operator');
        if ($sessionOperator) {
            $this->operatorId = $sessionOperator['id'];
            $this->operatorName = $sessionOperator['name'];
            $this->pinLocked = false;
        }
    }

    // === PIN-авторизация оператора ===

    public function appendPin(string $digit): void
    {
        if (mb_strlen($this->pin) < 4) {
            $this->pin .= $digit;
        }

        if (mb_strlen($this->pin) === 4) {
            $this->verifyPin();
        }
    }

    public function clearPin(): void
    {
        $this->pin = '';
        $this->resetErrorBag('pin');
    }

    public function backspacePin(): void
    {
        $this->pin = mb_substr($this->pin, 0, -1);
    }

    public function verifyPin(): void
    {
        $branchId = session('current_branch_id');

        // Ищем пользователя по PIN в текущем филиале с ролью повара
        $user = User::where('pin_code', $this->pin)
            ->where('is_active', true)
            ->whereHas('employee', fn($q) => $q->where('branch_id', $branchId))
            ->whereHas('roles', fn($q) => $q->whereIn('slug', ['cook']))
            ->first();

        if (! $user) {
            $this->addError('pin', 'Неверный PIN-код или нет доступа.');
            $this->pin = '';
            return;
        }

        $this->operatorId = $user->id;
        $this->operatorName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->email;
        $this->pinLocked = false;
        $this->pin = '';

        session(['kitchen_operator' => ['id' => $user->id, 'name' => $this->operatorName]]);
    }

    public function lockKitchen(): void
    {
        $this->pinLocked = true;
        $this->pin = '';
        $this->operatorId = null;
        $this->operatorName = null;
        session()->forget('kitchen_operator');
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
