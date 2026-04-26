<?php

declare(strict_types=1);

namespace App\Livewire\Menu;

use App\Domain\Menu\Models\Product;
use App\Domain\Warehouse\Models\Ingredient;
use App\Domain\Warehouse\Models\TechCard;
use App\Domain\Warehouse\Models\TechCardItem;
use App\Support\Traits\ResolvesLayout;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

final class TechCards extends Component
{
    use ResolvesLayout, WithPagination;

    public string $search = '';

    // Модалка создания/редактирования
    public bool $showModal = false;

    public ?int $editingId = null;

    public ?int $productId = null;

    public string $outputQuantity = '1';

    public string $description = '';

    public string $cookingInstructions = '';

    public bool $isActive = true;

    // Позиции тех. карты
    public array $cardItems = [];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'productId', 'outputQuantity', 'description', 'cookingInstructions', 'isActive', 'cardItems']);
        $this->outputQuantity = '1';
        $this->isActive = true;
        $this->cardItems = [];
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $techCard = TechCard::with('items.ingredient.unit')->findOrFail($id);

        $this->editingId = $techCard->id;
        $this->productId = $techCard->product_id;
        $this->outputQuantity = (string) $techCard->output_quantity;
        $this->description = $techCard->description ?? '';
        $this->cookingInstructions = $techCard->cooking_instructions ?? '';
        $this->isActive = $techCard->is_active;

        $this->cardItems = $techCard->items->map(fn (TechCardItem $item) => [
            'ingredient_id' => $item->ingredient_id,
            'quantity' => (string) $item->quantity,
            'loss_percent' => (string) $item->loss_percent,
        ])->toArray();

        $this->showModal = true;
    }

    public function addItem(): void
    {
        $this->cardItems[] = [
            'ingredient_id' => null,
            'quantity' => '',
            'loss_percent' => '0',
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->cardItems[$index]);
        $this->cardItems = array_values($this->cardItems);
    }

    public function save(): void
    {
        $this->validate([
            'productId' => 'required|exists:products,id',
            'outputQuantity' => 'required|numeric|min:0.001',
            'description' => 'nullable|string|max:2000',
            'cookingInstructions' => 'nullable|string|max:5000',
            'cardItems' => 'required|array|min:1',
            'cardItems.*.ingredient_id' => 'required|exists:ingredients,id',
            'cardItems.*.quantity' => 'required|numeric|min:0.0001',
            'cardItems.*.loss_percent' => 'nullable|numeric|min:0|max:100',
        ], [
            'cardItems.required' => 'Добавьте хотя бы один ингредиент.',
            'cardItems.min' => 'Добавьте хотя бы один ингредиент.',
            'cardItems.*.ingredient_id.required' => 'Выберите ингредиент.',
            'cardItems.*.quantity.required' => 'Укажите количество.',
        ]);

        DB::transaction(function () {
            $data = [
                'product_id' => $this->productId,
                'output_quantity' => (float) $this->outputQuantity,
                'description' => $this->description ?: null,
                'cooking_instructions' => $this->cookingInstructions ?: null,
                'is_active' => $this->isActive,
            ];

            if ($this->editingId) {
                $techCard = TechCard::findOrFail($this->editingId);
                $techCard->update($data);
                $techCard->items()->delete();
            } else {
                $techCard = TechCard::create($data);
            }

            foreach ($this->cardItems as $index => $item) {
                $techCard->items()->create([
                    'ingredient_id' => $item['ingredient_id'],
                    'quantity' => (float) $item['quantity'],
                    'loss_percent' => (float) ($item['loss_percent'] ?: 0),
                    'sort_order' => $index,
                ]);
            }
        });

        $this->showModal = false;
    }

    public function toggleActive(int $id): void
    {
        $techCard = TechCard::findOrFail($id);
        $techCard->update(['is_active' => !$techCard->is_active]);
    }

    public function deleteTechCard(int $id): void
    {
        TechCard::findOrFail($id)->delete();
    }

    #[Computed]
    public function products()
    {
        $orgId = auth()->user()->organization_id;

        return Product::where('organization_id', $orgId)->orderBy('name')->get();
    }

    #[Computed]
    public function ingredients()
    {
        $orgId = auth()->user()->organization_id;

        return Ingredient::where('organization_id', $orgId)->active()->with('unit')->orderBy('name')->get();
    }

    public function render()
    {
        $orgId = auth()->user()->organization_id;

        $techCards = TechCard::query()
            ->whereHas('product', fn ($q) => $q->where('organization_id', $orgId))
            ->when($this->search, fn ($q) => $q->whereHas('product', fn ($q2) => $q2->where('name', 'like', "%{$this->search}%")))
            ->with(['product', 'items.ingredient.unit'])
            ->latest()
            ->paginate(20);

        return view('livewire.menu.tech-cards', compact('techCards'))
            ->layout($this->resolveLayout());
    }
}
