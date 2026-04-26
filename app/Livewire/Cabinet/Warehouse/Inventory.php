<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet\Warehouse;

use App\Domain\Warehouse\Models\Inventory as InventoryModel;
use App\Domain\Warehouse\Models\Stock;
use App\Domain\Warehouse\Models\StockMovement;
use App\Domain\Warehouse\Models\Warehouse;
use App\Support\Enums\StockMovementType;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.cabinet')]
final class Inventory extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public ?int $warehouseId = null;

    public string $notes = '';

    public array $inventoryItems = [];

    public function create(): void
    {
        $this->reset(['warehouseId', 'notes', 'inventoryItems']);
        $this->inventoryItems = [];
        $this->showModal = true;
    }

    public function loadStockItems(): void
    {
        if (!$this->warehouseId) {
            return;
        }

        $stocks = Stock::where('warehouse_id', $this->warehouseId)
            ->with('ingredient.unit')
            ->where('quantity', '>', 0)
            ->get();

        $this->inventoryItems = $stocks->map(fn ($stock) => [
            'ingredient_id' => $stock->ingredient_id,
            'ingredient_name' => $stock->ingredient->name.' ('.($stock->ingredient->unit?->short_name ?? '').')',
            'expected_quantity' => (string) $stock->quantity,
            'actual_quantity' => '',
            'cost_price' => (string) $stock->average_cost,
        ])->toArray();
    }

    public function save(): void
    {
        $this->validate([
            'warehouseId' => 'required|exists:warehouses,id',
            'inventoryItems' => 'required|array|min:1',
            'inventoryItems.*.actual_quantity' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () {
            $inventory = InventoryModel::create([
                'warehouse_id' => $this->warehouseId,
                'user_id' => auth()->id(),
                'status' => 'completed',
                'started_at' => now(),
                'completed_at' => now(),
                'notes' => $this->notes ?: null,
            ]);

            foreach ($this->inventoryItems as $item) {
                $expected = (float) $item['expected_quantity'];
                $actual = (float) $item['actual_quantity'];
                $diff = $actual - $expected;

                $inventory->items()->create([
                    'ingredient_id' => $item['ingredient_id'],
                    'expected_quantity' => $expected,
                    'actual_quantity' => $actual,
                    'cost_price' => (float) ($item['cost_price'] ?? 0),
                ]);

                // Корректируем остатки если есть разница
                if (abs($diff) > 0.001) {
                    $stock = Stock::where('warehouse_id', $this->warehouseId)
                        ->where('ingredient_id', $item['ingredient_id'])
                        ->first();

                    if ($stock) {
                        $stock->update(['quantity' => max(0, $actual)]);
                    }

                    StockMovement::create([
                        'warehouse_id' => $this->warehouseId,
                        'ingredient_id' => $item['ingredient_id'],
                        'user_id' => auth()->id(),
                        'type' => StockMovementType::INVENTORY,
                        'quantity' => $diff,
                        'cost_price' => (float) ($item['cost_price'] ?? 0),
                        'reference_type' => InventoryModel::class,
                        'reference_id' => $inventory->id,
                        'notes' => 'Инвентаризация: ожидалось '.$expected.', фактически '.$actual,
                    ]);
                }
            }
        });

        $this->showModal = false;
    }

    #[Computed]
    public function warehouses()
    {
        $orgId = auth()->user()->organization_id;

        return Warehouse::whereHas('branch', fn ($q) => $q->where('organization_id', $orgId))
            ->where('is_active', true)->with('branch')->get();
    }

    public function render()
    {
        $orgId = auth()->user()->organization_id;

        $inventories = InventoryModel::query()
            ->whereHas('warehouse.branch', fn ($q) => $q->where('organization_id', $orgId))
            ->with(['warehouse', 'user', 'items.ingredient'])
            ->latest()
            ->paginate(20);

        return view('livewire.cabinet.warehouse.inventory', compact('inventories'));
    }
}
