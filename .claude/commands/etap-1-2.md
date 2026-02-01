# Этап 1.2: Миграции базы данных

Создай все миграции для RestoPOS.

## Структура миграций

Создай миграции в следующем порядке (важна последовательность для foreign keys):

### Группа 1: Организации и пользователи

```bash
php artisan make:migration create_organizations_table
php artisan make:migration create_branches_table
php artisan make:migration create_users_table --create=users
php artisan make:migration create_user_branches_table
```

### Группа 2: Роли и права (Spatie)

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="migrations"
```

### Группа 3: Меню

```bash
php artisan make:migration create_menu_categories_table
php artisan make:migration create_menu_items_table
php artisan make:migration create_modifiers_table
php artisan make:migration create_modifier_options_table
php artisan make:migration create_menu_item_modifiers_table
php artisan make:migration create_tech_cards_table
php artisan make:migration create_tech_card_ingredients_table
```

### Группа 4: Зал и столы

```bash
php artisan make:migration create_halls_table
php artisan make:migration create_tables_table
```

### Группа 5: Заказы

```bash
php artisan make:migration create_orders_table
php artisan make:migration create_order_items_table
php artisan make:migration create_order_item_modifiers_table
php artisan make:migration create_order_payments_table
```

### Группа 6: Склад

```bash
php artisan make:migration create_warehouses_table
php artisan make:migration create_ingredient_categories_table
php artisan make:migration create_ingredients_table
php artisan make:migration create_suppliers_table
php artisan make:migration create_supplies_table
php artisan make:migration create_supply_items_table
php artisan make:migration create_inventory_transactions_table
php artisan make:migration create_inventories_table
php artisan make:migration create_inventory_items_table
```

### Группа 7: Финансы

```bash
php artisan make:migration create_cash_registers_table
php artisan make:migration create_cash_shifts_table
php artisan make:migration create_cash_transactions_table
php artisan make:migration create_expenses_table
php artisan make:migration create_expense_categories_table
```

### Группа 8: Клиенты

```bash
php artisan make:migration create_customers_table
php artisan make:migration create_customer_groups_table
php artisan make:migration create_loyalty_programs_table
php artisan make:migration create_loyalty_transactions_table
php artisan make:migration create_promotions_table
```

### Группа 9: Персонал

```bash
php artisan make:migration create_positions_table
php artisan make:migration create_work_shifts_table
php artisan make:migration create_employee_shifts_table
php artisan make:migration create_salaries_table
```

### Группа 10: Доставка

```bash
php artisan make:migration create_delivery_zones_table
php artisan make:migration create_couriers_table
php artisan make:migration create_deliveries_table
```

---

## Содержимое ключевых миграций

### organizations_table

```php
Schema::create('organizations', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('name');
    $table->string('legal_name')->nullable();
    $table->string('inn', 20)->nullable();
    $table->string('logo')->nullable();
    $table->string('subdomain', 100)->unique()->nullable();
    $table->enum('subscription_plan', ['trial', 'starter', 'business', 'enterprise'])->default('trial');
    $table->timestamp('subscription_expires_at')->nullable();
    $table->json('settings')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
});
```

### branches_table

```php
Schema::create('branches', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('address', 500)->nullable();
    $table->string('city', 100)->nullable();
    $table->string('phone', 50)->nullable();
    $table->string('email')->nullable();
    $table->string('timezone', 50)->default('Asia/Tashkent');
    $table->char('currency_code', 3)->default('UZS');
    $table->json('working_hours')->nullable();
    $table->json('settings')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
    
    $table->index('organization_id');
});
```

### users_table (обнови существующую)

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
    $table->string('email')->nullable();
    $table->string('phone', 50)->nullable();
    $table->string('password');
    $table->string('pin_code', 10)->nullable();
    $table->string('first_name', 100);
    $table->string('last_name', 100)->nullable();
    $table->string('avatar')->nullable();
    $table->string('language', 5)->default('ru');
    $table->boolean('is_active')->default(true);
    $table->timestamp('last_login_at')->nullable();
    $table->rememberToken();
    $table->timestamps();
    $table->softDeletes();
    
    $table->unique(['organization_id', 'email']);
    $table->unique(['organization_id', 'phone']);
    $table->index('organization_id');
});
```

### menu_categories_table

```php
Schema::create('menu_categories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
    $table->foreignId('parent_id')->nullable()->constrained('menu_categories')->nullOnDelete();
    $table->string('name');
    $table->string('slug');
    $table->text('description')->nullable();
    $table->string('image')->nullable();
    $table->string('color', 7)->nullable();
    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
    
    $table->unique(['branch_id', 'slug']);
    $table->index(['branch_id', 'is_active', 'sort_order']);
});
```

### menu_items_table

