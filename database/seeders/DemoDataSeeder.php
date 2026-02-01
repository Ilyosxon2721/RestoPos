<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        // Создаём демо-организацию
        $orgId = DB::table('organizations')->insertGetId([
            'uuid' => Str::uuid(),
            'name' => 'RestoPOS Demo',
            'legal_name' => 'ООО "RestoPOS Demo"',
            'inn' => '123456789',
            'subscription_plan' => 'business',
            'subscription_expires_at' => now()->addYear(),
            'settings' => json_encode([
                'currency' => 'UZS',
                'timezone' => 'Asia/Tashkent',
                'tax_rate' => 0,
                'service_charge_percent' => 10,
            ]),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Создаём филиал
        $branchId = DB::table('branches')->insertGetId([
            'uuid' => Str::uuid(),
            'organization_id' => $orgId,
            'name' => 'Основной филиал',
            'address' => 'г. Ташкент, ул. Амира Темура, 1',
            'city' => 'Ташкент',
            'phone' => '+998 71 123 45 67',
            'email' => 'main@restopos.uz',
            'timezone' => 'Asia/Tashkent',
            'currency_code' => 'UZS',
            'working_hours' => json_encode([
                'mon' => ['09:00', '23:00'],
                'tue' => ['09:00', '23:00'],
                'wed' => ['09:00', '23:00'],
                'thu' => ['09:00', '23:00'],
                'fri' => ['09:00', '00:00'],
                'sat' => ['10:00', '00:00'],
                'sun' => ['10:00', '23:00'],
            ]),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Создаём пользователя-владельца
        $ownerUserId = DB::table('users')->insertGetId([
            'uuid' => Str::uuid(),
            'organization_id' => $orgId,
            'email' => 'admin@restopos.uz',
            'phone' => '+998901234567',
            'password' => Hash::make('password'),
            'pin_code' => '1234',
            'first_name' => 'Админ',
            'last_name' => 'RestoPOS',
            'locale' => 'ru',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Привязываем роль владельца
        $ownerRoleId = DB::table('roles')->where('slug', 'owner')->value('id');
        DB::table('user_roles')->insert([
            'user_id' => $ownerUserId,
            'role_id' => $ownerRoleId,
            'branch_id' => null,
        ]);

        // Создаём демо-официанта
        $waiterUserId = DB::table('users')->insertGetId([
            'uuid' => Str::uuid(),
            'organization_id' => $orgId,
            'email' => 'waiter@restopos.uz',
            'phone' => '+998901234568',
            'password' => Hash::make('password'),
            'pin_code' => '1111',
            'first_name' => 'Азиз',
            'last_name' => 'Каримов',
            'locale' => 'ru',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $waiterRoleId = DB::table('roles')->where('slug', 'waiter')->value('id');
        DB::table('user_roles')->insert([
            'user_id' => $waiterUserId,
            'role_id' => $waiterRoleId,
            'branch_id' => $branchId,
        ]);

        // Создаём сотрудника для официанта
        DB::table('employees')->insert([
            'user_id' => $waiterUserId,
            'branch_id' => $branchId,
            'position' => 'Официант',
            'hire_date' => now()->subMonths(3),
            'salary_type' => 'mixed',
            'monthly_salary' => 3000000,
            'sales_percent' => 2,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Создаём единицы измерения
        $units = [
            ['name' => 'Штука', 'short_name' => 'шт', 'is_default' => true],
            ['name' => 'Килограмм', 'short_name' => 'кг', 'is_default' => false],
            ['name' => 'Грамм', 'short_name' => 'г', 'is_default' => false],
            ['name' => 'Литр', 'short_name' => 'л', 'is_default' => false],
            ['name' => 'Миллилитр', 'short_name' => 'мл', 'is_default' => false],
            ['name' => 'Порция', 'short_name' => 'порц', 'is_default' => false],
        ];

        $unitIds = [];
        foreach ($units as $unit) {
            $unitIds[$unit['short_name']] = DB::table('units')->insertGetId(array_merge($unit, [
                'organization_id' => $orgId,
                'created_at' => $now,
            ]));
        }

        // Создаём способы оплаты
        $paymentMethods = [
            ['name' => 'Наличные', 'type' => 'cash', 'sort_order' => 1],
            ['name' => 'Карта', 'type' => 'card', 'sort_order' => 2],
            ['name' => 'Payme', 'type' => 'transfer', 'sort_order' => 3],
            ['name' => 'Click', 'type' => 'transfer', 'sort_order' => 4],
            ['name' => 'Бонусы', 'type' => 'bonus', 'is_fiscal' => false, 'sort_order' => 5],
        ];

        foreach ($paymentMethods as $method) {
            DB::table('payment_methods')->insert(array_merge([
                'organization_id' => $orgId,
                'is_fiscal' => true,
                'is_active' => true,
                'created_at' => $now,
            ], $method));
        }

        // Создаём залы
        $hallId = DB::table('halls')->insertGetId([
            'branch_id' => $branchId,
            'name' => 'Основной зал',
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $terraceId = DB::table('halls')->insertGetId([
            'branch_id' => $branchId,
            'name' => 'Терраса',
            'sort_order' => 2,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Создаём столы в основном зале
        $tablePositions = [
            ['name' => '1', 'capacity' => 2, 'pos_x' => 50, 'pos_y' => 50],
            ['name' => '2', 'capacity' => 2, 'pos_x' => 200, 'pos_y' => 50],
            ['name' => '3', 'capacity' => 4, 'pos_x' => 350, 'pos_y' => 50],
            ['name' => '4', 'capacity' => 4, 'pos_x' => 50, 'pos_y' => 200],
            ['name' => '5', 'capacity' => 4, 'pos_x' => 200, 'pos_y' => 200],
            ['name' => '6', 'capacity' => 6, 'pos_x' => 350, 'pos_y' => 200, 'shape' => 'rectangle', 'width' => 150],
            ['name' => '7', 'capacity' => 8, 'pos_x' => 200, 'pos_y' => 350, 'shape' => 'round', 'width' => 120, 'height' => 120],
        ];

        foreach ($tablePositions as $i => $table) {
            DB::table('tables')->insert(array_merge([
                'hall_id' => $hallId,
                'shape' => 'square',
                'width' => 100,
                'height' => 100,
                'status' => 'free',
                'sort_order' => $i + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ], $table));
        }

        // Столы на террасе
        for ($i = 8; $i <= 12; $i++) {
            DB::table('tables')->insert([
                'hall_id' => $terraceId,
                'name' => (string) $i,
                'capacity' => 4,
                'shape' => 'square',
                'pos_x' => (($i - 8) % 3) * 150 + 50,
                'pos_y' => floor(($i - 8) / 3) * 150 + 50,
                'width' => 100,
                'height' => 100,
                'status' => 'free',
                'sort_order' => $i,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Создаём цехи
        $kitchenId = DB::table('workshops')->insertGetId([
            'branch_id' => $branchId,
            'name' => 'Кухня',
            'color' => '#FF6B6B',
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $barId = DB::table('workshops')->insertGetId([
            'branch_id' => $branchId,
            'name' => 'Бар',
            'color' => '#4ECDC4',
            'sort_order' => 2,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Создаём категории меню
        $categories = [
            ['name' => 'Салаты', 'color' => '#4CAF50', 'sort_order' => 1],
            ['name' => 'Супы', 'color' => '#FF9800', 'sort_order' => 2],
            ['name' => 'Горячее', 'color' => '#F44336', 'sort_order' => 3],
            ['name' => 'Гарниры', 'color' => '#9C27B0', 'sort_order' => 4],
            ['name' => 'Напитки', 'color' => '#2196F3', 'sort_order' => 5],
            ['name' => 'Десерты', 'color' => '#E91E63', 'sort_order' => 6],
        ];

        $categoryIds = [];
        foreach ($categories as $category) {
            $categoryIds[$category['name']] = DB::table('categories')->insertGetId(array_merge($category, [
                'organization_id' => $orgId,
                'is_visible' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // Создаём товары
        $products = [
            // Салаты
            ['category' => 'Салаты', 'name' => 'Цезарь с курицей', 'price' => 48000, 'workshop' => $kitchenId, 'weight' => 250, 'cooking_time' => 10],
            ['category' => 'Салаты', 'name' => 'Греческий', 'price' => 38000, 'workshop' => $kitchenId, 'weight' => 200, 'cooking_time' => 8],
            ['category' => 'Салаты', 'name' => 'Оливье', 'price' => 32000, 'workshop' => $kitchenId, 'weight' => 200, 'cooking_time' => 5],

            // Супы
            ['category' => 'Супы', 'name' => 'Борщ', 'price' => 28000, 'workshop' => $kitchenId, 'weight' => 350, 'cooking_time' => 5],
            ['category' => 'Супы', 'name' => 'Шурпа', 'price' => 35000, 'workshop' => $kitchenId, 'weight' => 400, 'cooking_time' => 5],
            ['category' => 'Супы', 'name' => 'Лагман', 'price' => 42000, 'workshop' => $kitchenId, 'weight' => 450, 'cooking_time' => 15],

            // Горячее
            ['category' => 'Горячее', 'name' => 'Плов', 'price' => 45000, 'workshop' => $kitchenId, 'weight' => 350, 'cooking_time' => 5],
            ['category' => 'Горячее', 'name' => 'Шашлык из баранины', 'price' => 78000, 'workshop' => $kitchenId, 'weight' => 250, 'cooking_time' => 20],
            ['category' => 'Горячее', 'name' => 'Стейк рибай', 'price' => 120000, 'workshop' => $kitchenId, 'weight' => 300, 'cooking_time' => 25],
            ['category' => 'Горячее', 'name' => 'Куриная грудка гриль', 'price' => 55000, 'workshop' => $kitchenId, 'weight' => 200, 'cooking_time' => 15],

            // Гарниры
            ['category' => 'Гарниры', 'name' => 'Картофель фри', 'price' => 18000, 'workshop' => $kitchenId, 'weight' => 150, 'cooking_time' => 8],
            ['category' => 'Гарниры', 'name' => 'Рис отварной', 'price' => 12000, 'workshop' => $kitchenId, 'weight' => 150, 'cooking_time' => 3],
            ['category' => 'Гарниры', 'name' => 'Овощи гриль', 'price' => 22000, 'workshop' => $kitchenId, 'weight' => 180, 'cooking_time' => 10],

            // Напитки
            ['category' => 'Напитки', 'name' => 'Чай чёрный', 'type' => 'drink', 'price' => 8000, 'workshop' => $barId, 'weight' => 400, 'cooking_time' => 3],
            ['category' => 'Напитки', 'name' => 'Чай зелёный', 'type' => 'drink', 'price' => 8000, 'workshop' => $barId, 'weight' => 400, 'cooking_time' => 3],
            ['category' => 'Напитки', 'name' => 'Кофе американо', 'type' => 'drink', 'price' => 15000, 'workshop' => $barId, 'weight' => 200, 'cooking_time' => 3],
            ['category' => 'Напитки', 'name' => 'Кофе капучино', 'type' => 'drink', 'price' => 20000, 'workshop' => $barId, 'weight' => 250, 'cooking_time' => 5],
            ['category' => 'Напитки', 'name' => 'Лимонад домашний', 'type' => 'drink', 'price' => 18000, 'workshop' => $barId, 'weight' => 350, 'cooking_time' => 3],
            ['category' => 'Напитки', 'name' => 'Coca-Cola', 'type' => 'drink', 'price' => 10000, 'workshop' => $barId, 'weight' => 330, 'cooking_time' => 1],

            // Десерты
            ['category' => 'Десерты', 'name' => 'Чизкейк', 'price' => 28000, 'workshop' => $kitchenId, 'weight' => 150, 'cooking_time' => 2],
            ['category' => 'Десерты', 'name' => 'Тирамису', 'price' => 32000, 'workshop' => $kitchenId, 'weight' => 150, 'cooking_time' => 2],
            ['category' => 'Десерты', 'name' => 'Мороженое', 'price' => 18000, 'workshop' => $barId, 'weight' => 120, 'cooking_time' => 2],
        ];

        foreach ($products as $product) {
            DB::table('products')->insert([
                'uuid' => Str::uuid(),
                'organization_id' => $orgId,
                'category_id' => $categoryIds[$product['category']],
                'workshop_id' => $product['workshop'],
                'unit_id' => $unitIds['порц'],
                'type' => $product['type'] ?? 'dish',
                'name' => $product['name'],
                'price' => $product['price'],
                'weight' => $product['weight'],
                'cooking_time' => $product['cooking_time'],
                'is_visible' => true,
                'is_available' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Создаём склад
        $warehouseId = DB::table('warehouses')->insertGetId([
            'branch_id' => $branchId,
            'name' => 'Основной склад',
            'type' => 'main',
            'is_default' => true,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Группы клиентов
        DB::table('customer_groups')->insert([
            ['organization_id' => $orgId, 'name' => 'Новый', 'discount_percent' => 0, 'bonus_earn_percent' => 3, 'min_spent_to_join' => 0, 'color' => '#9E9E9E', 'created_at' => $now],
            ['organization_id' => $orgId, 'name' => 'Серебро', 'discount_percent' => 5, 'bonus_earn_percent' => 5, 'min_spent_to_join' => 1000000, 'color' => '#90A4AE', 'created_at' => $now],
            ['organization_id' => $orgId, 'name' => 'Золото', 'discount_percent' => 10, 'bonus_earn_percent' => 7, 'min_spent_to_join' => 5000000, 'color' => '#FFD54F', 'created_at' => $now],
            ['organization_id' => $orgId, 'name' => 'Платина', 'discount_percent' => 15, 'bonus_earn_percent' => 10, 'min_spent_to_join' => 15000000, 'color' => '#78909C', 'created_at' => $now],
        ]);

        $this->command->info('Demo data seeded successfully!');
        $this->command->info('Login: admin@restopos.uz / password');
        $this->command->info('PIN: 1234');
    }
}
