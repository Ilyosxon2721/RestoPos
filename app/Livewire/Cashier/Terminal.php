<?php

declare(strict_types=1);

namespace App\Livewire\Cashier;

use App\Domain\Auth\Models\User;
use App\Domain\Floor\Models\Hall;
use App\Domain\Floor\Models\Table;
use App\Domain\Menu\Models\Category;
use App\Domain\Menu\Models\Product;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\OrderItem;
use App\Domain\Staff\Models\Employee;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

#[Layout('components.layouts.cashier')]
class Terminal extends Component
{
    // PIN-авторизация оператора
    public bool $pinLocked = true;
    public string $pin = '';
    public ?int $operatorId = null;
    public ?string $operatorName = null;

    // Состояние терминала
    public ?int $selectedTable = null;
    public ?string $selectedTableName = null;
    public ?int $selectedHall = null;
    public ?int $selectedCategory = null;
    public string $searchProduct = '';
    public array $cart = [];
    public string $orderType = 'dine_in'; // dine_in, takeaway, delivery
    public ?int $guestsCount = null;

    // Модальные окна
    public bool $showTableModal = false;
    public bool $showPaymentModal = false;
    public bool $showDiscountModal = false;
    public bool $showOrdersModal = false;

    // Скидка
    public float $discountPercent = 0;
    public float $discountAmount = 0;
    public string $discountType = 'percent'; // percent, fixed

    // Оплата
    public string $paymentMethod = 'cash'; // cash, card, mixed
    public string $cashReceived = '';

    // Комментарий к заказу
    public string $orderComment = '';

    // Текущий открытый заказ
    public ?int $currentOrderId = null;

    public function mount(): void
    {
        $user = auth()->user();
        $branchId = $user->employee?->branch_id ?? session('current_branch_id');

        if ($branchId) {
            session(['current_branch_id' => $branchId]);
            $this->selectedHall = Hall::where('branch_id', $branchId)->first()?->id;
        }

        // Если оператор уже сохранён в сессии — восстановить
        $sessionOperator = session('pos_operator');
        if ($sessionOperator) {
            $this->operatorId = $sessionOperator['id'];
            $this->operatorName = $sessionOperator['name'];
            $this->pinLocked = false;
        }
    }

    // === PIN-авторизация оператора ===

    public function appendPin(string $digit): void
    {
        if (mb_strlen($this->pin) < 4) {
            $this->pin .= $digit;
        }

        if (mb_strlen($this->pin) === 4) {
            $this->verifyPin();
        }
    }

    public function clearPin(): void
    {
        $this->pin = '';
        $this->resetErrorBag('pin');
    }

    public function backspacePin(): void
    {
        $this->pin = mb_substr($this->pin, 0, -1);
    }

    public function verifyPin(): void
    {
        $branchId = session('current_branch_id');

        // Ищем пользователя по PIN в текущем филиале с ролью кассира/бармена
        $user = User::where('pin_code', $this->pin)
            ->where('is_active', true)
            ->whereHas('employee', fn($q) => $q->where('branch_id', $branchId))
            ->whereHas('roles', fn($q) => $q->whereIn('slug', ['cashier', 'bartender']))
            ->first();

        if (! $user) {
            $this->addError('pin', 'Неверный PIN-код или нет доступа.');
            $this->pin = '';
            return;
        }

        $this->operatorId = $user->id;
        $this->operatorName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->email;
        $this->pinLocked = false;
        $this->pin = '';

        session(['pos_operator' => ['id' => $user->id, 'name' => $this->operatorName]]);

        $this->dispatch('notify', message: "Оператор: {$this->operatorName}", type: 'success');
    }

    public function lockTerminal(): void
    {
        $this->pinLocked = true;
        $this->pin = '';
        $this->operatorId = null;
        $this->operatorName = null;
        session()->forget('pos_operator');
    }

    // === Выбор стола ===

    public function openTableModal(): void
    {
        $this->showTableModal = true;
    }

    public function selectHall(int $hallId): void
    {
        $this->selectedHall = $hallId;
    }

    public function selectTable(int $tableId): void
    {
        $table = Table::find($tableId);
        if (! $table) return;

        $this->selectedTable = $tableId;
        $this->selectedTableName = "Стол {$table->name}";
        $this->showTableModal = false;
        $this->orderType = 'dine_in';

        // Загружаем существующий открытый заказ для этого стола
        $this->loadTableOrder($tableId);
    }

    public function setOrderType(string $type): void
    {
        $this->orderType = $type;
        if ($type !== 'dine_in') {
            $this->selectedTable = null;
            $this->selectedTableName = match ($type) {
                'takeaway' => 'С собой',
                'delivery' => 'Доставка',
                default => null,
            };
        }
        $this->showTableModal = false;
    }

