<?php

declare(strict_types=1);

namespace App\Livewire\Menu;

use App\Domain\Menu\Actions\SaveDishAction;
use App\Domain\Menu\Models\Category;
use App\Domain\Menu\Models\ModifierGroup;
use App\Domain\Menu\Models\PreparationMethod;
use App\Domain\Menu\Models\Product;
use App\Domain\Menu\Models\Tax;
use App\Domain\Menu\Models\Unit;
use App\Domain\Menu\Models\Workshop;
use App\Domain\Warehouse\Models\Ingredient;
use App\Support\Traits\ResolvesLayout;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

final class DishEditor extends Component
{
    use ResolvesLayout, WithFileUploads;

    public ?int $productId = null;

    // Basic fields
    public string $name = '';

    public ?int $categoryId = null;

    public ?int $workshopId = null;

    public ?int $taxId = null;

    public string $type = 'dish';

    public string $price = '0';

    public string $description = '';

    public ?string $existingImage = null;

    public $newImage = null;

    // Options
    public bool $isWeighable = false;

    public bool $excludedFromDiscounts = false;

    public bool $isVisible = true;

    // Tech card
    public string $outputQuantity = '1';

    public ?int $outputUnitId = null;

    public string $cookingInstructions = '';

    /** @var array<int, array{id?: int, kind: 'ingredient'|'semi_finished', ref_id: ?int, unit_id: ?int, preparation_method_id: ?int, quantity: string, loss_percent: string}> */
    public array $items = [];

    /** @var array<int, array{group_id: int, is_required: bool, sort_order: int}> */
    public array $modifierGroups = [];

    public function mount(?int $productId = null): void
    {
        if ($productId) {
            $product = Product::with(['techCard.items', 'modifierGroups'])->findOrFail($productId);

            $this->productId = $product->id;
            $this->name = (string) $product->name;
            $this->categoryId = $product->category_id;
            $this->workshopId = $product->workshop_id;
            $this->taxId = $product->tax_id;
            $this->type = $product->type->value;
            $this->price = (string) $product->price;
            $this->description = (string) $product->description;
            $this->existingImage = $product->image;
            $this->isWeighable = (bool) $product->is_weighable;
            $this->excludedFromDiscounts = (bool) $product->excluded_from_discounts;
            $this->isVisible = (bool) $product->is_visible;

            if ($product->techCard) {
                $this->outputQuantity = (string) $product->techCard->output_quantity;
                $this->outputUnitId = $product->techCard->output_unit_id;
                $this->cookingInstructions = (string) $product->techCard->cooking_instructions;
                $this->items = $product->techCard->items->map(fn ($item) => [
                    'id' => $item->id,
                    'kind' => $item->ingredient_id ? 'ingredient' : 'semi_finished',
                    'ref_id' => $item->ingredient_id ?? $item->semi_finished_id,
                    'unit_id' => $item->unit_id,
                    'preparation_method_id' => $item->preparation_method_id,
                    'quantity' => (string) $item->quantity,
                    'loss_percent' => (string) $item->loss_percent,
                ])->toArray();
            }

            $this->modifierGroups = $product->modifierGroups->map(fn ($g) => [
                'group_id' => $g->id,
                'is_required' => (bool) $g->pivot->is_required,
                'sort_order' => (int) $g->pivot->sort_order,
            ])->toArray();
        }
    }

