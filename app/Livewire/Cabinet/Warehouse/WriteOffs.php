<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet\Warehouse;

use App\Domain\Warehouse\Models\Ingredient;
use App\Domain\Warehouse\Models\Stock;
use App\Domain\Warehouse\Models\StockBatch;
use App\Domain\Warehouse\Models\StockMovement;
use App\Domain\Warehouse\Models\Warehouse;
use App\Domain\Warehouse\Models\WriteOff;
use App\Support\Enums\StockMovementType;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.cabinet')]
final class WriteOffs extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public ?int $warehouseId = null;

    public string $reason = 'spoilage';

    public string $notes = '';

    public array $writeOffItems = [];

    public function create(): void
    {
        $this->reset(['warehouseId', 'reason', 'notes', 'writeOffItems']);
        $this->reason = 'spoilage';
        $this->writeOffItems = [];
        $this->showModal = true;
    }

    public function addItem(): void
    {
        $this->writeOffItems[] = [
            'ingredient_id' => null,
            'quantity' => '',
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->writeOffItems[$index]);
        $this->writeOffItems = array_values($this->writeOffItems);
    }

    public function save(): void
    {
        $this->validate([
            'warehouseId' => 'required|exists:warehouses,id',
            'reason' => 'required|in:spoilage,damage,theft,expired,other',
            'notes' => 'nullable|string|max:2000',
            'writeOffItems' => 'required|array|min:1',
            'writeOffItems.*.ingredient_id' => 'required|exists:ingredients,id',
            'writeOffItems.*.quantity' => 'required|numeric|min:0.001',
        ]);

        DB::transaction(function () {
            $writeOff = WriteOff::create([
                'warehouse_id' => $this->warehouseId,
                'user_id' => auth()->id(),
                'reason' => $this->reason,
                'notes' => $this->notes ?: null,
            ]);

            $totalAmount = 0;

            foreach ($this->writeOffItems as $item) {
                $qty = (float) $item['quantity'];

                $stock = Stock::where('warehouse_id', $this->warehouseId)
                    ->where('ingredient_id', $item['ingredient_id'])
                    ->first();

                $costPrice = $stock ? (float) $stock->average_cost : 0;

                $writeOff->items()->create([
                    'ingredient_id' => $item['ingredient_id'],
                    'quantity' => $qty,
                    'cost_price' => $costPrice,
                ]);

                $totalAmount += $qty * $costPrice;

                // Списываем со склада
                if ($stock) {
                    $stock->update([
                        'quantity' => max(0, (float) $stock->quantity - $qty),
                    ]);
                }

                // FIFO списание из партий
                $remaining = $qty;
                $batches = StockBatch::where('warehouse_id', $this->warehouseId)
                    ->where('ingredient_id', $item['ingredient_id'])
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

                // Движение
                StockMovement::create([
                    'warehouse_id' => $this->warehouseId,
                    'ingredient_id' => $item['ingredient_id'],
                    'user_id' => auth()->id(),
                    'type' => StockMovementType::WRITE_OFF,
                    'quantity' => -$qty,
                    'cost_price' => $costPrice,
                    'reference_type' => WriteOff::class,
                    'reference_id' => $writeOff->id,
                ]);
            }

            $writeOff->update(['total_amount' => $totalAmount]);
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

        $writeOffs = WriteOff::query()
            ->whereHas('warehouse.branch', fn ($q) => $q->where('organization_id', $orgId))
            ->with(['warehouse', 'user', 'items.ingredient'])
            ->latest()
            ->paginate(20);

        return view('livewire.cabinet.warehouse.write-offs', compact('writeOffs'));
    }
}
