<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Landing Page (Публичная)
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => view('landing'))->name('landing');

/*
|--------------------------------------------------------------------------
| Регистрация
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', \App\Livewire\Auth\Login::class)->name('login');
    Route::get('/register', fn () => redirect('/login')); // TODO: Registration form
});

/*
|--------------------------------------------------------------------------
| Super Admin Panel (/admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    // Гостевые маршруты Super Admin
    Route::get('/login', \App\Livewire\Admin\Login::class)->name('admin.login');
    Route::post('/logout', function () {
        auth('platform')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('admin.login');
    })->name('admin.logout');

    // Защищённые маршруты Super Admin
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
| Client Routes (Require Authentication)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Redirect after login based on role
    Route::get('/redirect', [\App\Application\Http\Middleware\RedirectByRole::class, 'handle'])->name('role.redirect');

    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');

    /*
    |----------------------------------------------------------------------
    | Cabinet — Панель владельца организации (/cabinet)
    |----------------------------------------------------------------------
    */
    Route::prefix('cabinet')->middleware('client_role:owner')->group(function () {
        Route::get('/', fn () => redirect()->route('cabinet.dashboard'));
        Route::get('/dashboard', \App\Livewire\Cabinet\Dashboard::class)->name('cabinet.dashboard');
        Route::get('/branches', \App\Livewire\Cabinet\Branches::class)->name('cabinet.branches');
        Route::get('/menu', \App\Livewire\Menu\Categories::class)->name('cabinet.menu');
        Route::get('/menu/items', \App\Livewire\Menu\Items::class)->name('cabinet.menu.items');
        Route::get('/staff', \App\Livewire\Cabinet\Staff::class)->name('cabinet.staff');
        Route::get('/customers', \App\Livewire\Customers\CustomerList::class)->name('cabinet.customers');
        Route::get('/warehouse', \App\Livewire\Warehouse\Stock::class)->name('cabinet.warehouse');
        Route::get('/finance', \App\Livewire\Cabinet\Finance::class)->name('cabinet.finance');
        Route::get('/reports', \App\Livewire\Cabinet\Reports::class)->name('cabinet.reports');
        Route::get('/subscription', \App\Livewire\Cabinet\Subscription::class)->name('cabinet.subscription');
        Route::get('/settings', \App\Livewire\Cabinet\Settings::class)->name('cabinet.settings');
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
    Route::get('/dashboard', fn () => redirect()->route('cabinet.dashboard'))->name('dashboard');
    Route::get('/pos', fn () => redirect()->route('cashier.terminal'))->name('pos');
    Route::get('/orders', fn () => redirect()->route('manager.orders'))->name('orders');
});