    public function addItem(): void
    {
        $this->items[] = [
            'kind' => 'ingredient',
            'ref_id' => null,
            'unit_id' => null,
            'preparation_method_id' => null,
            'quantity' => '',
            'loss_percent' => '0',
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function addModifierGroup(int $groupId): void
    {
        if (collect($this->modifierGroups)->contains('group_id', $groupId)) {
            return;
        }

        $this->modifierGroups[] = [
            'group_id' => $groupId,
            'is_required' => false,
            'sort_order' => count($this->modifierGroups),
        ];
    }

    public function removeModifierGroup(int $index): void
    {
        unset($this->modifierGroups[$index]);
        $this->modifierGroups = array_values($this->modifierGroups);
    }

    public function save(SaveDishAction $action): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'categoryId' => 'nullable|exists:categories,id',
            'workshopId' => 'nullable|exists:workshops,id',
            'taxId' => 'nullable|exists:taxes,id',
            'price' => 'required|numeric|min:0',
            'outputQuantity' => 'required|numeric|min:0.001',
            'newImage' => 'nullable|image|max:2048',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.loss_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $imagePath = $this->existingImage;
        if ($this->newImage) {
            $imagePath = $this->newImage->store('products', 'public');
        }

        $product = $action->execute(
            $this->productId ? Product::find($this->productId) : null,
            product_attributes: [
                'organization_id' => auth()->user()->organization_id,
                'category_id' => $this->categoryId,
                'workshop_id' => $this->workshopId,
                'tax_id' => $this->taxId,
                'type' => $this->type,
                'name' => $this->name,
                'description' => $this->description ?: null,
                'image' => $imagePath,
                'price' => (float) $this->price,
                'is_weighable' => $this->isWeighable,
                'excluded_from_discounts' => $this->excludedFromDiscounts,
                'is_visible' => $this->isVisible,
            ],
            techCard: [
                'output_quantity' => (float) $this->outputQuantity,
                'output_unit_id' => $this->outputUnitId,
                'cooking_instructions' => $this->cookingInstructions ?: null,
                'is_active' => true,
            ],
            items: array_map(fn ($row) => [
                'ingredient_id' => $row['kind'] === 'ingredient' ? $row['ref_id'] : null,
                'semi_finished_id' => $row['kind'] === 'semi_finished' ? $row['ref_id'] : null,
                'unit_id' => $row['unit_id'] ?: null,
                'preparation_method_id' => $row['preparation_method_id'] ?: null,
                'quantity' => $row['quantity'],
                'loss_percent' => $row['loss_percent'],
            ], $this->items),
            modifierGroups: $this->modifierGroups,
        );

        $this->productId = $product->id;
        $this->existingImage = $product->image;
        $this->newImage = null;
        $this->dispatch('dish-saved', productId: $product->id);
        session()->flash('success', 'Тех. карта сохранена.');
    }

    #[Computed]
    public function totalCost(): float
    {
        $total = 0.0;
        foreach ($this->items as $row) {
            $qty = (float) ($row['quantity'] ?: 0);
            $loss = (float) ($row['loss_percent'] ?: 0);
            $gross = $qty * (1 + $loss / 100);
            $total += $gross * $this->unitCost($row['kind'] ?? null, $row['ref_id'] ?? null);
        }

        return round($total, 2);
    }

    #[Computed]
    public function markupPercent(): float
    {
        $cost = $this->totalCost;
        $price = (float) ($this->price ?: 0);
        if ($cost <= 0) {
            return 0;
        }

        return round(($price - $cost) / $cost * 100, 2);
    }

    public function lineCost(int $index): float
    {
        $row = $this->items[$index] ?? null;
        if (!$row) {
            return 0;
        }
        $qty = (float) ($row['quantity'] ?: 0);
        $loss = (float) ($row['loss_percent'] ?: 0);
        $gross = $qty * (1 + $loss / 100);

        return round($gross * $this->unitCost($row['kind'] ?? null, $row['ref_id'] ?? null), 2);
    }

    private function unitCost(?string $kind, ?int $refId): float
    {
        if (!$refId) {
            return 0;
        }

        return match ($kind) {
            'ingredient' => (float) (Ingredient::find($refId)?->current_cost ?? 0),
            'semi_finished' => (float) (Product::find($refId)?->cost_price ?? 0),
            default => 0,
        };
    }

    #[Computed]
    public function categories()
    {
        return Category::where('organization_id', auth()->user()->organization_id)
            ->orderBy('name')->get();
    }

    #[Computed]
    public function workshops()
    {
        return Workshop::orderBy('name')->get();
    }

    #[Computed]
    public function taxes()
    {
        return Tax::where('organization_id', auth()->user()->organization_id)->active()->orderBy('name')->get();
    }

    #[Computed]
    public function units()
    {
        return Unit::where('organization_id', auth()->user()->organization_id)->orderBy('name')->get();
    }

    #[Computed]
    public function ingredients()
    {
        return Ingredient::where('organization_id', auth()->user()->organization_id)
            ->active()->with('unit')->orderBy('name')->get();
    }

    #[Computed]
    public function semiFinishedProducts()
    {
        return Product::where('organization_id', auth()->user()->organization_id)
            ->where('type', 'semi_finished')->orderBy('name')->get();
    }

    #[Computed]
    public function preparationMethods()
    {
        return PreparationMethod::where('organization_id', auth()->user()->organization_id)
            ->active()->orderBy('name')->get();
    }

    #[Computed]
    public function modifierGroupOptions()
    {
        $attached = collect($this->modifierGroups)->pluck('group_id')->all();

        return ModifierGroup::where('organization_id', auth()->user()->organization_id)
            ->whereNotIn('id', $attached)
            ->orderBy('name')->get();
    }

    #[Computed]
    public function attachedModifierGroups()
    {
        $ids = collect($this->modifierGroups)->pluck('group_id')->all();

        return ModifierGroup::with('modifiers')->whereIn('id', $ids)->get()->keyBy('id');
    }

    public function render()
    {
        return view('livewire.menu.dish-editor')->layout($this->resolveLayout());
    }
}
