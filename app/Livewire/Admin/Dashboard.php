<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Domain\Organization\Models\Organization;
use App\Domain\Platform\Models\Plan;
use App\Domain\Platform\Models\Subscription;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

#[Layout('components.layouts.admin')]
class Dashboard extends Component
{
    #[Computed]
    public function totalOrganizations(): int
    {
        return Organization::count();
    }

    #[Computed]
    public function activeSubscriptions(): int
    {
        return Subscription::where('status', 'active')->count();
    }

    #[Computed]
    public function totalRevenue(): float
    {
        return (float) Subscription::join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->where('subscriptions.status', 'active')
            ->sum('plans.price');
    }

    #[Computed]
    public function totalPlans(): int
    {
        return Plan::where('is_active', true)->count();
    }

    #[Computed]
    public function recentOrganizations()
    {
        return Organization::latest()->take(10)->get();
    }

    #[Computed]
    public function recentSubscriptions()
    {
        return Subscription::with(['organization', 'plan'])->latest()->take(10)->get();
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
