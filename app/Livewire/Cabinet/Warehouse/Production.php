<?php

declare(strict_types=1);

namespace App\Livewire\Cabinet\Warehouse;

use App\Domain\Menu\Models\Product;
use App\Domain\Warehouse\Models\Stock;
use App\Domain\Warehouse\Models\StockBatch;
use App\Domain\Warehouse\Models\StockMovement;
use App\Domain\Warehouse\Models\TechCard;
use App\Domain\Warehouse\Models\Warehouse;
use App\Support\Enums\StockMovementType;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.cabinet')]
final class Production extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public ?int $warehouseId = null;
    public ?int $productId = null;
    public string $quantity = '';
    public string $notes = '';

    /** @var array<int, array{ingredient_id: int, name: string, unit: string, required: float, available: float}> */
    public array $calculatedIngredients = [];
    public bool $canProduce = false;

    public function create(): void
    {
        $this->reset(['warehouseId', 'productId', 'quantity', 'notes', 'calculatedIngredients', 'canProduce']);
        $this->showModal = true;
    }

    /**
     * Пересчитать необходимые ингредиенты при изменении продукта или количества.
     */
    public function updatedProductId(): void
    {
        $this->calculateIngredients();
    }

    public function updatedQuantity(): void
    {
        $this->calculateIngredients();
    }

    public function updatedWarehouseId(): void
    {
        $this->calculateIngredients();
    }

    public function calculateIngredients(): void
    {
        $this->calculatedIngredients = [];
        $this->canProduce = false;

        if (!$this->productId || !$this->quantity || (float) $this->quantity <= 0 || !$this->warehouseId) {
            return;
        }

        $techCard = TechCard::where('product_id', $this->productId)->active()->with('items.ingredient.unit')->first();

        if (!$techCard) {
            return;
        }

        $outputQty = (float) $techCard->output_quantity;
        if ($outputQty <= 0) {
            $outputQty = 1;
        }

        $multiplier = (float) $this->quantity / $outputQty;
        $this->canProduce = true;

        foreach ($techCard->items as $item) {
            if (!$item->ingredient) {
                continue;
            }

            $requiredQty = $item->gross_quantity * $multiplier;

            // Проверяем доступность на выбранном складе
            $stock = Stock::where('warehouse_id', $this->warehouseId)
                ->where('ingredient_id', $item->ingredient_id)
                ->first();

            $available = $stock ? (float) $stock->quantity : 0;

            if ($available < $requiredQty) {
                $this->canProduce = false;
            }

            $this->calculatedIngredients[] = [
                'ingredient_id' => $item->ingredient_id,
                'name' => $item->ingredient->name,
                'unit' => $item->ingredient->unit?->short_name ?? '',
                'required' => round($requiredQty, 3),
                'available' => round($available, 3),
            ];
        }
    }

    public function save(): void
    {
        $this->validate([
            'warehouseId' => 'required|exists:warehouses,id',
            'productId' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.001',
            'notes' => 'nullable|string|max:2000',
        ]);

        $techCard = TechCard::where('product_id', $this->productId)->active()->with('items.ingredient')->first();

        if (!$techCard) {
            $this->addError('productId', 'Техкарта не найдена для выбранного продукта.');
            return;
        }

        $outputQty = (float) $techCard->output_quantity;
        if ($outputQty <= 0) {
            $outputQty = 1;
        }
        $multiplier = (float) $this->quantity / $outputQty;

        DB::transaction(function () use ($techCard, $multiplier) {
            foreach ($techCard->items as $item) {
                if (!$item->ingredient_id) {
                    continue;
                }

                $requiredQty = $item->gross_quantity * $multiplier;

                // Уменьшаем остаток на складе
                $stock = Stock::where('warehouse_id', $this->warehouseId)
                    ->where('ingredient_id', $item->ingredient_id)
                    ->first();

                $costPrice = $stock ? (float) $stock->average_cost : 0;

                if ($stock) {
                    $stock->update([
                        'quantity' => max(0, (float) $stock->quantity - $requiredQty),
                    ]);
                }

                // FIFO списание из партий
                $remaining = $requiredQty;
                $batches = StockBatch::where('warehouse_id', $this->warehouseId)
                    ->where('ingredient_id', $item->ingredient_id)
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

                // Движение склада
                StockMovement::create([
                    'warehouse_id' => $this->warehouseId,
                    'ingredient_id' => $item->ingredient_id,
                    'user_id' => auth()->id(),
                    'type' => StockMovementType::PRODUCTION,
                    'quantity' => -$requiredQty,
                    'cost_price' => $costPrice,
                    'reference_type' => TechCard::class,
                    'reference_id' => $techCard->id,
                    'notes' => $this->notes ?: null,
                ]);
            }
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
    public function products()
    {
        $orgId = auth()->user()->organization_id;

        return Product::where('organization_id', $orgId)
            ->whereHas('techCard', fn ($q) => $q->where('is_active', true))
            ->with('unit')
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        $orgId = auth()->user()->organization_id;

        // Показываем последние производственные движения (группируем по дате+techcard)
        $productions = StockMovement::query()
            ->where('type', StockMovementType::PRODUCTION)
            ->whereHas('warehouse.branch', fn ($q) => $q->where('organization_id', $orgId))
            ->with(['warehouse', 'ingredient.unit', 'user'])
            ->latest()
            ->paginate(20);

        return view('livewire.cabinet.warehouse.production', compact('productions'));
    }
}
