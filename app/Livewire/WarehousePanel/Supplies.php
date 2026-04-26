<?php

declare(strict_types=1);

namespace App\Livewire\WarehousePanel;

use App\Domain\Warehouse\Models\Supply;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.warehouse-panel')]
class Supplies extends Component
{
    use WithPagination;

    public function render()
    {
        $branchId = session('current_branch_id');
        $supplies = Supply::where('branch_id', $branchId)
            ->with('supplier')
            ->latest()
            ->paginate(20);

        return view('livewire.warehouse-panel.supplies', compact('supplies'));
    }
}
