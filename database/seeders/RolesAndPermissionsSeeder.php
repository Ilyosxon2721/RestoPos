<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Системные роли
        $roles = [
            ['name' => 'Владелец', 'slug' => 'owner', 'description' => 'Полный доступ к системе', 'is_system' => true],
            ['name' => 'Директор', 'slug' => 'director', 'description' => 'Управление филиалом', 'is_system' => true],
            ['name' => 'Администратор', 'slug' => 'admin', 'description' => 'Администрирование системы', 'is_system' => true],
            ['name' => 'Бухгалтер', 'slug' => 'accountant', 'description' => 'Финансовая отчётность', 'is_system' => true],
            ['name' => 'Старший официант', 'slug' => 'head_waiter', 'description' => 'Управление залом', 'is_system' => true],
            ['name' => 'Официант', 'slug' => 'waiter', 'description' => 'Обслуживание гостей', 'is_system' => true],
            ['name' => 'Бармен', 'slug' => 'bartender', 'description' => 'Работа за баром', 'is_system' => true],
            ['name' => 'Кассир', 'slug' => 'cashier', 'description' => 'Работа с кассой', 'is_system' => true],
            ['name' => 'Повар', 'slug' => 'cook', 'description' => 'Приготовление блюд', 'is_system' => true],
            ['name' => 'Курьер', 'slug' => 'courier', 'description' => 'Доставка заказов', 'is_system' => true],
            ['name' => 'Кладовщик', 'slug' => 'storekeeper', 'description' => 'Управление складом', 'is_system' => true],
        ];

        $now = now();
        foreach ($roles as $role) {
            DB::table('roles')->insert(array_merge($role, [
                'organization_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // Права доступа по модулям
        $permissions = [
            // Заказы
            ['name' => 'Просмотр заказов', 'slug' => 'orders.view', 'module' => 'orders'],
            ['name' => 'Создание заказов', 'slug' => 'orders.create', 'module' => 'orders'],
            ['name' => 'Редактирование заказов', 'slug' => 'orders.edit', 'module' => 'orders'],
            ['name' => 'Отмена заказов', 'slug' => 'orders.cancel', 'module' => 'orders'],
            ['name' => 'Применение скидок', 'slug' => 'orders.discount', 'module' => 'orders'],
            ['name' => 'Возврат заказов', 'slug' => 'orders.refund', 'module' => 'orders'],

            // Меню
            ['name' => 'Просмотр меню', 'slug' => 'menu.view', 'module' => 'menu'],
            ['name' => 'Управление меню', 'slug' => 'menu.manage', 'module' => 'menu'],
            ['name' => 'Управление техкартами', 'slug' => 'menu.tech_cards', 'module' => 'menu'],
            ['name' => 'Управление стоп-листом', 'slug' => 'menu.stop_list', 'module' => 'menu'],

            // Склад
            ['name' => 'Просмотр склада', 'slug' => 'warehouse.view', 'module' => 'warehouse'],
            ['name' => 'Приёмка поставок', 'slug' => 'warehouse.supply', 'module' => 'warehouse'],
            ['name' => 'Списание', 'slug' => 'warehouse.write_off', 'module' => 'warehouse'],
            ['name' => 'Перемещения', 'slug' => 'warehouse.transfer', 'module' => 'warehouse'],
            ['name' => 'Инвентаризация', 'slug' => 'warehouse.inventory', 'module' => 'warehouse'],

            // Персонал
            ['name' => 'Просмотр персонала', 'slug' => 'staff.view', 'module' => 'staff'],
            ['name' => 'Управление персоналом', 'slug' => 'staff.manage', 'module' => 'staff'],
            ['name' => 'Расчёт зарплаты', 'slug' => 'staff.salary', 'module' => 'staff'],

            // Финансы
            ['name' => 'Просмотр финансов', 'slug' => 'finance.view', 'module' => 'finance'],
            ['name' => 'Управление финансами', 'slug' => 'finance.manage', 'module' => 'finance'],
            ['name' => 'Кассовые операции', 'slug' => 'finance.cash_operations', 'module' => 'finance'],
            ['name' => 'Открытие/закрытие смены', 'slug' => 'finance.cash_shift', 'module' => 'finance'],

            // Отчёты
            ['name' => 'Просмотр отчётов', 'slug' => 'reports.view', 'module' => 'reports'],
            ['name' => 'Экспорт отчётов', 'slug' => 'reports.export', 'module' => 'reports'],
            ['name' => 'Расширенная аналитика', 'slug' => 'reports.advanced', 'module' => 'reports'],

            // Клиенты
            ['name' => 'Просмотр клиентов', 'slug' => 'customers.view', 'module' => 'customers'],
            ['name' => 'Управление клиентами', 'slug' => 'customers.manage', 'module' => 'customers'],
            ['name' => 'Управление бонусами', 'slug' => 'customers.bonuses', 'module' => 'customers'],
            ['name' => 'Управление акциями', 'slug' => 'customers.promotions', 'module' => 'customers'],

            // Доставка
            ['name' => 'Просмотр доставки', 'slug' => 'delivery.view', 'module' => 'delivery'],
            ['name' => 'Управление доставкой', 'slug' => 'delivery.manage', 'module' => 'delivery'],
            ['name' => 'Назначение курьеров', 'slug' => 'delivery.assign', 'module' => 'delivery'],

            // Бронирование
            ['name' => 'Просмотр бронирований', 'slug' => 'reservations.view', 'module' => 'reservations'],
            ['name' => 'Управление бронированиями', 'slug' => 'reservations.manage', 'module' => 'reservations'],

            // Настройки
            ['name' => 'Настройки филиала', 'slug' => 'settings.branch', 'module' => 'settings'],
            ['name' => 'Настройки системы', 'slug' => 'settings.system', 'module' => 'settings'],
            ['name' => 'Управление ролями', 'slug' => 'settings.roles', 'module' => 'settings'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert(array_merge($permission, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // Привязка прав к ролям
        $rolePermissions = [
            'owner' => ['*'], // все права
            'director' => [
                'orders.*', 'menu.*', 'warehouse.*', 'staff.view', 'staff.manage',
                'finance.*', 'reports.*', 'customers.*', 'delivery.*', 'reservations.*',
                'settings.branch',
            ],
            'admin' => [
                'orders.*', 'menu.*', 'warehouse.view', 'warehouse.supply',
                'staff.view', 'finance.view', 'finance.cash_operations', 'finance.cash_shift',
                'reports.view', 'customers.*', 'delivery.*', 'reservations.*',
            ],
            'accountant' => [
                'finance.*', 'reports.*', 'warehouse.view', 'staff.salary',
            ],
            'head_waiter' => [
                'orders.*', 'menu.view', 'menu.stop_list', 'customers.view',
                'reservations.*', 'reports.view',
            ],
            'waiter' => [
                'orders.view', 'orders.create', 'orders.edit', 'menu.view',
                'customers.view', 'reservations.view',
            ],
            'bartender' => [
                'orders.view', 'orders.create', 'orders.edit', 'menu.view', 'menu.stop_list',
            ],
            'cashier' => [
                'orders.view', 'orders.create', 'orders.edit', 'orders.discount',
                'finance.cash_operations', 'finance.cash_shift', 'customers.view',
                'customers.bonuses', 'reports.view',
            ],
            'cook' => [
                'orders.view', 'menu.view', 'menu.stop_list',
            ],
            'courier' => [
                'orders.view', 'delivery.view',
            ],
            'storekeeper' => [
                'warehouse.*', 'menu.view',
            ],
        ];

        $allPermissions = DB::table('permissions')->pluck('id', 'slug')->toArray();
        $allRoles = DB::table('roles')->pluck('id', 'slug')->toArray();

        foreach ($rolePermissions as $roleSlug => $permissionPatterns) {
            if (!isset($allRoles[$roleSlug])) {
                continue;
            }

            $roleId = $allRoles[$roleSlug];

            foreach ($permissionPatterns as $pattern) {
                if ($pattern === '*') {
                    // Все права
                    foreach ($allPermissions as $permSlug => $permId) {
                        DB::table('role_permissions')->insert([
                            'role_id' => $roleId,
                            'permission_id' => $permId,
                        ]);
                    }
                } elseif (str_ends_with($pattern, '.*')) {
                    // Все права модуля
                    $module = str_replace('.*', '', $pattern);
                    foreach ($allPermissions as $permSlug => $permId) {
                        if (str_starts_with($permSlug, $module.'.')) {
                            DB::table('role_permissions')->insert([
                                'role_id' => $roleId,
                                'permission_id' => $permId,
                            ]);
                        }
                    }
                } else {
                    // Конкретное право
                    if (isset($allPermissions[$pattern])) {
                        DB::table('role_permissions')->insert([
                            'role_id' => $roleId,
                            'permission_id' => $allPermissions[$pattern],
                        ]);
                    }
                }
            }
        }
    }
}
