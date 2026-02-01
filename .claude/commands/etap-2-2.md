# Этап 2.2: Модуль Orders

Создай полный модуль заказов — ядро POS-системы.

## Структура

```
app/Domain/Orders/
├── Actions/
│   ├── CreateOrderAction.php
│   ├── AddOrderItemAction.php
│   ├── RemoveOrderItemAction.php
│   ├── ApplyDiscountAction.php
│   ├── ProcessPaymentAction.php
│   ├── CancelOrderAction.php
│   └── SplitBillAction.php
├── DTOs/
│   ├── OrderDTO.php
│   ├── OrderItemDTO.php
│   └── PaymentDTO.php
├── Events/
│   ├── OrderCreated.php
│   ├── OrderPaid.php
│   └── OrderCancelled.php
└── Services/
    ├── OrderService.php
    └── OrderNumberGenerator.php
```

## Задачи:

### 1. Модель Order

Создай `app/Models/Order.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Organization\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'uuid',
        'branch_id',
        'table_id',
        'customer_id',
        'waiter_id',
        'order_number',
        'type',
        'status',
        'guests_count',
        'subtotal',
        'discount_amount',
        'discount_reason',
        'service_charge',
        'tax_amount',
        'total',
        'notes',
        'confirmed_at',
        'ready_at',
        'served_at',
        'paid_at',
        'cancelled_at',
        'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'service_charge' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'guests_count' => 'integer',
            'confirmed_at' => 'datetime',
            'ready_at' => 'datetime',
            'served_at' => 'datetime',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->uuid = $model->uuid ?? (string) Str::uuid();
        });
    }

    // Relationships
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function waiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waiter_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['paid', 'cancelled']);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Helpers
    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum(\DB::raw('price * quantity'));
        $itemDiscounts = $this->items()->sum('discount');
        
        $this->subtotal = $subtotal;
        $this->total = $subtotal - $this->discount_amount - $itemDiscounts + $this->service_charge + $this->tax_amount;
        $this->save();
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function canBeCancelled(): bool
    {
        return !in_array($this->status, ['paid', 'cancelled']);
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'confirmed']);
    }

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->payments()->where('status', 'completed')->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->total - $this->paid_amount);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'confirmed' => 'blue',
            'preparing' => 'yellow',
            'ready' => 'green',
            'served' => 'purple',
            'paid' => 'emerald',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Черновик',
            'confirmed' => 'Подтверждён',
            'preparing' => 'Готовится',
            'ready' => 'Готов',
            'served' => 'Подан',
            'paid' => 'Оплачен',
            'cancelled' => 'Отменён',
            default => $this->status,
        };
    }
}
```

### 2. Модель OrderItem

Создай `app/Models/OrderItem.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'menu_item_id',
        'name',
        'price',
        'quantity',
        'discount',
        'total',
        'status',
        'notes',
        'cook_id',
        'sent_to_kitchen_at',
        'ready_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'quantity' => 'decimal:3',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'sent_to_kitchen_at' => 'datetime',
            'ready_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        
        static::saving(function ($model) {
            $model->total = ($model->price * $model->quantity) - $model->discount;
        });
        
        static::saved(function ($model) {
            $model->order->recalculateTotals();
        });
        
        static::deleted(function ($model) {
            $model->order->recalculateTotals();
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function cook(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cook_id');
    }

    public function modifiers(): HasMany
    {
        return $this->hasMany(OrderItemModifier::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'gray',
            'preparing' => 'yellow',
            'ready' => 'green',
            'served' => 'blue',
            'cancelled' => 'red',
            default => 'gray',
        };
    }
}
```

### 3. Модель OrderPayment

Создай `app/Models/OrderPayment.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'cash_shift_id',
        'method',
        'amount',
        'received_amount',
        'change_amount',
        'reference',
        'status',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'received_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function cashShift(): BelongsTo
    {
        return $this->belongsTo(CashShift::class);
    }
}
```

### 4. Модели Table и Hall

Создай `app/Models/Hall.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Organization\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hall extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'name',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function tables(): HasMany
    {
        return $this->hasMany(Table::class);
    }
}
```

Создай `app/Models/Table.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Organization\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Table extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'hall_id',
        'name',
        'capacity',
        'position_x',
        'position_y',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'position_x' => 'integer',
            'position_y' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function activeOrder(): HasOne
    {
        return $this->hasOne(Order::class)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->latest();
    }

    public function isOccupied(): bool
    {
        return $this->activeOrder()->exists();
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'free' => 'green',
            'occupied' => 'red',
            'reserved' => 'yellow',
            default => 'gray',
        };
    }
}
```

