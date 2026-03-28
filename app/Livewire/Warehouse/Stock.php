<?php

declare(strict_types=1);

namespace App\Livewire\Warehouse;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Stock extends Component
{
    use WithPagination;

    #[Url]
    public string $searchQuery = '';

    public bool $showLowStock = false;

    public function updatedSearchQuery(): void
    {
        $this->resetPage();
    }

    public function updatedShowLowStock(): void
    {
        $this->resetPage();
    }

    public function toggleLowStock(): void
    {
        $this->showLowStock = !$this->showLowStock;
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->searchQuery = '';
        $this->showLowStock = false;
    }

    public function render()
    {
        // Запрос к складским данным через ingredients и stocks
        $query = DB::table('ingredients')
            ->leftJoin('stocks', 'ingredients.id', '=', 'stocks.ingredient_id')
            ->select([
                'ingredients.id',
                'ingredients.name',
                'ingredients.unit',
                'ingredients.min_stock',
                DB::raw('COALESCE(stocks.quantity, 0) as current_stock'),
                DB::raw('COALESCE(stocks.updated_at, ingredients.updated_at) as last_updated'),
            ]);

        if ($this->searchQuery !== '') {
            $query->where('ingredients.name', 'like', '%' . $this->searchQuery . '%');
        }

        if ($this->showLowStock) {
            $query->whereRaw('COALESCE(stocks.quantity, 0) <= ingredients.min_stock');
        }

        $query->orderBy('ingredients.name');

        $items = $query->paginate(15);

        return view('livewire.warehouse.stock', [
            'items' => $items,
        ]);
    }
}
