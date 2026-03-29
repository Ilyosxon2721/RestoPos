<?php

declare(strict_types=1);

namespace App\Livewire\Menu;

use App\Domain\Menu\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Validate;
use App\Support\Traits\ResolvesLayout;
use Livewire\Component;

final class Categories extends Component
{
    use ResolvesLayout;

    public Collection $categories;

    public bool $showModal = false;

    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:7')]
    public string $color = '#3B82F6';

    #[Validate('nullable|integer|exists:categories,id')]
    public ?int $parentId = null;

    #[Validate('integer|min:0')]
    public int $sortOrder = 0;

    public bool $isActive = true;

    public function mount(): void
    {
        $this->loadCategories();
    }

    public function loadCategories(): void
    {
        $organizationId = auth()->user()->organization_id;

        $this->categories = Category::query()
            ->where('organization_id', $organizationId)
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $category = Category::findOrFail($id);

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->color = $category->color ?? '#3B82F6';
        $this->parentId = $category->parent_id;
        $this->sortOrder = $category->sort_order ?? 0;
        $this->isActive = (bool) $category->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $organizationId = auth()->user()->organization_id;

        $data = [
            'name' => $this->name,
            'color' => $this->color,
            'parent_id' => $this->parentId,
            'sort_order' => $this->sortOrder,
            'is_active' => $this->isActive,
            'organization_id' => $organizationId,
        ];

        if ($this->editingId) {
            $category = Category::findOrFail($this->editingId);
            $category->update($data);
        } else {
            Category::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        $this->loadCategories();
    }

    public function delete(int $id): void
    {
        $category = Category::findOrFail($id);
        $category->delete();

        $this->loadCategories();
    }

    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            Category::where('id', $id)->update(['sort_order' => $index]);
        }

        $this->loadCategories();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->color = '#3B82F6';
        $this->parentId = null;
        $this->sortOrder = 0;
        $this->isActive = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.menu.categories')
            ->layout($this->resolveLayout());
    }

}
