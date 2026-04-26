<?php

declare(strict_types=1);

namespace App\Livewire\Manager;

use App\Domain\Floor\Models\Table;
use App\Domain\Order\Models\Order;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.manager')]
class Dashboard extends Component
{
    #[Computed]
    public function todayOrders(): int
    {
        $branchId = session('current_branch_id');

        return Order::where('branch_id', $branchId)->whereDate('created_at', today())->count();
    }

    #[Computed]
    public function todayRevenue(): float
    {
        $branchId = session('current_branch_id');

        return (float) Order::where('branch_id', $branchId)
            ->whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('total_amount');
    }

    #[Computed]
    public function openOrders(): int
    {
        $branchId = session('current_branch_id');

        return Order::where('branch_id', $branchId)->whereNotIn('status', ['completed', 'cancelled'])->count();
    }

    #[Computed]
    public function occupiedTables(): int
    {
        $branchId = session('current_branch_id');

        return Table::whereHas('hall', fn ($q) => $q->where('branch_id', $branchId))->where('status', 'occupied')->count();
    }

    #[Computed]
    public function recentOrders()
    {
        $branchId = session('current_branch_id');

        return Order::where('branch_id', $branchId)->latest()->take(15)->get();
    }

    public function render()
    {
        return view('livewire.manager.dashboard');
    }
}
