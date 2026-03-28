<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Domain\Floor\Models\Table;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Models\Payment;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\PaymentStatus;
use App\Support\Enums\TableStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
final class Dashboard extends Component
{
    #[Computed]
    public function todayOrders(): int
    {
        return Order::whereDate('created_at', Carbon::today())->count();
    }

    #[Computed]
    public function todayRevenue(): float
    {
        return (float) Payment::whereDate('created_at', Carbon::today())
            ->where('status', PaymentStatus::Completed)
            ->sum('amount');
    }

    #[Computed]
    public function openOrders(): int
    {
        return Order::whereIn('status', [
            OrderStatus::NEW,
            OrderStatus::ACCEPTED,
            OrderStatus::PREPARING,
            OrderStatus::READY,
            OrderStatus::SERVED,
        ])->count();
    }

    #[Computed]
    public function occupiedTables(): int
    {
        return Table::where('status', TableStatus::Occupied)->count();
    }

    #[Computed]
    public function totalTables(): int
    {
        return Table::count();
    }

    #[Computed]
    public function recentOrders(): Collection
    {
        return Order::with(['table', 'waiter'])
            ->latest()
            ->limit(10)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.dashboard');
    }
}
