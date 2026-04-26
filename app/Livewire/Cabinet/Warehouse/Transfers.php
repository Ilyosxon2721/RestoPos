<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet\Warehouse;

use App\Domain\Warehouse\Models\Ingredient;
use App\Domain\Warehouse\Models\Stock;
use App\Domain\Warehouse\Models\StockMovement;
use App\Domain\Warehouse\Models\Transfer;
use App\Domain\Warehouse\Models\Warehouse;
use App\Support\Enums\StockMovementType;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.cabinet')]
final class Transfers extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public ?int $fromWarehouseId = null;

    public ?int $toWarehouseId = null;

    public string $notes = '';

    public array $transferItems = [];

    public function create(): void
    {
        $this->reset(['fromWarehouseId', 'toWarehouseId', 'notes', 'transferItems']);
        $this->transferItems = [];
        $this->showModal = true;
    }

    public function addItem(): void
    {
        $this->transferItems[] = ['ingredient_id' => null, 'quantity' => ''];
    }

    public function removeItem(int $index): void
    {
        unset($this->transferItems[$index]);
        $this->transferItems = array_values($this->transferItems);
    }

    public function save(): void
    {
        $this->validate([
            'fromWarehouseId' => 'required|exists:warehouses,id',
            'toWarehouseId' => 'required|exists:warehouses,id|different:fromWarehouseId',
            'transferItems' => 'required|array|min:1',
            'transferItems.*.ingredient_id' => 'required|exists:ingredients,id',
            'transferItems.*.quantity' => 'required|numeric|min:0.001',
        ], [
            'toWarehouseId.different' => 'Склад-получатель должен отличаться от склада-отправителя.',
        ]);

        DB::transaction(function () {
            $transfer = Transfer::create([
                'from_warehouse_id' => $this->fromWarehouseId,
                'to_warehouse_id' => $this->toWarehouseId,
                'user_id' => auth()->id(),
                'status' => 'received',
                'notes' => $this->notes ?: null,
            ]);

            foreach ($this->transferItems as $item) {
                $qty = (float) $item['quantity'];
                $ingredientId = $item['ingredient_id'];

                $transfer->items()->create([
                    'ingredient_id' => $ingredientId,
                    'quantity' => $qty,
                ]);

                // Списываем с отправителя
                $fromStock = Stock::firstOrCreate(
                    ['warehouse_id' => $this->fromWarehouseId, 'ingredient_id' => $ingredientId],
                    ['quantity' => 0, 'reserved_quantity' => 0, 'average_cost' => 0]
                );
                $costPrice = (float) $fromStock->average_cost;
                $fromStock->update(['quantity' => max(0, (float) $fromStock->quantity - $qty)]);

                // Начисляем получателю
                $toStock = Stock::firstOrCreate(
                    ['warehouse_id' => $this->toWarehouseId, 'ingredient_id' => $ingredientId],
                    ['quantity' => 0, 'reserved_quantity' => 0, 'average_cost' => 0]
                );
                $newQty = (float) $toStock->quantity + $qty;
                $newAvgCost = $newQty > 0
                    ? (((float) $toStock->quantity * (float) $toStock->average_cost) + ($qty * $costPrice)) / $newQty
                    : $costPrice;
                $toStock->update(['quantity' => $newQty, 'average_cost' => $newAvgCost]);

                // Движения
                StockMovement::create([
                    'warehouse_id' => $this->fromWarehouseId,
                    'ingredient_id' => $ingredientId,
                    'user_id' => auth()->id(),
                    'type' => StockMovementType::TRANSFER_OUT,
                    'quantity' => -$qty,
                    'cost_price' => $costPrice,
                    'reference_type' => Transfer::class,
                    'reference_id' => $transfer->id,
                ]);
                StockMovement::create([
                    'warehouse_id' => $this->toWarehouseId,
                    'ingredient_id' => $ingredientId,
                    'user_id' => auth()->id(),
                    'type' => StockMovementType::TRANSFER_IN,
                    'quantity' => $qty,
                    'cost_price' => $costPrice,
                    'reference_type' => Transfer::class,
                    'reference_id' => $transfer->id,
                ]);
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

    #[Computed]
    public function ingredients()
    {
        return Ingredient::where('organization_id', auth()->user()->organization_id)
            ->active()->with('unit')->orderBy('name')->get();
    }

    public function render()
    {
        $orgId = auth()->user()->organization_id;

        $transfers = Transfer::query()
            ->where(fn ($q) => $q
                ->whereHas('fromWarehouse.branch', fn ($q2) => $q2->where('organization_id', $orgId))
                ->orWhereHas('toWarehouse.branch', fn ($q2) => $q2->where('organization_id', $orgId))
            )
            ->with(['fromWarehouse', 'toWarehouse', 'user', 'items.ingredient'])
            ->latest()
            ->paginate(20);

        return view('livewire.cabinet.warehouse.transfers', compact('transfers'));
    }
}
