<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet\Warehouse;

use App\Domain\Warehouse\Models\StockMovement;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.cabinet')]
final class Movement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $typeFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $orgId = auth()->user()->organization_id;

        $movements = StockMovement::query()
            ->whereHas('warehouse.branch', fn($q) => $q->where('organization_id', $orgId))
            ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
            ->when($this->search, fn($q) => $q->whereHas('ingredient', fn($q2) => $q2->where('name', 'like', "%{$this->search}%")))
            ->with(['warehouse', 'ingredient.unit', 'user'])
            ->latest()
            ->paginate(30);

        return view('livewire.cabinet.warehouse.movement', compact('movements'));
    }
}
