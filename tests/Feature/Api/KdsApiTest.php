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
use App\Support\Enums\OrderItemStatus;
use App\Support\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->organization = Organization::factory()->create();
    $this->branch = Branch::factory()->create(['organization_id' => $this->organization->id]);
    $this->user = User::factory()->create(['organization_id' => $this->organization->id]);

    $role = \App\Domain\Auth\Models\Role::create([
        'organization_id' => $this->organization->id,
        'name' => 'Admin',
        'slug' => 'admin',
        'is_system' => true,
    ]);
    $this->user->roles()->attach($role->id, ['branch_id' => $this->branch->id]);

    $hall = Hall::factory()->create(['branch_id' => $this->branch->id]);
    $this->table = Table::factory()->create(['hall_id' => $hall->id]);

    $this->category = Category::factory()->create(['organization_id' => $this->organization->id]);
    $this->product = Product::factory()->create([
        'organization_id' => $this->organization->id,
        'category_id' => $this->category->id,
    ]);

    Sanctum::actingAs($this->user);
});

it('can list kitchen orders', function (): void {
    $order = Order::factory()->create([
        'branch_id' => $this->branch->id,
        'table_id' => $this->table->id,
        'status' => OrderStatus::ACCEPTED,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $this->product->id,
        'status' => OrderItemStatus::SENT,
        'sent_to_kitchen_at' => now(),
    ]);

    $response = $this->getJson('/api/v1/kds/orders?branch_id='.$this->branch->id);

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

it('can start preparing an item', function (): void {
    $order = Order::factory()->create([
        'branch_id' => $this->branch->id,
        'table_id' => $this->table->id,
        'status' => OrderStatus::ACCEPTED,
    ]);

    $item = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $this->product->id,
        'status' => OrderItemStatus::SENT,
        'sent_to_kitchen_at' => now(),
    ]);

    $response = $this->postJson("/api/v1/kds/items/{$item->id}/start?branch_id=".$this->branch->id);

    $response->assertOk();

    $item->refresh();
    expect($item->status)->toBe(OrderItemStatus::PREPARING);
});

it('can mark item as ready', function (): void {
    $order = Order::factory()->create([
        'branch_id' => $this->branch->id,
        'table_id' => $this->table->id,
        'status' => OrderStatus::PREPARING,
    ]);

    $item = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $this->product->id,
        'status' => OrderItemStatus::PREPARING,
        'sent_to_kitchen_at' => now()->subMinutes(10),
    ]);

    $response = $this->postJson("/api/v1/kds/items/{$item->id}/ready?branch_id=".$this->branch->id);

    $response->assertOk();

    $item->refresh();
    expect($item->status)->toBe(OrderItemStatus::READY);
    expect($item->ready_at)->not->toBeNull();
});

it('can mark item as served', function (): void {
    $order = Order::factory()->create([
        'branch_id' => $this->branch->id,
        'table_id' => $this->table->id,
        'status' => OrderStatus::READY,
    ]);

    $item = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $this->product->id,
        'status' => OrderItemStatus::READY,
        'sent_to_kitchen_at' => now()->subMinutes(15),
        'ready_at' => now()->subMinutes(2),
    ]);

    $response = $this->postJson("/api/v1/kds/items/{$item->id}/served?branch_id=".$this->branch->id);

    $response->assertOk();

    $item->refresh();
    expect($item->status)->toBe(OrderItemStatus::SERVED);
});

it('cannot start preparing an item that is not sent', function (): void {
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

    $response = $this->postJson("/api/v1/kds/items/{$item->id}/start?branch_id=".$this->branch->id);

    $response->assertStatus(422);
});

it('can view kitchen statistics', function (): void {
    $response = $this->getJson('/api/v1/kds/statistics?branch_id='.$this->branch->id);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'pending',
                'preparing',
                'ready',
                'avg_prep_time',
            ],
        ]);
});
