<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet;

use App\Domain\Customer\Models\Customer;
use App\Domain\Order\Models\Order;
use App\Domain\Organization\Models\Branch;
use App\Domain\Staff\Models\Employee;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.cabinet')]
class Dashboard extends Component
{
    #[Computed]
    public function totalBranches(): int
    {
        return Branch::where('organization_id', auth()->user()->organization_id)->count();
    }

    #[Computed]
    public function totalEmployees(): int
    {
        return Employee::whereHas('user', fn ($q) => $q->where('organization_id', auth()->user()->organization_id))->count();
    }

    #[Computed]
    public function totalCustomers(): int
    {
        return Customer::where('organization_id', auth()->user()->organization_id)->count();
    }

    #[Computed]
    public function todayRevenue(): float
    {
        return (float) Order::whereHas('branch', fn ($q) => $q->where('organization_id', auth()->user()->organization_id))
            ->whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('total_amount');
    }

    #[Computed]
    public function recentOrders()
    {
        return Order::whereHas('branch', fn ($q) => $q->where('organization_id', auth()->user()->organization_id))
            ->with('branch')
            ->latest()
            ->take(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.cabinet.dashboard');
    }
}