```php
Schema::create('menu_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
    $table->foreignId('category_id')->constrained('menu_categories')->cascadeOnDelete();
    $table->string('name');
    $table->string('slug');
    $table->text('description')->nullable();
    $table->string('image')->nullable();
    $table->decimal('price', 12, 2);
    $table->decimal('cost_price', 12, 2)->nullable();
    $table->string('sku', 50)->nullable();
    $table->string('barcode', 50)->nullable();
    $table->string('unit', 20)->default('шт');
    $table->integer('cook_time_minutes')->nullable();
    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->boolean('is_stop_list')->default(false);
    $table->json('nutrition')->nullable();
    $table->json('allergens')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    $table->unique(['branch_id', 'slug']);
    $table->index(['branch_id', 'category_id', 'is_active']);
    $table->index(['branch_id', 'sku']);
    $table->index(['branch_id', 'barcode']);
});
```

### orders_table

```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
    $table->foreignId('table_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('waiter_id')->nullable()->constrained('users')->nullOnDelete();
    $table->string('order_number', 20);
    $table->enum('type', ['dine_in', 'takeaway', 'delivery'])->default('dine_in');
    $table->enum('status', ['draft', 'confirmed', 'preparing', 'ready', 'served', 'paid', 'cancelled'])->default('draft');
    $table->integer('guests_count')->default(1);
    $table->decimal('subtotal', 12, 2)->default(0);
    $table->decimal('discount_amount', 12, 2)->default(0);
    $table->string('discount_reason')->nullable();
    $table->decimal('service_charge', 12, 2)->default(0);
    $table->decimal('tax_amount', 12, 2)->default(0);
    $table->decimal('total', 12, 2)->default(0);
    $table->text('notes')->nullable();
    $table->timestamp('confirmed_at')->nullable();
    $table->timestamp('ready_at')->nullable();
    $table->timestamp('served_at')->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->timestamp('cancelled_at')->nullable();
    $table->string('cancel_reason')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    $table->unique(['branch_id', 'order_number']);
    $table->index(['branch_id', 'status', 'created_at']);
    $table->index(['branch_id', 'table_id', 'status']);
});
```

### order_items_table

```php
Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->foreignId('menu_item_id')->constrained()->restrictOnDelete();
    $table->string('name');
    $table->decimal('price', 12, 2);
    $table->decimal('quantity', 8, 3);
    $table->decimal('discount', 12, 2)->default(0);
    $table->decimal('total', 12, 2);
    $table->enum('status', ['pending', 'preparing', 'ready', 'served', 'cancelled'])->default('pending');
    $table->text('notes')->nullable();
    $table->foreignId('cook_id')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('sent_to_kitchen_at')->nullable();
    $table->timestamp('ready_at')->nullable();
    $table->timestamps();
    
    $table->index(['order_id', 'status']);
});
```

### cash_shifts_table

```php
Schema::create('cash_shifts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
    $table->foreignId('cash_register_id')->constrained()->cascadeOnDelete();
    $table->foreignId('opened_by')->constrained('users')->restrictOnDelete();
    $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
    $table->decimal('opening_amount', 12, 2);
    $table->decimal('closing_amount', 12, 2)->nullable();
    $table->decimal('expected_amount', 12, 2)->nullable();
    $table->decimal('difference', 12, 2)->nullable();
    $table->decimal('cash_sales', 12, 2)->default(0);
    $table->decimal('card_sales', 12, 2)->default(0);
    $table->decimal('other_sales', 12, 2)->default(0);
    $table->integer('orders_count')->default(0);
    $table->timestamp('opened_at');
    $table->timestamp('closed_at')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
    
    $table->index(['branch_id', 'opened_at']);
    $table->index(['cash_register_id', 'closed_at']);
});
```

### customers_table

```php
Schema::create('customers', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
    $table->foreignId('group_id')->nullable()->constrained('customer_groups')->nullOnDelete();
    $table->string('first_name', 100);
    $table->string('last_name', 100)->nullable();
    $table->string('phone', 50)->nullable();
    $table->string('email')->nullable();
    $table->date('birth_date')->nullable();
    $table->enum('gender', ['male', 'female'])->nullable();
    $table->text('address')->nullable();
    $table->text('notes')->nullable();
    $table->decimal('bonus_balance', 12, 2)->default(0);
    $table->decimal('total_spent', 12, 2)->default(0);
    $table->integer('orders_count')->default(0);
    $table->timestamp('last_order_at')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
    
    $table->unique(['organization_id', 'phone']);
    $table->index(['organization_id', 'is_active']);
});
```

---

## Запуск миграций

После создания всех файлов:

```bash
php artisan migrate
```

## Проверка

```bash
php artisan migrate:status
```

Этап 1.2 завершён!
