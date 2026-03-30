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

    // Модалка зала
    public bool $showHallModal = false;
    public ?int $editingHallId = null;
    public string $hallName = '';

    // Модалка стола
    public bool $showTableModal = false;
    public ?int $editingTableId = null;
    public string $tableName = '';
    public string $tableCapacity = '4';
    public string $tableShape = 'square';

    public function mount(): void
    {
        $branchId = session('current_branch_id');
        $this->selectedHall = Hall::where('branch_id', $branchId)->first()?->id;
    }

    public function selectHall(int $hallId): void
    {
        $this->selectedHall = $hallId;
    }

    // === Залы ===

    public function createHall(): void
    {
        $this->reset(['editingHallId', 'hallName']);
        $this->showHallModal = true;
    }

    public function editHall(int $id): void
    {
        $hall = Hall::findOrFail($id);
        $this->editingHallId = $hall->id;
        $this->hallName = $hall->name;
        $this->showHallModal = true;
    }

    public function saveHall(): void
    {
        $this->validate([
            'hallName' => 'required|string|max:100',
        ]);

        $branchId = session('current_branch_id');

        if ($this->editingHallId) {
            $hall = Hall::findOrFail($this->editingHallId);
            $hall->update(['name' => $this->hallName]);
        } else {
            $hall = Hall::create([
                'branch_id' => $branchId,
                'name' => $this->hallName,
                'sort_order' => Hall::where('branch_id', $branchId)->max('sort_order') + 1,
            ]);
            $this->selectedHall = $hall->id;
        }

        $this->showHallModal = false;
    }

    public function deleteHall(int $id): void
    {
        $hall = Hall::findOrFail($id);
        $hall->tables()->delete();
        $hall->delete();

        $branchId = session('current_branch_id');
        $this->selectedHall = Hall::where('branch_id', $branchId)->first()?->id;
    }

    // === Столы ===

    public function createTable(): void
    {
        if (! $this->selectedHall) return;

        $this->reset(['editingTableId', 'tableCapacity', 'tableShape']);

        // Автоинкремент имени стола
        $maxName = Table::where('hall_id', $this->selectedHall)->max('sort_order') ?? 0;
        $this->tableName = (string) ($maxName + 1);
        $this->tableCapacity = '4';
        $this->tableShape = 'square';

        $this->showTableModal = true;
    }

    public function editTable(int $id): void
    {
        $table = Table::findOrFail($id);
        $this->editingTableId = $table->id;
        $this->tableName = $table->name;
        $this->tableCapacity = (string) ($table->capacity ?? 4);
        $this->tableShape = $table->shape ?? 'square';
        $this->showTableModal = true;
    }

    public function saveTable(): void
    {
        $this->validate([
            'tableName' => 'required|string|max:50',
            'tableCapacity' => 'required|integer|min:1|max:50',
            'tableShape' => 'required|in:square,round,rectangle',
        ]);

        $data = [
            'hall_id' => $this->selectedHall,
            'name' => $this->tableName,
            'capacity' => (int) $this->tableCapacity,
            'shape' => $this->tableShape,
        ];

        if ($this->editingTableId) {
            Table::findOrFail($this->editingTableId)->update($data);
        } else {
            $maxSort = Table::where('hall_id', $this->selectedHall)->max('sort_order') ?? 0;
            $data['sort_order'] = $maxSort + 1;
            $data['status'] = 'free';
            Table::create($data);
        }

        $this->showTableModal = false;
    }

    public function deleteTable(int $id): void
    {
        Table::findOrFail($id)->delete();
    }

    public function render()
    {
        $branchId = session('current_branch_id');
        $halls = Hall::where('branch_id', $branchId)->ordered()->get();
        $tables = $this->selectedHall
            ? Table::where('hall_id', $this->selectedHall)->orderBy('sort_order')->get()
            : collect();

        return view('livewire.manager.floor', compact('halls', 'tables'));
    }
}
