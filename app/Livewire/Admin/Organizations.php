<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Domain\Organization\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class Organizations extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(int $orgId): void
    {
        $org = Organization::findOrFail($orgId);
        $org->update(['is_active' => !$org->is_active]);
    }

    public function render()
    {
        $organizations = Organization::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->withCount('branches')
            ->latest()
            ->paginate(20);

        return view('livewire.admin.organizations', compact('organizations'));
    }
}
