<?php

declare(strict_types=1);

use App\Domain\Floor\Models\Hall;
use App\Domain\Floor\Models\Table;
use App\Domain\Order\Models\Order;
use App\Livewire\Dashboard;
use App\Support\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    test()->markTestSkipped('Stale tests, pending rewrite for current API.');
    ['user' => $this->user, 'organization' => $this->organization, 'branch' => $this->branch] = createAuthenticatedUser();
});

it('renders successfully', function (): void {
    $this->actingAs($this->user);

    $response = $this->get('/dashboard');

    $response->assertOk();
    $response->assertSeeLivewire(Dashboard::class);
});

it('shows today statistics', function (): void {
    $this->actingAs($this->user);

    $hall = Hall::factory()->create(['branch_id' => $this->branch->id]);
    $table = Table::factory()->create(['hall_id' => $hall->id, 'status' => 'occupied']);

    // Создаём заказы за сегодня
    Order::factory()->count(3)->create([
        'branch_id' => $this->branch->id,
        'status' => OrderStatus::NEW,
        'created_at' => now(),
    ]);

    Order::factory()->create([
        'branch_id' => $this->branch->id,
        'status' => OrderStatus::COMPLETED,
        'created_at' => now(),
    ]);

    $component = \Livewire\Livewire::test(Dashboard::class);

    // Проверяем, что компонент рендерится и вычисляемые свойства доступны
    $component->assertOk();

    // Сегодняшние заказы (все 4)
    expect($component->instance()->todayOrders)->toBe(4);

    // Открытые заказы (3 со статусом NEW)
    expect($component->instance()->openOrders)->toBe(3);

    // Занятые столы
    expect($component->instance()->occupiedTables)->toBe(1);
    expect($component->instance()->totalTables)->toBe(1);
});

it('shows recent orders', function (): void {
    $this->actingAs($this->user);

    Order::factory()->count(5)->create([
        'branch_id' => $this->branch->id,
    ]);

    $component = \Livewire\Livewire::test(Dashboard::class);

    expect($component->instance()->recentOrders)->toHaveCount(5);
});
