<?php

declare(strict_types=1);

namespace App\Livewire\Waiter;

use App\Domain\Floor\Models\Hall;
use App\Domain\Floor\Models\Table;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.waiter')]
class Tables extends Component
{
    public ?int $selectedHall = null;

    public function mount(): void
    {
        $branchId = session('current_branch_id');
        $this->selectedHall = Hall::where('branch_id', $branchId)->first()?->id;
    }

    public function selectHall(int $hallId): void
    {
        $this->selectedHall = $hallId;
    }

    public function render()
    {
        $branchId = session('current_branch_id');
        $halls = Hall::where('branch_id', $branchId)->get();
        $tables = $this->selectedHall ? Table::where('hall_id', $this->selectedHall)->with('currentOrder')->get() : collect();

        return view('livewire.waiter.tables', compact('halls', 'tables'));
    }
}
