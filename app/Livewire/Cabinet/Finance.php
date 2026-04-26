<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet;

use App\Domain\Order\Models\Order;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.cabinet')]
class Finance extends Component
{
    public string $period = 'today';

    #[Computed]
    public function revenue(): float
    {
        $query = Order::whereHas('branch', fn ($q) => $q->where('organization_id', auth()->user()->organization_id))
            ->where('status', 'completed');

        return (float) match ($this->period) {
            'today' => $query->whereDate('created_at', today())->sum('total_amount'),
            'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()])->sum('total_amount'),
            'month' => $query->whereMonth('created_at', now()->month)->sum('total_amount'),
            default => $query->sum('total_amount'),
        };
    }

    #[Computed]
    public function ordersCount(): int
    {
        $query = Order::whereHas('branch', fn ($q) => $q->where('organization_id', auth()->user()->organization_id));

        return match ($this->period) {
            'today' => $query->whereDate('created_at', today())->count(),
            'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()])->count(),
            'month' => $query->whereMonth('created_at', now()->month)->count(),
            default => $query->count(),
        };
    }

    public function render()
    {
        return view('livewire.cabinet.finance');
    }
}
