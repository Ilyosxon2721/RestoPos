<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Общий middleware tenant — определяет организацию по субдомену
|--------------------------------------------------------------------------
| Работает на всех маршрутах. Если субдомен найден — привязывает tenant.
| Если нет — пропускает (главный домен restopos.uz).
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Landing Page (Главный домен — restopos.uz)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (app()->bound('tenant')) {
        // На субдомене / → перенаправляем на логин или дашборд
        if (auth()->check()) {
            return redirect('/redirect');
        }
        return redirect('/login');
    }
    return view('landing');
})->name('landing');

/*
|--------------------------------------------------------------------------
| Регистрация (только на главном домене restopos.uz)
|--------------------------------------------------------------------------
*/
Route::get('/register', \App\Livewire\Auth\Register::class)
    ->middleware('guest')
    ->name('register');

/*
|--------------------------------------------------------------------------
| Login — работает и на главном домене, и на субдомене
|--------------------------------------------------------------------------
*/
Route::get('/login', \App\Livewire\Auth\Login::class)
    ->middleware('guest')
    ->name('login');

/*
|--------------------------------------------------------------------------
| Logout — работает везде
|--------------------------------------------------------------------------
*/
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

/*
|--------------------------------------------------------------------------
| Super Admin Panel (/admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    Route::get('/login', \App\Livewire\Admin\Login::class)->name('admin.login');
    Route::post('/logout', function () {
        auth('platform')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('admin.login');
    })->name('admin.logout');

    Route::middleware('super_admin')->group(function () {
        Route::get('/', fn () => redirect()->route('admin.dashboard'));
        Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->name('admin.dashboard');
        Route::get('/organizations', \App\Livewire\Admin\Organizations::class)->name('admin.organizations');
        Route::get('/plans', \App\Livewire\Admin\Plans::class)->name('admin.plans');
        Route::get('/subscriptions', \App\Livewire\Admin\Subscriptions::class)->name('admin.subscriptions');
        Route::get('/settings', \App\Livewire\Admin\Settings::class)->name('admin.settings');
    });
});

/*
|--------------------------------------------------------------------------
| Redirect — определяет панель по роли пользователя
|--------------------------------------------------------------------------
| Единая точка входа после логина. Если на главном домене и у
| организации есть субдомен — перенаправляет на субдомен.
|--------------------------------------------------------------------------
*/
Route::get('/redirect', \App\Application\Http\Middleware\RedirectByRole::class)
    ->middleware('auth')
    ->name('role.redirect');

