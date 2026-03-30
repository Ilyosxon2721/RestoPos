<?php

declare(strict_types=1);

namespace App\Livewire\Menu;

use App\Domain\Menu\Models\Unit;
use App\Domain\Warehouse\Models\Ingredient;
use App\Support\Traits\ResolvesLayout;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

final class Ingredients extends Component
{
    use WithPagination, ResolvesLayout;

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $name = '';
    public ?int $unitId = null;
    public string $sku = '';
    public string $barcode = '';
    public string $minStock = '0';
    public string $currentCost = '0';
    public string $lossPercent = '0';
    public ?int $shelfLifeDays = null;
    public bool $isActive = true;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'unitId', 'sku', 'barcode', 'minStock', 'currentCost', 'lossPercent', 'shelfLifeDays', 'isActive']);
        $this->isActive = true;
        $this->minStock = '0';
        $this->currentCost = '0';
        $this->lossPercent = '0';
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $ingredient = Ingredient::where('organization_id', auth()->user()->organization_id)->findOrFail($id);

        $this->editingId = $ingredient->id;
        $this->name = $ingredient->name;
        $this->unitId = $ingredient->unit_id;
        $this->sku = $ingredient->sku ?? '';
        $this->barcode = $ingredient->barcode ?? '';
        $this->minStock = (string) $ingredient->min_stock;
        $this->currentCost = (string) $ingredient->current_cost;
        $this->lossPercent = (string) $ingredient->loss_percent;
        $this->shelfLifeDays = $ingredient->shelf_life_days;
        $this->isActive = $ingredient->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'unitId' => 'required|exists:units,id',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'minStock' => 'required|numeric|min:0',
            'currentCost' => 'required|numeric|min:0',
            'lossPercent' => 'nullable|numeric|min:0|max:100',
            'shelfLifeDays' => 'nullable|integer|min:1',
        ]);

        $orgId = auth()->user()->organization_id;

        $data = [
            'organization_id' => $orgId,
            'name' => $this->name,
            'unit_id' => $this->unitId,
            'sku' => $this->sku ?: null,
            'barcode' => $this->barcode ?: null,
            'min_stock' => (float) $this->minStock,
            'current_cost' => (float) $this->currentCost,
            'loss_percent' => (float) ($this->lossPercent ?: 0),
            'shelf_life_days' => $this->shelfLifeDays,
            'is_active' => $this->isActive,
        ];

        if ($this->editingId) {
            Ingredient::where('organization_id', $orgId)->findOrFail($this->editingId)->update($data);
        } else {
            Ingredient::create($data);
        }

        $this->showModal = false;
    }

    public function toggleActive(int $id): void
    {
        $ingredient = Ingredient::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $ingredient->update(['is_active' => !$ingredient->is_active]);
    }

    public function deleteIngredient(int $id): void
    {
        Ingredient::where('organization_id', auth()->user()->organization_id)->findOrFail($id)->delete();
    }

    #[Computed]
    public function units()
    {
        return Unit::where('organization_id', auth()->user()->organization_id)->orderBy('name')->get();
    }

    public function render()
    {
        $orgId = auth()->user()->organization_id;

        $ingredients = Ingredient::where('organization_id', $orgId)
            ->when($this->search, fn($q) => $q->where(fn($q2) => $q2
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('sku', 'like', "%{$this->search}%")
                ->orWhere('barcode', 'like', "%{$this->search}%")
            ))
            ->with('unit')
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.menu.ingredients', compact('ingredients'))
            ->layout($this->resolveLayout());
    }
}
