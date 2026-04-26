<?php

declare(strict_types=1);

namespace App\Livewire\WarehousePanel;

use App\Domain\Warehouse\Models\Stock as StockModel;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.warehouse-panel')]
class Stock extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $lowStockOnly = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedLowStockOnly(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $branchId = session('current_branch_id');
        $stocks = StockModel::where('branch_id', $branchId)
            ->when($this->search, fn ($q, $s) => $q->whereHas('product', fn ($q2) => $q2->where('name', 'like', "%{$s}%")))
            ->when($this->lowStockOnly, fn ($q) => $q->whereColumn('quantity', '<=', 'min_quantity'))
            ->with('product')
            ->paginate(20);

        return view('livewire.warehouse-panel.stock', compact('stocks'));
    }
}
