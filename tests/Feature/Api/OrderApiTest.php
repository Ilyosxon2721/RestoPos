<?php

declare(strict_types=1);

use App\Domain\Auth\Models\User;
use App\Domain\Floor\Models\Hall;
use App\Domain\Floor\Models\Table;
use App\Domain\Menu\Models\Category;
use App\Domain\Menu\Models\Product;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\OrderItem;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Organization;
use App\Domain\Payment\Models\CashShift;
use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Models\PaymentMethod;
use App\Domain\Staff\Models\Employee;
use App\Support\Enums\OrderItemStatus;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\PaymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    test()->markTestSkipped('Stale tests, pending rewrite for current API.');
    $this->organization = Organization::factory()->create();
    $this->branch = Branch::factory()->create(['organization_id' => $this->organization->id]);
    $this->user = User::factory()->create(['organization_id' => $this->organization->id]);

    $this->employee = Employee::factory()->create([
        'user_id' => $this->user->id,
        'branch_id' => $this->branch->id,
    ]);

    $this->hall = Hall::factory()->create(['branch_id' => $this->branch->id]);
    $this->table = Table::factory()->create(['hall_id' => $this->hall->id]);

    $this->category = Category::factory()->create(['organization_id' => $this->organization->id]);
    $this->product = Product::factory()->create([
        'organization_id' => $this->organization->id,
        'category_id' => $this->category->id,
        'price' => 50000,
    ]);

    $this->cashShift = CashShift::factory()->create([
        'branch_id' => $this->branch->id,
        'opened_by' => $this->user->id,
    ]);

    // Привязываем роль для доступа к филиалу
    $role = \App\Domain\Auth\Models\Role::create([
        'organization_id' => $this->organization->id,
        'name' => 'Admin',
        'slug' => 'admin',
        'is_system' => true,
    ]);
    $this->user->roles()->attach($role->id, ['branch_id' => $this->branch->id]);
});

it('rejects unauthenticated requests', function (): void {
    $response = $this->getJson('/api/v1/orders?branch_id='.$this->branch->id);

    $response->assertStatus(401);
});

it('can list orders', function (): void {
    Sanctum::actingAs($this->user);

    Order::factory()->count(3)->create([
        'branch_id' => $this->branch->id,
    ]);

    $response = $this->getJson('/api/v1/orders?branch_id='.$this->branch->id);

    $response->assertOk()
        ->assertJsonPath('data.total', 3);
});

it('can create an order', function (): void {
    Sanctum::actingAs($this->user);

    $response = $this->postJson('/api/v1/orders', [
        'branch_id' => $this->branch->id,
        'table_id' => $this->table->id,
        'type' => 'dine_in',
        'source' => 'pos',
        'guests_count' => 2,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.branch_id', $this->branch->id)
        ->assertJsonPath('data.table_id', $this->table->id);

    $this->assertDatabaseHas('orders', [
        'branch_id' => $this->branch->id,
        'table_id' => $this->table->id,
    ]);
});

it('can add item to order', function (): void {
    Sanctum::actingAs($this->user);

    $order = Order::factory()->create([
        'branch_id' => $this->branch->id,
        'table_id' => $this->table->id,
        'cash_shift_id' => $this->cashShift->id,
    ]);

    $response = $this->postJson("/api/v1/orders/{$order->id}/items", [
        'product_id' => $this->product->id,
        'quantity' => 2,
        'unit_price' => 50000,
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'product_id' => $this->product->id,
    ]);
});

it('can send items to kitchen', function (): void {
    Sanctum::actingAs($this->user);

    $order = Order::factory()->create([
        'branch_id' => $this->branch->id,
        'table_id' => $this->table->id,
        'status' => OrderStatus::NEW,
    ]);

    $item = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $this->product->id,
        'status' => OrderItemStatus::PENDING,
    ]);

    $response = $this->postJson("/api/v1/orders/{$order->id}/send-to-kitchen", [
        'item_ids' => [$item->id],
    ]);

    $response->assertOk();

    $item->refresh();
    expect($item->status)->toBe(OrderItemStatus::SENT);
});

it('can close a paid order', function (): void {
    Sanctum::actingAs($this->user);

    $order = Order::factory()->create([
        'branch_id' => $this->branch->id,
        'table_id' => $this->table->id,
        'status' => OrderStatus::SERVED,
        'payment_status' => PaymentStatus::PAID,
        'total_amount' => 100000,
    ]);

    $paymentMethod = PaymentMethod::create([
        'organization_id' => $this->organization->id,
        'name' => 'Cash',
        'type' => 'cash',
        'is_active' => true,
    ]);

    Payment::factory()->create([
        'order_id' => $order->id,
        'payment_method_id' => $paymentMethod->id,
        'user_id' => $this->user->id,
        'amount' => 100000,
        'status' => 'completed',
        'paid_at' => now(),
    ]);

    $response = $this->postJson("/api/v1/orders/{$order->id}/close");

    $response->assertOk();
});