    private function loadTableOrder(int $tableId): void
    {
        $order = Order::where('table_id', $tableId)
            ->whereIn('status', ['new', 'accepted', 'preparing', 'ready'])
            ->with('items.product')
            ->first();

        if ($order) {
            $this->currentOrderId = $order->id;
            $this->cart = [];
            foreach ($order->items as $item) {
                if ($item->status === 'cancelled') continue;
                $this->cart[] = [
                    'product_id' => $item->product_id,
                    'order_item_id' => $item->id,
                    'name' => $item->name,
                    'price' => (float) $item->unit_price,
                    'quantity' => (int) $item->quantity,
                    'subtotal' => (float) $item->total_price,
                    'comment' => $item->comment ?? '',
                    'sent' => in_array($item->status, ['sent', 'preparing', 'ready', 'served']),
                ];
            }
        } else {
            $this->currentOrderId = null;
            $this->cart = [];
        }
    }

    // === Категории и товары ===

    public function selectCategory(?int $categoryId): void
    {
        $this->selectedCategory = $categoryId;
        $this->searchProduct = '';
    }

    public function addToCart(int $productId): void
    {
        $product = Product::find($productId);
        if (! $product) return;

        // Ищем в корзине неотправленный элемент
        foreach ($this->cart as $index => &$item) {
            if ($item['product_id'] === $productId && ! ($item['sent'] ?? false)) {
                $item['quantity']++;
                $item['subtotal'] = $item['quantity'] * $item['price'];
                return;
            }
        }

        $this->cart[] = [
            'product_id' => $productId,
            'order_item_id' => null,
            'name' => $product->name,
            'price' => (float) $product->price,
            'quantity' => 1,
            'subtotal' => (float) $product->price,
            'comment' => '',
            'sent' => false,
        ];
    }

    public function incrementItem(int $index): void
    {
        if (isset($this->cart[$index])) {
            $this->cart[$index]['quantity']++;
            $this->cart[$index]['subtotal'] = $this->cart[$index]['quantity'] * $this->cart[$index]['price'];
        }
    }

    public function decrementItem(int $index): void
    {
        if (isset($this->cart[$index])) {
            if ($this->cart[$index]['quantity'] > 1) {
                $this->cart[$index]['quantity']--;
                $this->cart[$index]['subtotal'] = $this->cart[$index]['quantity'] * $this->cart[$index]['price'];
            } else {
                $this->removeFromCart($index);
            }
        }
    }

    public function removeFromCart(int $index): void
    {
        if (isset($this->cart[$index])) {
            // Если позиция уже отправлена на кухню — нельзя удалить просто так
            if ($this->cart[$index]['sent'] ?? false) {
                return;
            }
            array_splice($this->cart, $index, 1);
        }
    }

    public function clearCart(): void
    {
        // Убираем только неотправленные позиции
        $this->cart = array_values(array_filter($this->cart, fn($item) => $item['sent'] ?? false));
        if (empty($this->cart)) {
            $this->currentOrderId = null;
        }
    }

    // === Скидка ===

    public function openDiscountModal(): void
    {
        $this->showDiscountModal = true;
    }

    public function applyDiscount(): void
    {
        if ($this->discountType === 'percent') {
            $this->discountPercent = min(100, max(0, (float) $this->discountPercent));
            $this->discountAmount = 0;
        } else {
            $this->discountAmount = max(0, (float) $this->discountAmount);
            $this->discountPercent = 0;
        }
        $this->showDiscountModal = false;
    }

    public function removeDiscount(): void
    {
        $this->discountPercent = 0;
        $this->discountAmount = 0;
    }

    // === Отправка на кухню ===

    public function sendToKitchen(): void
    {
        if (empty($this->cart)) return;

        $branchId = session('current_branch_id');

        // Определяем employee_id оператора (кассира по PIN)
        $operatorEmployeeId = null;
        if ($this->operatorId) {
            $operatorEmployeeId = Employee::where('user_id', $this->operatorId)
                ->where('branch_id', $branchId)
                ->value('id');
        }

        // Создаём или обновляем заказ
        if ($this->currentOrderId) {
            $order = Order::find($this->currentOrderId);
        } else {
            $order = Order::create([
                'branch_id' => $branchId,
                'table_id' => $this->selectedTable,
                'waiter_id' => $operatorEmployeeId,
                'order_number' => Order::generateOrderNumber($branchId),
                'type' => $this->orderType,
                'source' => 'pos',
                'status' => 'new',
                'payment_status' => 'unpaid',
                'guests_count' => $this->guestsCount ?? 1,
                'opened_at' => now(),
            ]);
            $this->currentOrderId = $order->id;
        }

        // Добавляем новые позиции
        foreach ($this->cart as $index => &$item) {
            if ($item['sent']) continue;

            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'total_price' => $item['subtotal'],
                'status' => 'sent',
                'sent_to_kitchen_at' => now(),
                'comment' => $item['comment'] ?: null,
            ]);