### 5. OrderNumberGenerator Service

Создай `app/Domain/Orders/Services/OrderNumberGenerator.php`:

```php
<?php

declare(strict_types=1);

namespace App\Domain\Orders\Services;

use App\Domain\Organization\Services\TenantService;
use App\Models\Order;

final class OrderNumberGenerator
{
    public function generate(): string
    {
        $branchId = TenantService::branchId();
        $today = now()->format('ymd');
        
        $lastOrder = Order::withoutGlobalScopes()
            ->where('branch_id', $branchId)
            ->whereDate('created_at', today())
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        
        if ($lastOrder && preg_match('/(\d+)$/', $lastOrder->order_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('%s-%04d', $today, $sequence);
    }
}
```

### 6. CreateOrderAction

Создай `app/Domain/Orders/Actions/CreateOrderAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Domain\Orders\Actions;

use App\Domain\Orders\DTOs\OrderDTO;
use App\Domain\Orders\Events\OrderCreated;
use App\Domain\Orders\Services\OrderNumberGenerator;
use App\Models\Order;
use App\Models\Table;

final class CreateOrderAction
{
    public function __construct(
        private readonly OrderNumberGenerator $numberGenerator
    ) {}

    public function execute(OrderDTO $dto): Order
    {
        $order = Order::create([
            'table_id' => $dto->tableId,
            'customer_id' => $dto->customerId,
            'waiter_id' => $dto->waiterId ?? auth()->id(),
            'order_number' => $this->numberGenerator->generate(),
            'type' => $dto->type,
            'status' => 'draft',
            'guests_count' => $dto->guestsCount,
            'notes' => $dto->notes,
        ]);

        if ($dto->tableId) {
            Table::where('id', $dto->tableId)->update(['status' => 'occupied']);
        }

        event(new OrderCreated($order));

        return $order;
    }
}
```

### 7. ProcessPaymentAction

Создай `app/Domain/Orders/Actions/ProcessPaymentAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Domain\Orders\Actions;

use App\Domain\Orders\DTOs\PaymentDTO;
use App\Domain\Orders\Events\OrderPaid;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Table;
use Illuminate\Support\Facades\DB;

final class ProcessPaymentAction
{
    public function execute(Order $order, PaymentDTO $dto): OrderPayment
    {
        return DB::transaction(function () use ($order, $dto) {
            $payment = OrderPayment::create([
                'order_id' => $order->id,
                'cash_shift_id' => $dto->cashShiftId,
                'method' => $dto->method,
                'amount' => $dto->amount,
                'received_amount' => $dto->receivedAmount,
                'change_amount' => max(0, $dto->receivedAmount - $dto->amount),
                'reference' => $dto->reference,
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            // Проверяем полную оплату
            if ($order->remaining_amount <= 0) {
                $order->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                // Освобождаем стол
                if ($order->table_id) {
                    Table::where('id', $order->table_id)->update(['status' => 'free']);
                }

                event(new OrderPaid($order));
            }

            return $payment;
        });
    }
}
```

### 8. Livewire: POS Terminal

Создай `app/Livewire/POS/Terminal.php`:

