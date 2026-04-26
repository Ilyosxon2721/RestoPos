<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet\Warehouse;

use App\Domain\Warehouse\Models\Ingredient;
use App\Domain\Warehouse\Models\Stock;
use App\Domain\Warehouse\Models\StockBatch;
use App\Domain\Warehouse\Models\StockMovement;
use App\Domain\Warehouse\Models\Supplier;
use App\Domain\Warehouse\Models\Supply;
use App\Domain\Warehouse\Models\Warehouse;
use App\Support\Enums\StockMovementType;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.cabinet')]
final class Supplies extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    // Модалка
    public bool $showModal = false;

    public ?int $editingId = null;

    public ?int $warehouseId = null;

    public ?int $supplierId = null;

    public string $documentNumber = '';

    public ?string $documentDate = null;

    public string $notes = '';

    public array $supplyItems = [];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'warehouseId', 'supplierId', 'documentNumber', 'documentDate', 'notes', 'supplyItems']);
        $this->supplyItems = [];
        $this->showModal = true;
    }

    public function addItem(): void
    {
        $this->supplyItems[] = [
            'ingredient_id' => null,
            'quantity' => '',
            'price' => '',
            'expiry_date' => null,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->supplyItems[$index]);
        $this->supplyItems = array_values($this->supplyItems);
    }

    public function save(): void
    {
        $this->validate([
            'warehouseId' => 'required|exists:warehouses,id',
            'supplierId' => 'nullable|exists:suppliers,id',
            'documentNumber' => 'nullable|string|max:100',
            'documentDate' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
            'supplyItems' => 'required|array|min:1',
            'supplyItems.*.ingredient_id' => 'required|exists:ingredients,id',
            'supplyItems.*.quantity' => 'required|numeric|min:0.001',
            'supplyItems.*.price' => 'required|numeric|min:0',
        ], [
            'supplyItems.required' => 'Добавьте хотя бы одну позицию.',
            'supplyItems.min' => 'Добавьте хотя бы одну позицию.',
        ]);

        DB::transaction(function () {
            $supply = Supply::create([
                'warehouse_id' => $this->warehouseId,
                'supplier_id' => $this->supplierId,
                'user_id' => auth()->id(),
                'document_number' => $this->documentNumber ?: null,
                'document_date' => $this->documentDate,
                'status' => 'received',
                'received_at' => now(),
                'notes' => $this->notes ?: null,
            ]);

            $totalAmount = 0;

            foreach ($this->supplyItems as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['price'];
                $total = $qty * $price;
                $totalAmount += $total;

                $supplyItem = $supply->items()->create([
                    'ingredient_id' => $item['ingredient_id'],
                    'quantity' => $qty,
                    'price' => $price,
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);

                // Обновляем складские остатки
                $stock = Stock::firstOrCreate(
                    ['warehouse_id' => $this->warehouseId, 'ingredient_id' => $item['ingredient_id']],
                    ['quantity' => 0, 'reserved_quantity' => 0, 'average_cost' => 0]
                );

                $newQty = (float) $stock->quantity + $qty;
                $newAvgCost = $newQty > 0
                    ? (((float) $stock->quantity * (float) $stock->average_cost) + ($qty * $price)) / $newQty
                    : $price;

                $stock->update([
                    'quantity' => $newQty,
                    'average_cost' => $newAvgCost,
                    'last_supply_date' => now(),
                    'last_supply_price' => $price,
                ]);

                // Создаём партию для FIFO
                StockBatch::create([
                    'warehouse_id' => $this->warehouseId,
                    'ingredient_id' => $item['ingredient_id'],
                    'supply_item_id' => $supplyItem->id,
                    'initial_quantity' => $qty,
                    'remaining_quantity' => $qty,
                    'cost_price' => $price,
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);

                // Движение на складе
                StockMovement::create([
                    'warehouse_id' => $this->warehouseId,
                    'ingredient_id' => $item['ingredient_id'],
                    'user_id' => auth()->id(),
                    'type' => StockMovementType::SUPPLY,
                    'quantity' => $qty,
                    'cost_price' => $price,
                    'reference_type' => Supply::class,
                    'reference_id' => $supply->id,
                ]);
            }

            $supply->update(['total_amount' => $totalAmount]);
        });

        $this->showModal = false;
    }

    #[Computed]
    public function warehouses()
    {
        $orgId = auth()->user()->organization_id;

        return Warehouse::whereHas('branch', fn ($q) => $q->where('organization_id', $orgId))
            ->where('is_active', true)
            ->with('branch')
            ->get();
    }

    #[Computed]
    public function suppliers()
    {
        return Supplier::where('organization_id', auth()->user()->organization_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function ingredients()
    {
        return Ingredient::where('organization_id', auth()->user()->organization_id)
            ->active()
            ->with('unit')
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        $orgId = auth()->user()->organization_id;

        $supplies = Supply::query()
            ->whereHas('warehouse.branch', fn ($q) => $q->where('organization_id', $orgId))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('document_number', 'like', "%{$this->search}%")
                ->orWhereHas('supplier', fn ($q3) => $q3->where('name', 'like', "%{$this->search}%"))
            ))
            ->with(['supplier', 'warehouse', 'user', 'items.ingredient'])
            ->latest()
            ->paginate(20);

        return view('livewire.cabinet.warehouse.supplies', compact('supplies'));
    }
}
