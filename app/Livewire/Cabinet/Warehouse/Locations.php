<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet\Warehouse;

use App\Domain\Organization\Models\Branch;
use App\Domain\Warehouse\Models\Warehouse;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.cabinet')]
final class Locations extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $name = '';
    public ?int $branchId = null;
    public string $type = 'main';
    public bool $isDefault = false;
    public bool $isActive = true;

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'branchId', 'type', 'isDefault', 'isActive']);
        $this->type = 'main';
        $this->isActive = true;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $warehouse = Warehouse::findOrFail($id);
        $this->editingId = $warehouse->id;
        $this->name = $warehouse->name;
        $this->branchId = $warehouse->branch_id;
        $this->type = $warehouse->type;
        $this->isDefault = $warehouse->is_default;
        $this->isActive = $warehouse->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'branchId' => 'required|exists:branches,id',
            'type' => 'required|in:main,kitchen,bar,freezer',
        ]);

        $data = [
            'branch_id' => $this->branchId,
            'name' => $this->name,
            'type' => $this->type,
            'is_default' => $this->isDefault,
            'is_active' => $this->isActive,
        ];

        if ($this->editingId) {
            Warehouse::findOrFail($this->editingId)->update($data);
        } else {
            Warehouse::create($data);
        }

        $this->showModal = false;
    }

    public function toggleActive(int $id): void
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->update(['is_active' => !$warehouse->is_active]);
    }

    public function deleteWarehouse(int $id): void
    {
        Warehouse::findOrFail($id)->delete();
    }

    public function render()
    {
        $orgId = auth()->user()->organization_id;

        $warehouses = Warehouse::whereHas('branch', fn($q) => $q->where('organization_id', $orgId))
            ->with('branch')
            ->orderBy('name')
            ->get();

        $branches = Branch::where('organization_id', $orgId)->get();

        return view('livewire.cabinet.warehouse.locations', compact('warehouses', 'branches'));
    }
}
