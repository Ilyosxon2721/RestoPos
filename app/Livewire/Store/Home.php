<?php

declare(strict_types=1);

namespace App\Livewire\Store;

use App\Domain\Menu\Models\Category;
use App\Domain\Menu\Models\Product;
use App\Domain\Store\Models\StoreBanner;
use App\Domain\Store\Models\StoreSettings;
use Livewire\Attributes\On;
use Livewire\Component;

final class Home extends Component
{
    public StoreSettings $store;
    public string $search = '';
    public ?int $selectedCategory = null;

    public function mount(string $slug): void
    {
        $this->store = StoreSettings::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    #[On('store-search')]
    public function updateSearch(string $query): void
    {
        $this->search = $query;
    }

    public function selectCategory(?int $categoryId): void
    {
        $this->selectedCategory = $categoryId;
    }

    public function getBannersProperty()
    {
        return StoreBanner::query()
            ->withoutGlobalScope('organization')
            ->where('organization_id', $this->store->organization_id)
            ->active()
            ->ordered()
            ->get();
    }

    public function getCategoriesProperty()
    {
        return Category::query()
            ->withoutGlobalScope('organization')
            ->where('organization_id', $this->store->organization_id)
            ->where('is_visible', true)
            ->whereNull('parent_id')
            ->with(['children' => fn($q) => $q->where('is_visible', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function getDrinkOfDayProperty()
    {
        if (!$this->store->drink_of_day_product_id) {
            return null;
        }

        return Product::query()
            ->withoutGlobalScope('organization')
            ->where('id', $this->store->drink_of_day_product_id)
            ->where('is_available', true)
            ->where('is_visible', true)
            ->first();
    }

    public function getProductsProperty()
    {
        $query = Product::query()
            ->withoutGlobalScope('organization')
            ->where('organization_id', $this->store->organization_id)
            ->where('is_visible', true)
            ->where('is_available', true)
            ->where('in_stop_list', false)
            ->with('category');

        if ($this->search !== '') {
            $query->where('name', 'like', "%{$this->search}%");
        }

        if ($this->selectedCategory !== null) {
            // Включаем дочерние категории
            $childIds = Category::withoutGlobalScope('organization')
                ->where('parent_id', $this->selectedCategory)
                ->pluck('id')
                ->push($this->selectedCategory)
                ->toArray();
            $query->whereIn('category_id', $childIds);
        }

        return $query->orderBy('sort_order')->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.store.home')
            ->layout('components.layouts.store', ['store' => $this->store]);
    }
}
