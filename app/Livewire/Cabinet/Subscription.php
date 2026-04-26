<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet;

use App\Domain\Platform\Models\Plan;
use App\Domain\Platform\Models\Subscription as SubscriptionModel;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.cabinet')]
class Subscription extends Component
{
    #[Computed]
    public function currentSubscription(): ?SubscriptionModel
    {
        return SubscriptionModel::where('organization_id', auth()->user()->organization_id)
            ->with('plan')
            ->latest()
            ->first();
    }

    #[Computed]
    public function availablePlans()
    {
        return Plan::active()->ordered()->get();
    }

    public function render()
    {
        return view('livewire.cabinet.subscription');
    }
}
