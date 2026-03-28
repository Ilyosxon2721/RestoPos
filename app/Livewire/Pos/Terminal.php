<?php

declare(strict_types=1);

namespace App\Livewire\Pos;

use App\Domain\Floor\Models\Hall;
use App\Domain\Floor\Models\Table;
use App\Domain\Menu\Models\Category;
use App\Domain\Menu\Models\Product;
use App\Domain\Order\Actions\AddOrderItemAction;
use App\Domain\Order\Actions\CreateOrderAction;
use App\Domain\Order\Actions\SendToKitchenAction;
use App\Domain\Order\Models\Order;
use App\Support\Enums\OrderItemStatus;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\TableStatus;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Terminal extends Component
{
    public ?int $selectedHall = null;

    public ?int $selectedTable = null;

    public ?int $currentOrderId = null;

    public string $searchQuery = '';

    public ?int $selectedCategory = null;

    /** @var array<int, array{product_id: int, name: string, price: float, quantity: int}> */
    public array $cart = [];

    public bool $paymentModal = false;

    public string $paymentMethod = 'cash';

    public float $paymentAmount = 0;

    public function mount(): void
    {
        $firstHall = Hall::with('tables')->first();

        if ($firstHall) {
            $this->selectedHall = $firstHall->id;
        }
    }

    #[Computed]
    public function halls(): Collection
    {
        return Hall::with('tables')->orderBy('name')->get();
    }

    #[Computed]
    public function tables(): Collection
    {
        if (! $this->selectedHall) {
            return collect();
        }

        return Table::where('hall_id', $this->selectedHall)
            ->orderBy('number')
            ->get();
    }

    #[Computed]
    public function categories(): Collection
    {
        return Category::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function products(): Collection
    {
        $query = Product::where('is_active', true);

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        if ($this->searchQuery !== '') {
            $query->where('name', 'like', '%' . $this->searchQuery . '%');
        }

        return $query->orderBy('name')->get();
    }

    #[Computed]
    public function currentOrder(): ?Order
    {
        if (! $this->currentOrderId) {
            return null;
        }

        return Order::with('items.product')->find($this->currentOrderId);
    }

    #[Computed]
    public function subtotal(): float
    {
        return (float) collect($this->cart)->sum(fn (array $item): float => $item['price'] * $item['quantity']);
    }

    #[Computed]
    public function discount(): float
    {
        return 0.0;
    }

    #[Computed]
    public function total(): float
    {
        return $this->subtotal - $this->discount;
    }

    #[Computed]
    public function change(): float
    {
        if ($this->paymentMethod !== 'cash') {
            return 0.0;
        }

        return max(0, $this->paymentAmount - $this->total);
    }

    public function selectHall(int $hallId): void
    {
        $this->selectedHall = $hallId;
        $this->selectedTable = null;
        $this->currentOrderId = null;
        $this->cart = [];
    }

    public function selectTable(int $tableId): void
    {
        $this->selectedTable = $tableId;

        // Загружаем существующий открытый заказ для стола, если есть
        $existingOrder = Order::where('table_id', $tableId)
            ->whereIn('status', [OrderStatus::Open, OrderStatus::InProgress])
            ->with('items.product')
            ->first();

        if ($existingOrder) {
            $this->currentOrderId = $existingOrder->id;
            $this->cart = $existingOrder->items->map(fn ($item): array => [
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'price' => (float) $item->price,
                'quantity' => $item->quantity,
                'order_item_id' => $item->id,
                'status' => $item->status->value,
            ])->toArray();
        } else {
            $this->currentOrderId = null;
            $this->cart = [];
        }
    }

    public function selectCategory(?int $categoryId): void
    {
        $this->selectedCategory = $categoryId;
    }

    public function addToCart(int $productId): void
    {
        $product = Product::find($productId);

        if (! $product) {
            return;
        }

        // Проверяем, есть ли уже в корзине
        foreach ($this->cart as $index => $item) {
            if ($item['product_id'] === $productId && ! isset($item['order_item_id'])) {
                $this->cart[$index]['quantity']++;

                return;
            }
        }

        $this->cart[] = [
            'product_id' => $productId,
            'name' => $product->name,
            'price' => (float) $product->price,
            'quantity' => 1,
        ];
    }

    public function updateQuantity(int $index, int $quantity): void
    {
        if (! isset($this->cart[$index])) {
            return;
        }

        if ($quantity <= 0) {
            $this->removeFromCart($index);

            return;
        }

        $this->cart[$index]['quantity'] = $quantity;
    }

    public function removeFromCart(int $index): void
    {
        if (! isset($this->cart[$index])) {
            return;
        }

        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function createOrder(): void
    {
        if (! $this->selectedTable || empty($this->cart)) {
            return;
        }

        if ($this->currentOrderId) {
            // Добавляем новые позиции к существующему заказу
            $addItemAction = app(AddOrderItemAction::class);
            $order = Order::find($this->currentOrderId);

            foreach ($this->cart as $item) {
                if (! isset($item['order_item_id'])) {
                    $addItemAction->execute($order, [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                }
            }
        } else {
            // Создаём новый заказ
            $action = app(CreateOrderAction::class);

            $items = collect($this->cart)->map(fn (array $item): array => [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ])->toArray();

            $order = $action->execute([
                'table_id' => $this->selectedTable,
                'items' => $items,
                'user_id' => auth()->id(),
            ]);

            $this->currentOrderId = $order->id;
        }

        // Перезагружаем корзину из заказа
        $this->selectTable($this->selectedTable);

        $this->dispatch('order-created');
    }

    public function sendToKitchen(): void
    {
        if (! $this->currentOrderId) {
            $this->createOrder();
        }

        if (! $this->currentOrderId) {
            return;
        }

        $action = app(SendToKitchenAction::class);
        $order = Order::find($this->currentOrderId);

        $action->execute($order);

        // Перезагружаем корзину из заказа
        $this->selectTable($this->selectedTable);

        $this->dispatch('sent-to-kitchen');
    }

    public function openPayment(): void
    {
        if (empty($this->cart)) {
            return;
        }

        if (! $this->currentOrderId) {
            $this->createOrder();
        }

        $this->paymentAmount = $this->total;
        $this->paymentMethod = 'cash';
        $this->paymentModal = true;
    }

    public function processPayment(): void
    {
        if (! $this->currentOrderId) {
            return;
        }

        $order = Order::find($this->currentOrderId);

        if (! $order) {
            return;
        }

        $order->update([
            'status' => OrderStatus::Paid,
            'payment_method' => $this->paymentMethod,
            'paid_amount' => $this->paymentAmount,
            'paid_at' => now(),
        ]);

        // Освобождаем стол
        $table = Table::find($this->selectedTable);
        if ($table) {
            $table->update(['status' => TableStatus::Free]);
        }

        $this->paymentModal = false;
        $this->currentOrderId = null;
        $this->cart = [];
        $this->paymentAmount = 0;

        $this->dispatch('payment-processed');
    }

    public function clearCart(): void
    {
        $this->cart = [];

        if (! $this->currentOrderId) {
            return;
        }

        // Если заказ ещё не отправлен на кухню, можно его отменить
        $order = Order::find($this->currentOrderId);

        if ($order && $order->status === OrderStatus::Open) {
            $order->update(['status' => OrderStatus::Cancelled]);
            $this->currentOrderId = null;

            $table = Table::find($this->selectedTable);
            if ($table) {
                $table->update(['status' => TableStatus::Free]);
            }
        }
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.pos.terminal');
    }
}
