<?php

declare(strict_types=1);

namespace App\Livewire\Warehouse;

use App\Support\Traits\ResolvesLayout;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Stock extends Component
{
    use ResolvesLayout, WithPagination;

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
        // Запрос к складским данным через ingredients и stock
        $query = DB::table('ingredients')
            ->leftJoin('stock', 'ingredients.id', '=', 'stock.ingredient_id')
            ->leftJoin('units', 'ingredients.unit_id', '=', 'units.id')
            ->select([
                'ingredients.id',
                'ingredients.name',
                'units.short_name as unit',
                'ingredients.min_stock',
                DB::raw('COALESCE(stock.quantity, 0) as current_stock'),
                DB::raw('COALESCE(stock.updated_at, ingredients.updated_at) as last_updated'),
            ]);

        if ($this->searchQuery !== '') {
            $query->where('ingredients.name', 'like', '%'.$this->searchQuery.'%');
        }

        if ($this->showLowStock) {
            $query->whereRaw('COALESCE(stock.quantity, 0) <= ingredients.min_stock');
        }

        $query->orderBy('ingredients.name');

        $items = $query->paginate(15);

        return view('livewire.warehouse.stock', [
            'items' => $items,
        ])->layout($this->resolveLayout());
    }
}
