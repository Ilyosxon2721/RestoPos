<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet\Warehouse;

use App\Domain\Warehouse\Models\Ingredient;
use App\Domain\Warehouse\Models\Stock;
use App\Domain\Warehouse\Models\StockBatch;
use App\Domain\Warehouse\Models\StockMovement;
use App\Domain\Warehouse\Models\Warehouse;
use App\Support\Enums\StockMovementType;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.cabinet')]
final class Packaging extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public ?int $warehouseId = null;

    public ?int $sourceIngredientId = null;

    public string $sourceQuantity = '';

    public ?int $targetIngredientId = null;

    public string $targetQuantity = '';

    public string $notes = '';

    public function create(): void
    {
        $this->reset(['warehouseId', 'sourceIngredientId', 'sourceQuantity', 'targetIngredientId', 'targetQuantity', 'notes']);
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'warehouseId' => 'required|exists:warehouses,id',
            'sourceIngredientId' => 'required|exists:ingredients,id',
            'sourceQuantity' => 'required|numeric|min:0.001',
            'targetIngredientId' => 'required|exists:ingredients,id',
            'targetQuantity' => 'required|numeric|min:0.001',
            'notes' => 'nullable|string|max:2000',
        ]);

        $sourceQty = (float) $this->sourceQuantity;
        $targetQty = (float) $this->targetQuantity;

        DB::transaction(function () use ($sourceQty, $targetQty) {
            // === Списание с источника ===
            $sourceStock = Stock::where('warehouse_id', $this->warehouseId)
                ->where('ingredient_id', $this->sourceIngredientId)
                ->first();

            $costPrice = $sourceStock ? (float) $sourceStock->average_cost : 0;

            if ($sourceStock) {
                $sourceStock->update([
                    'quantity' => max(0, (float) $sourceStock->quantity - $sourceQty),
                ]);
            }

            // FIFO списание из партий источника
            $remaining = $sourceQty;
            $batches = StockBatch::where('warehouse_id', $this->warehouseId)
                ->where('ingredient_id', $this->sourceIngredientId)
                ->where('remaining_quantity', '>', 0)
                ->orderBy('expiry_date')
                ->orderBy('created_at')
                ->get();

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }
                $deduct = min($remaining, (float) $batch->remaining_quantity);
                $batch->update(['remaining_quantity' => (float) $batch->remaining_quantity - $deduct]);
                $remaining -= $deduct;
            }

            // Движение — расход источника
            StockMovement::create([
                'warehouse_id' => $this->warehouseId,
                'ingredient_id' => $this->sourceIngredientId,
                'user_id' => auth()->id(),
                'type' => StockMovementType::PRODUCTION,
                'quantity' => -$sourceQty,
                'cost_price' => $costPrice,
                'notes' => 'Фасовка (расход): '.($this->notes ?: ''),
            ]);

            // === Приход на цель ===
            // Рассчитываем себестоимость единицы цели
            $targetCostPerUnit = $targetQty > 0 ? ($costPrice * $sourceQty) / $targetQty : 0;

            $targetStock = Stock::firstOrCreate(
                [
                    'warehouse_id' => $this->warehouseId,
                    'ingredient_id' => $this->targetIngredientId,
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'average_cost' => 0,
                ]
            );

            // Пересчёт средней себестоимости
            $oldQty = (float) $targetStock->quantity;
            $oldCost = (float) $targetStock->average_cost;
            $newQty = $oldQty + $targetQty;
            $newAvgCost = $newQty > 0
                ? (($oldQty * $oldCost) + ($targetQty * $targetCostPerUnit)) / $newQty
                : $targetCostPerUnit;

            $targetStock->update([
                'quantity' => $newQty,
                'average_cost' => round($newAvgCost, 4),
            ]);

            // Новая партия для цели
            StockBatch::create([
                'warehouse_id' => $this->warehouseId,
                'ingredient_id' => $this->targetIngredientId,
                'initial_quantity' => $targetQty,
                'remaining_quantity' => $targetQty,
                'cost_price' => round($targetCostPerUnit, 4),
            ]);

            // Движение — приход цели
            StockMovement::create([
                'warehouse_id' => $this->warehouseId,
                'ingredient_id' => $this->targetIngredientId,
                'user_id' => auth()->id(),
                'type' => StockMovementType::PRODUCTION,
                'quantity' => $targetQty,
                'cost_price' => round($targetCostPerUnit, 4),
                'notes' => 'Фасовка (приход): '.($this->notes ?: ''),
            ]);
        });

        $this->showModal = false;
        $this->resetPage();
    }

    #[Computed]
    public function warehouses()
    {
        $orgId = auth()->user()->organization_id;

        return Warehouse::whereHas('branch', fn ($q) => $q->where('organization_id', $orgId))
            ->where('is_active', true)->with('branch')->get();
    }

    #[Computed]
    public function ingredients()
    {
        return Ingredient::where('organization_id', auth()->user()->organization_id)
            ->active()->with('unit')->orderBy('name')->get();
    }

    public function render()
    {
        $orgId = auth()->user()->organization_id;

        // Показываем движения типа PRODUCTION с примечанием "Фасовка"
        $packagings = StockMovement::query()
            ->where('type', StockMovementType::PRODUCTION)
            ->where('notes', 'like', 'Фасовка%')
            ->whereHas('warehouse.branch', fn ($q) => $q->where('organization_id', $orgId))
            ->with(['warehouse', 'ingredient.unit', 'user'])
            ->latest()
            ->paginate(20);

        return view('livewire.cabinet.warehouse.packaging', compact('packagings'));
    }
}
