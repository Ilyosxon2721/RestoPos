<?php

declare(strict_types=1);

namespace App\Livewire\Manager;

use App\Domain\Floor\Models\Hall;
use App\Domain\Floor\Models\Table;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.manager')]
class Floor extends Component
{
    public ?int $selectedHall = null;

    public function mount(): void
    {
        $branchId = session('current_branch_id');
        $this->selectedHall = Hall::where('branch_id', $branchId)->first()?->id;
    }

    public function render()
    {
        $branchId = session('current_branch_id');
        $halls = Hall::where('branch_id', $branchId)->get();
        $tables = $this->selectedHall ? Table::where('hall_id', $this->selectedHall)->get() : collect();

        return view('livewire.manager.floor', compact('halls', 'tables'));
    }
}