```php
<?php

declare(strict_types=1);

namespace App\Livewire\POS;

use App\Domain\Orders\Actions\CreateOrderAction;
use App\Domain\Orders\Actions\ProcessPaymentAction;
use App\Domain\Orders\DTOs\OrderDTO;
use App\Domain\Orders\DTOs\PaymentDTO;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.pos')]
final class Terminal extends Component
{
    public ?int $currentOrderId = null;
    public ?int $selectedTableId = null;
    public ?int $selectedCategoryId = null;
    public string $search = '';
    
    public bool $showPaymentModal = false;
    public string $paymentMethod = 'cash';
    public float $receivedAmount = 0;

    #[Computed]
    public function tables()
    {
        return Table::with('activeOrder.items')
            ->active()
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function categories()
    {
        return MenuCategory::active()->ordered()->get();
    }

    #[Computed]
    public function menuItems()
    {
        return MenuItem::query()
            ->available()
            ->when($this->selectedCategoryId, fn($q) => $q->where('category_id', $this->selectedCategoryId))
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->ordered()
            ->get();
    }

    #[Computed]
    public function currentOrder(): ?Order
    {
        if (!$this->currentOrderId) {
            return null;
        }
        
        return Order::with(['items.menuItem', 'table', 'customer'])->find($this->currentOrderId);
    }

    public function selectTable(int $tableId): void
    {
        $table = Table::with('activeOrder')->findOrFail($tableId);
        $this->selectedTableId = $tableId;

        if ($table->activeOrder) {
            $this->currentOrderId = $table->activeOrder->id;
        } else {
            $this->currentOrderId = null;
        }
    }

    public function createOrder(CreateOrderAction $action): void
    {
        if (!$this->selectedTableId) {
            return;
        }

        $order = $action->execute(new OrderDTO(
            tableId: $this->selectedTableId,
            type: 'dine_in',
            guestsCount: 1,
        ));

        $this->currentOrderId = $order->id;
    }

    public function addItem(int $menuItemId): void
    {
        if (!$this->currentOrderId) {
            if ($this->selectedTableId) {
                $this->createOrder(app(CreateOrderAction::class));
            } else {
                return;
            }
        }

        $menuItem = MenuItem::findOrFail($menuItemId);
        
        // Проверяем есть ли уже такой item в заказе
        $existingItem = OrderItem::where('order_id', $this->currentOrderId)
            ->where('menu_item_id', $menuItemId)
            ->where('status', 'pending')
            ->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + 1,
            ]);
        } else {
            OrderItem::create([
                'order_id' => $this->currentOrderId,
                'menu_item_id' => $menuItemId,
                'name' => $menuItem->name,
                'price' => $menuItem->price,
                'quantity' => 1,
            ]);
        }
    }

    public function updateItemQuantity(int $itemId, int $change): void
    {
        $item = OrderItem::findOrFail($itemId);
        $newQuantity = $item->quantity + $change;

        if ($newQuantity <= 0) {
            $item->delete();
        } else {
            $item->update(['quantity' => $newQuantity]);
        }
    }

    public function removeItem(int $itemId): void
    {
        OrderItem::findOrFail($itemId)->delete();
    }

    public function openPayment(): void
    {
        if (!$this->currentOrder || $this->currentOrder->total <= 0) {
            return;
        }
        
        $this->receivedAmount = (float) $this->currentOrder->total;
        $this->showPaymentModal = true;
    }

    public function processPayment(ProcessPaymentAction $action): void
    {
        if (!$this->currentOrder) {
            return;
        }

        $action->execute($this->currentOrder, new PaymentDTO(
            method: $this->paymentMethod,
            amount: (float) $this->currentOrder->total,
            receivedAmount: $this->receivedAmount,
        ));

        $this->showPaymentModal = false;
        $this->currentOrderId = null;
        $this->selectedTableId = null;
        
        session()->flash('success', 'Заказ оплачен!');
    }

    public function render(): View
    {
        return view('livewire.pos.terminal');
    }
}
```

### 9. Routes

Добавь в `routes/web.php`:

```php
Route::middleware('auth')->group(function () {
    // POS Terminal
    Route::get('/pos', \App\Livewire\POS\Terminal::class)->name('pos');
    
    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', \App\Livewire\Orders\Index::class)->name('index');
        Route::get('/{order}', \App\Livewire\Orders\Show::class)->name('show');
    });
});
```

### 10. Seeder для столов

Добавь в `database/seeders/DatabaseSeeder.php`:

```php
// После MenuSeeder
$this->call([
    RolesAndPermissionsSeeder::class,
    OrganizationSeeder::class,
    MenuSeeder::class,
    TablesSeeder::class,
]);
```

Создай `database/seeders/TablesSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Hall;
use App\Models\Table;
use Illuminate\Database\Seeder;

class TablesSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::first();

        $hall = Hall::create([
            'branch_id' => $branch->id,
            'name' => 'Основной зал',
            'sort_order' => 0,
        ]);

        for ($i = 1; $i <= 10; $i++) {
            Table::create([
                'branch_id' => $branch->id,
                'hall_id' => $hall->id,
                'name' => "Стол $i",
                'capacity' => rand(2, 6),
                'status' => 'free',
            ]);
        }
    }
}
```

## Проверка

```bash
php artisan migrate:fresh --seed
```

Открой `http://127.0.0.1:8001/pos`

Этап 2.2 завершён!