            $item['order_item_id'] = $orderItem->id;
            $item['sent'] = true;
        }

        // Пересчитываем итоги заказа
        $order->calculateTotals();
        $order->save();

        $this->dispatch('notify', message: 'Заказ отправлен на кухню', type: 'success');
    }

    // === Оплата ===

    public function openPaymentModal(): void
    {
        if (empty($this->cart)) return;
        $this->cashReceived = '';
        $this->paymentMethod = 'cash';
        $this->showPaymentModal = true;
    }

    public function processPayment(): void
    {
        // Сначала отправляем неотправленные на кухню
        $hasNewItems = false;
        foreach ($this->cart as $item) {
            if (! $item['sent']) {
                $hasNewItems = true;
                break;
            }
        }
        if ($hasNewItems) {
            $this->sendToKitchen();
        }

        if (! $this->currentOrderId) return;

        $order = Order::find($this->currentOrderId);
        if (! $order) return;

        $total = $this->totalWithDiscount;

        // Применяем скидку
        if ($this->discountPercent > 0) {
            $order->discount_percent = $this->discountPercent;
            $order->discount_amount = round($order->subtotal * $this->discountPercent / 100);
        } elseif ($this->discountAmount > 0) {
            $order->discount_amount = $this->discountAmount;
        }

        // Создаём платёж
        $order->payments()->create([
            'payment_method_id' => null,
            'cash_shift_id' => null,
            'user_id' => auth()->id(),
            'amount' => $total,
            'change_amount' => $this->paymentMethod === 'cash' ? max(0, ((float) $this->cashReceived) - $total) : 0,
            'status' => 'completed',
            'paid_at' => now(),
            'payment_data' => ['method' => $this->paymentMethod],
        ]);

        $order->status = 'completed';
        $order->payment_status = 'paid';
        $order->closed_at = now();
        $order->calculateTotals();
        $order->save();

        // Сброс
        $this->showPaymentModal = false;
        $this->cart = [];
        $this->currentOrderId = null;
        $this->selectedTable = null;
        $this->selectedTableName = null;
        $this->discountPercent = 0;
        $this->discountAmount = 0;
        $this->orderComment = '';

        $this->dispatch('notify', message: 'Оплата проведена', type: 'success');
    }

    // === Вычисляемые свойства ===

    #[Computed]
    public function cartTotal(): float
    {
        return array_sum(array_column($this->cart, 'subtotal'));
    }

    #[Computed]
    public function totalDiscount(): float
    {
        if ($this->discountPercent > 0) {
            return round($this->cartTotal * $this->discountPercent / 100);
        }
        return $this->discountAmount;
    }

    #[Computed]
    public function totalWithDiscount(): float
    {
        return max(0, $this->cartTotal - $this->totalDiscount);
    }

    #[Computed]
    public function cartItemsCount(): int
    {
        return array_sum(array_column($this->cart, 'quantity'));
    }

    #[Computed]
    public function changeAmount(): float
    {
        if ($this->paymentMethod !== 'cash' || ! $this->cashReceived) return 0;
        return max(0, ((float) $this->cashReceived) - $this->totalWithDiscount);
    }

    #[Computed]
    public function halls()
    {
        $branchId = session('current_branch_id');
        return $branchId ? Hall::where('branch_id', $branchId)->get() : collect();
    }

    #[Computed]
    public function tables()
    {
        if (! $this->selectedHall) return collect();

        return Table::where('hall_id', $this->selectedHall)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($table) {
                $table->has_order = Order::where('table_id', $table->id)
                    ->whereIn('status', ['new', 'accepted', 'preparing', 'ready'])
                    ->exists();
                return $table;
            });
    }

    #[Computed]
    public function categories()
    {
        $orgId = auth()->user()?->organization_id;
        if (! $orgId) return collect();

        return Category::withoutGlobalScopes()
            ->where('organization_id', $orgId)
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function products()
    {
        $orgId = auth()->user()?->organization_id;
        if (! $orgId) return collect();

        return Product::withoutGlobalScopes()
            ->where('organization_id', $orgId)
            ->where('is_available', true)
            ->where('in_stop_list', false)
            ->when($this->selectedCategory, fn($q, $id) => $q->where('category_id', $id))
            ->when($this->searchProduct, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function openOrders()
    {
        $branchId = session('current_branch_id');
        if (! $branchId) return collect();

        return Order::where('branch_id', $branchId)
            ->whereIn('status', ['new', 'accepted', 'preparing', 'ready'])
            ->with(['items', 'table'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    }

    public function loadOrder(int $orderId): void
    {
        $order = Order::with('items.product', 'table')->find($orderId);
        if (! $order) return;

        $this->currentOrderId = $order->id;
        $this->selectedTable = $order->table_id;
        $this->selectedTableName = $order->table ? "Стол {$order->table->name}" : 'Без стола';
        $this->orderType = $order->type ?? 'dine_in';

        $this->cart = [];
        foreach ($order->items as $item) {
            if ($item->status === 'cancelled') continue;
            $this->cart[] = [
                'product_id' => $item->product_id,
                'order_item_id' => $item->id,
                'name' => $item->name,
                'price' => (float) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'subtotal' => (float) $item->total_price,
                'comment' => $item->comment ?? '',
                'sent' => in_array($item->status, ['sent', 'preparing', 'ready', 'served']),
            ];
        }

        $this->showOrdersModal = false;
    }

    public function render()
    {
        return view('livewire.cashier.terminal');
    }
}
