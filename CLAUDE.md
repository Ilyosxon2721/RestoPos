# RestoPOS - Claude Code Project Guide

## 📋 Project Overview

**RestoPOS** — облачная POS-система для автоматизации ресторанов и кафе (аналог Poster POS).

### Tech Stack
- **Backend:** Laravel 12, PHP 8.3+
- **Frontend:** Livewire 3, Alpine.js, Tailwind CSS
- **Database:** MySQL 8.0 / PostgreSQL 16
- **Cache:** Redis 7+
- **Search:** Meilisearch (опционально)
- **Queue:** Laravel Horizon + Redis

### Architecture
```
restopos/
├── app/
│   ├── Domain/           # Domain-driven modules
│   │   ├── Auth/
│   │   ├── Orders/
│   │   ├── Menu/
│   │   ├── Warehouse/
│   │   ├── Finance/
│   │   └── Customers/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   └── Services/
├── resources/
│   ├── views/
│   │   ├── livewire/
│   │   └── components/
│   └── js/
├── database/
│   ├── migrations/
│   └── seeders/
└── tests/
    ├── Feature/
    └── Unit/
```

---

## 🚀 Quick Commands

### Development
```bash
# Запуск dev-сервера
php artisan serve

# Запуск Vite для фронтенда
npm run dev

# Запуск очередей
php artisan horizon

# Запуск всего через Docker
docker compose up -d
```

### Testing
```bash
# Все тесты
php artisan test

# Конкретный тест
php artisan test --filter=OrderTest

# С покрытием
php artisan test --coverage
```

### Database
```bash
# Миграции
php artisan migrate

# Откат
php artisan migrate:rollback

# Сброс и пересоздание
php artisan migrate:fresh --seed
```

---

## 📁 Key Files

| File | Purpose |
|------|---------|
| `routes/api.php` | API маршруты |
| `routes/web.php` | Web маршруты |
| `app/Domain/*/` | Доменная логика модулей |
| `config/restopos.php` | Конфигурация системы |
| `database/migrations/` | Схема БД |

---

## 🏗️ Domain Modules

### 1. Orders (Заказы)
- Создание/редактирование заказов
- Разделение счёта
- Применение скидок
- Печать чеков

### 2. Menu (Меню)
- Категории и блюда
- Модификаторы
- Техкарты
- Стоп-лист

### 3. Warehouse (Склад)
- Приёмка товаров
- Списание
- Инвентаризация
- FIFO учёт

### 4. Finance (Финансы)
- Кассовые смены
- Платежи
- Отчёты P&L

### 5. Customers (Клиенты)
- CRM
- Программа лояльности
- Бонусы и скидки

---

## 🎯 Coding Standards

### PHP/Laravel
```php
// Используй строгую типизацию
declare(strict_types=1);

// Используй DTO для передачи данных
final readonly class CreateOrderDTO
{
    public function __construct(
        public int $tableId,
        public array $items,
        public ?int $customerId = null,
    ) {}
}

// Actions вместо толстых контроллеров
final class CreateOrderAction
{
    public function execute(CreateOrderDTO $dto): Order
    {
        // логика
    }
}
```

### Naming Conventions
- **Models:** `Order`, `MenuItem`, `Customer` (singular)
- **Controllers:** `OrderController`, `MenuItemController`
- **Actions:** `CreateOrderAction`, `ApplyDiscountAction`
- **Events:** `OrderCreated`, `PaymentReceived`
- **Jobs:** `ProcessOrderJob`, `SendReceiptJob`

### Database
- Таблицы: `snake_case` множественное (`orders`, `menu_items`)
- Pivot: алфавитный порядок (`customer_order`)
- Внешние ключи: `{table}_id` (`order_id`)

---

## 🔄 Git Workflow

### Branches
- `main` — production
- `develop` — development
- `feature/RP-XXX-description` — фичи
- `fix/RP-XXX-description` — баги
- `hotfix/RP-XXX-description` — срочные фиксы

### Commit Messages
```
feat(orders): add split bill functionality
fix(menu): correct price calculation with modifiers
docs(api): update order endpoints documentation
test(warehouse): add inventory tests
refactor(finance): extract payment processing
```

---

## 🧪 Testing Guidelines

### Naming
```php
/** @test */
public function it_creates_order_with_items(): void
{
    // Arrange
    $table = Table::factory()->create();
    $items = MenuItem::factory()->count(3)->create();

    // Act
    $order = $this->orderService->create($table, $items);

    // Assert
    $this->assertDatabaseHas('orders', ['id' => $order->id]);
    $this->assertCount(3, $order->items);
}
```

### What to Test
- ✅ Domain logic (Actions, Services)
- ✅ API endpoints (Feature tests)
- ✅ Livewire components
- ⚠️ Controllers (через Feature tests)
- ❌ Eloquent (уже протестирован Laravel)

---

## 🔐 Security Notes

- Все API endpoints требуют аутентификацию
- Используй `Policy` для авторизации
- Не храни секреты в коде
- Логируй все финансовые операции
- Валидируй все входящие данные через `FormRequest`

---

## 📞 API Patterns

### Response Format
```json
{
  "success": true,
  "data": { },
  "message": "Order created successfully",
  "meta": {
    "pagination": { }
  }
}
```

### Error Format
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Invalid input",
    "details": { }
  }
}
```

---

## 🤖 Claude Code Instructions

### При работе с кодом:
1. **Всегда** используй строгую типизацию PHP
2. Следуй PSR-12 стандартам
3. Пиши тесты для новой функциональности
4. Используй `readonly` классы для DTO/Value Objects
5. Комментарии на русском, код на английском

### При создании миграций:
1. Добавляй `down()` метод
2. Используй `foreignId()` для внешних ключей
3. Не забывай индексы для часто используемых полей

### При работе с Livewire:
1. Используй `#[Computed]` для вычисляемых свойств
2. Избегай N+1 запросов с `$this->loadMissing()`
3. Валидируй в реальном времени через `#[Validate]`

---

## 📊 Performance Tips

- Используй `Redis` для кэширования меню и категорий
- `Eager loading` для связанных моделей
- Индексы на `branch_id`, `created_at`, `status`
- Очереди для отправки уведомлений и печати
