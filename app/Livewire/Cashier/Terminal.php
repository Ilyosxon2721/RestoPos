<?php

declare(strict_types=1);

namespace App\Livewire\Cashier;

use App\Domain\Floor\Models\Hall;
use App\Domain\Floor\Models\Table;
use App\Domain\Menu\Models\Category;
use App\Domain\Menu\Models\Product;
use App\Domain\Order\Models\Order;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

#[Layout('components.layouts.cashier')]
class Terminal extends Component
{
    public ?int $selectedTable = null;
    public ?int $selectedCategory = null;
    public ?int $selectedHall = null;
    public array $cart = [];
    public string $searchProduct = '';

    public function mount(): void
    {
        $branchId = session('current_branch_id');
        $this->selectedHall = Hall::where('branch_id', $branchId)->first()?->id;
    }

    public function selectTable(int $tableId): void
    {
        $this->selectedTable = $tableId;
        $this->cart = [];
    }

    public function selectCategory(?int $categoryId): void
    {
        $this->selectedCategory = $categoryId;
    }

    public function selectHall(int $hallId): void
    {
        $this->selectedHall = $hallId;
        $this->selectedTable = null;
        $this->cart = [];
    }

    public function addToCart(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product) return;

        foreach ($this->cart as &$item) {
            if ($item['product_id'] === $productId) {
                $item['quantity']++;
                $item['subtotal'] = $item['quantity'] * $item['price'];
                return;
            }
        }

        $this->cart[] = [
            'product_id' => $productId,
            'name' => $product->name,
            'price' => (float) $product->price,
            'quantity' => 1,
            'subtotal' => (float) $product->price,
        ];
    }

    public function updateQuantity(int $index, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeFromCart($index);
            return;
        }
        $this->cart[$index]['quantity'] = $quantity;
        $this->cart[$index]['subtotal'] = $this->cart[$index]['price'] * $quantity;
    }

    public function removeFromCart(int $index): void
    {
        array_splice($this->cart, $index, 1);
    }

    public function clearCart(): void
    {
        $this->cart = [];
    }

    #[Computed]
    public function cartTotal(): float
    {
        return array_sum(array_column($this->cart, 'subtotal'));
    }

    #[Computed]
    public function halls()
    {
        $branchId = session('current_branch_id');
        return Hall::where('branch_id', $branchId)->get();
    }

    #[Computed]
    public function tables()
    {
        return $this->selectedHall ? Table::where('hall_id', $this->selectedHall)->get() : collect();
    }

    #[Computed]
    public function categories()
    {
        return Category::where('organization_id', auth()->user()->organization_id)
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function products()
    {
        return Product::where('organization_id', auth()->user()->organization_id)
            ->where('is_available', true)
            ->when($this->selectedCategory, fn($q, $id) => $q->where('category_id', $id))
            ->when($this->searchProduct, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->get();
    }

    public function render()
    {
        return view('livewire.cashier.terminal');
    }
}
