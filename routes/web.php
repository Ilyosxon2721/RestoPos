<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/login'));

// Гостевые маршруты
Route::middleware('guest')->group(function () {
    Route::get('/login', \App\Livewire\Auth\Login::class)->name('login');
});

// Авторизованные маршруты
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', \App\Livewire\Dashboard::class)->name('dashboard');
    Route::get('/pos', \App\Livewire\Pos\Terminal::class)->name('pos');
    Route::get('/orders', \App\Livewire\Orders\OrderList::class)->name('orders');
    Route::get('/kitchen', \App\Livewire\Kitchen\Display::class)->name('kitchen');
    Route::get('/menu', fn() => redirect('/menu/categories'));
    Route::get('/menu/categories', \App\Livewire\Menu\Categories::class)->name('menu.categories');
    Route::get('/menu/items', \App\Livewire\Menu\Items::class)->name('menu.items');
    Route::get('/customers', \App\Livewire\Customers\CustomerList::class)->name('customers');
    Route::get('/warehouse', \App\Livewire\Warehouse\Stock::class)->name('warehouse');
    Route::get('/staff', \App\Livewire\Staff\EmployeeList::class)->name('staff');
    Route::get('/reports', \App\Livewire\Reports\Dashboard::class)->name('reports');
    Route::get('/settings', \App\Livewire\Settings\General::class)->name('settings');

    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});
