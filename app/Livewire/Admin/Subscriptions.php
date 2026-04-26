<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Domain\Platform\Models\Subscription;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class Subscriptions extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function cancel(int $id): void
    {
        $sub = Subscription::findOrFail($id);
        $sub->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    public function activate(int $id): void
    {
        $sub = Subscription::findOrFail($id);
        $sub->update([
            'status' => 'active',
            'cancelled_at' => null,
        ]);
    }

    public function render()
    {
        $subscriptions = Subscription::with(['organization', 'plan'])
            ->when($this->statusFilter, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20);

        return view('livewire.admin.subscriptions', compact('subscriptions'));
    }
}