/*
|--------------------------------------------------------------------------
| Client Panel Routes (Require Authentication)
|--------------------------------------------------------------------------
| Все панели: cabinet, manager, cashier, waiter, kitchen, warehouse.
| Работают на субдомене (lolotea.restopos.uz/cabinet/dashboard)
| и на главном домене (для обратной совместимости).
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /*
    |----------------------------------------------------------------------
    | Cabinet — Панель владельца организации (/cabinet)
    |----------------------------------------------------------------------
    */
    Route::prefix('cabinet')->middleware('client_role:owner')->group(function () {
        Route::get('/', fn () => redirect()->route('cabinet.dashboard'));
        Route::get('/dashboard', \App\Livewire\Cabinet\Dashboard::class)->name('cabinet.dashboard');
        Route::get('/finance', \App\Livewire\Cabinet\Finance::class)->name('cabinet.finance');
        Route::get('/reports', \App\Livewire\Cabinet\Reports::class)->name('cabinet.reports');

        // Меню
        Route::get('/menu', \App\Livewire\Menu\Categories::class)->name('cabinet.menu');
        Route::get('/menu/items', \App\Livewire\Menu\Items::class)->name('cabinet.menu.items');
        Route::get('/menu/ingredients', \App\Livewire\Menu\Ingredients::class)->name('cabinet.menu.ingredients');
        Route::get('/menu/tech-cards', \App\Livewire\Menu\TechCards::class)->name('cabinet.menu.tech-cards');

        // Склад
        Route::get('/warehouse', \App\Livewire\Warehouse\Stock::class)->name('cabinet.warehouse');
        Route::get('/warehouse/supplies', \App\Livewire\Cabinet\Warehouse\Supplies::class)->name('cabinet.warehouse.supplies');
        Route::get('/warehouse/production', \App\Livewire\Cabinet\Warehouse\Production::class)->name('cabinet.warehouse.production');
        Route::get('/warehouse/transfers', \App\Livewire\Cabinet\Warehouse\Transfers::class)->name('cabinet.warehouse.transfers');
        Route::get('/warehouse/write-offs', \App\Livewire\Cabinet\Warehouse\WriteOffs::class)->name('cabinet.warehouse.write-offs');
        Route::get('/warehouse/movement', \App\Livewire\Cabinet\Warehouse\Movement::class)->name('cabinet.warehouse.movement');
        Route::get('/warehouse/inventory', \App\Livewire\Cabinet\Warehouse\Inventory::class)->name('cabinet.warehouse.inventory');
        Route::get('/warehouse/suppliers', \App\Livewire\Cabinet\Warehouse\Suppliers::class)->name('cabinet.warehouse.suppliers');
        Route::get('/warehouse/locations', \App\Livewire\Cabinet\Warehouse\Locations::class)->name('cabinet.warehouse.locations');
        Route::get('/warehouse/packaging', \App\Livewire\Cabinet\Warehouse\Packaging::class)->name('cabinet.warehouse.packaging');

        // Маркетинг
        Route::get('/customers', \App\Livewire\Customers\CustomerList::class)->name('cabinet.customers');
        Route::get('/marketing/groups', \App\Livewire\Cabinet\Marketing\Groups::class)->name('cabinet.marketing.groups');
        Route::get('/marketing/loyalty', \App\Livewire\Cabinet\Marketing\Loyalty::class)->name('cabinet.marketing.loyalty');
        Route::get('/marketing/promotions', \App\Livewire\Cabinet\Marketing\Promotions::class)->name('cabinet.marketing.promotions');

        // Доступ
        Route::get('/staff', \App\Livewire\Cabinet\Staff::class)->name('cabinet.staff');
        Route::get('/roles', \App\Livewire\Cabinet\Roles::class)->name('cabinet.roles');
        Route::get('/branches', \App\Livewire\Cabinet\Branches::class)->name('cabinet.branches');

        // Настройки
        Route::get('/settings', \App\Livewire\Cabinet\Settings::class)->name('cabinet.settings');
        Route::get('/subscription', \App\Livewire\Cabinet\Subscription::class)->name('cabinet.subscription');
        Route::get('/settings/orders', \App\Livewire\Cabinet\Settings\Orders::class)->name('cabinet.settings.orders');
        Route::get('/settings/delivery', \App\Livewire\Cabinet\Settings\Delivery::class)->name('cabinet.settings.delivery');
        Route::get('/settings/tables', \App\Livewire\Cabinet\Settings\Tables::class)->name('cabinet.settings.tables');
        Route::get('/settings/security', \App\Livewire\Cabinet\Settings\Security::class)->name('cabinet.settings.security');
        Route::get('/settings/receipt', \App\Livewire\Cabinet\Settings\Receipt::class)->name('cabinet.settings.receipt');
        Route::get('/settings/taxes', \App\Livewire\Cabinet\Settings\Taxes::class)->name('cabinet.settings.taxes');
    });

    /*
    |----------------------------------------------------------------------
    | Manager — Панель менеджера/директора филиала (/manager)
    |----------------------------------------------------------------------
    */
    Route::prefix('manager')->middleware('client_role:director,admin,head_waiter')->group(function () {
        Route::get('/', fn () => redirect()->route('manager.dashboard'));
        Route::get('/dashboard', \App\Livewire\Manager\Dashboard::class)->name('manager.dashboard');
        Route::get('/orders', \App\Livewire\Manager\Orders::class)->name('manager.orders');
        Route::get('/menu', \App\Livewire\Menu\Categories::class)->name('manager.menu');
        Route::get('/menu/items', \App\Livewire\Menu\Items::class)->name('manager.menu.items');
        Route::get('/floor', \App\Livewire\Manager\Floor::class)->name('manager.floor');
        Route::get('/staff', \App\Livewire\Staff\EmployeeList::class)->name('manager.staff');
        Route::get('/customers', \App\Livewire\Customers\CustomerList::class)->name('manager.customers');
        Route::get('/warehouse', \App\Livewire\Warehouse\Stock::class)->name('manager.warehouse');
        Route::get('/reports', \App\Livewire\Reports\Dashboard::class)->name('manager.reports');
        Route::get('/settings', \App\Livewire\Settings\General::class)->name('manager.settings');
    });

    /*
    |----------------------------------------------------------------------
    | Cashier — Панель кассира (/cashier)
    |----------------------------------------------------------------------
    */
    Route::prefix('cashier')->middleware('client_role:cashier,bartender,admin,owner')->group(function () {
        Route::get('/', fn () => redirect()->route('cashier.terminal'));
        Route::get('/terminal', \App\Livewire\Cashier\Terminal::class)->name('cashier.terminal');
    });

    /*
    |----------------------------------------------------------------------
    | Waiter — Панель официанта (/waiter)
    |----------------------------------------------------------------------
    */
    Route::prefix('waiter')->middleware('client_role:waiter,head_waiter,admin,owner')->group(function () {
        Route::get('/', fn () => redirect()->route('waiter.tables'));
        Route::get('/tables', \App\Livewire\Waiter\Tables::class)->name('waiter.tables');
        Route::get('/orders', \App\Livewire\Waiter\Orders::class)->name('waiter.orders');
    });

    /*
    |----------------------------------------------------------------------
    | Kitchen — Панель кухни (/kitchen)
    |----------------------------------------------------------------------
    */
    Route::prefix('kitchen')->middleware('client_role:cook,admin,owner')->group(function () {
        Route::get('/', \App\Livewire\Kitchen\Display::class)->name('kitchen.display');
    });

    /*
    |----------------------------------------------------------------------
    | Warehouse Panel — Панель кладовщика (/warehouse-panel)
    |----------------------------------------------------------------------
    */
    Route::prefix('warehouse-panel')->middleware('client_role:storekeeper,admin,owner')->group(function () {
        Route::get('/', fn () => redirect()->route('warehouse-panel.stock'));
        Route::get('/stock', \App\Livewire\WarehousePanel\Stock::class)->name('warehouse-panel.stock');
        Route::get('/supplies', \App\Livewire\WarehousePanel\Supplies::class)->name('warehouse-panel.supplies');
    });

    /*
    |----------------------------------------------------------------------
    | Legacy routes (backward compatibility)
    |----------------------------------------------------------------------
    */
    Route::get('/dashboard', fn () => redirect('/redirect'))->name('dashboard');
    Route::get('/pos', fn () => redirect()->route('cashier.terminal'))->name('pos');
    Route::get('/orders', fn () => redirect()->route('manager.orders'))->name('orders');
});
