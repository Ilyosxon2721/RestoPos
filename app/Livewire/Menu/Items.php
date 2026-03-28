<?php

declare(strict_types=1);

namespace App\Livewire\Menu;

use App\Domain\Menu\Models\Category;
use App\Domain\Menu\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
final class Items extends Component
{
    use WithPagination;

    public Collection $categories;

    public bool $showModal = false;

    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|integer|exists:categories,id')]
    public ?int $categoryId = null;

    #[Validate('required|numeric|min:0')]
    public string $price = '';

    #[Validate('nullable|numeric|min:0')]
    public string $costPrice = '';

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    #[Validate('nullable|integer|min:0')]
    public ?int $cookingTime = null;

    public bool $isAvailable = true;

    public string $searchQuery = '';

    public ?int $filterCategory = null;

    public function mount(): void
    {
        $this->loadCategories();
    }

    public function loadCategories(): void
    {
        $organizationId = auth()->user()->organization_id;

        $this->categories = Category::query()
            ->where('organization_id', $organizationId)
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function products()
    {
        $organizationId = auth()->user()->organization_id;

        $query = Product::query()
            ->where('organization_id', $organizationId)
            ->with('category');

        if ($this->searchQuery !== '') {
            $query->where('name', 'like', '%' . $this->searchQuery . '%');
        }

        if ($this->filterCategory !== null) {
            $query->where('category_id', $this->filterCategory);
        }

        return $query->orderBy('name')->paginate(20);
    }

    public function updatedSearchQuery(): void
    {
        $this->resetPage();
        unset($this->products);
    }

    public function updatedFilterCategory(): void
    {
        $this->resetPage();
        unset($this->products);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $product = Product::findOrFail($id);

        $this->editingId = $product->id;
        $this->name = $product->name;
        $this->categoryId = $product->category_id;
        $this->price = (string) $product->price;
        $this->costPrice = (string) ($product->cost_price ?? '');
        $this->description = $product->description ?? '';
        $this->cookingTime = $product->cooking_time;
        $this->isAvailable = (bool) $product->is_available;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $organizationId = auth()->user()->organization_id;

        $data = [
            'name' => $this->name,
            'category_id' => $this->categoryId,
            'price' => (float) $this->price,
            'cost_price' => $this->costPrice !== '' ? (float) $this->costPrice : null,
            'description' => $this->description !== '' ? $this->description : null,
            'cooking_time' => $this->cookingTime,
            'is_available' => $this->isAvailable,
            'organization_id' => $organizationId,
        ];

        if ($this->editingId) {
            $product = Product::findOrFail($this->editingId);
            $product->update($data);
        } else {
            Product::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        unset($this->products);
    }

    public function delete(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->delete();

        unset($this->products);
    }

    public function toggleAvailability(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update([
            'is_available' => !$product->is_available,
        ]);

        unset($this->products);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->categoryId = null;
        $this->price = '';
        $this->costPrice = '';
        $this->description = '';
        $this->cookingTime = null;
        $this->isAvailable = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.menu.items');
    }
}
