<?php

declare(strict_types=1);

use App\Domain\Auth\Models\User;
use App\Domain\Customer\Models\Customer;
use App\Domain\Order\Models\Order;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Organization;
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

    Sanctum::actingAs($this->user);
});

it('can list customers', function (): void {
    Customer::factory()->count(5)->create([
        'organization_id' => $this->organization->id,
    ]);

    $response = $this->getJson('/api/v1/customers?branch_id=' . $this->branch->id);

    $response->assertOk()
        ->assertJsonPath('data.total', 5);
});

it('can create a customer', function (): void {
    $response = $this->postJson('/api/v1/customers?branch_id=' . $this->branch->id, [
        'first_name' => 'Иван',
        'last_name' => 'Петров',
        'phone' => '+998901234567',
        'email' => 'ivan@test.com',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.first_name', 'Иван')
        ->assertJsonPath('data.phone', '+998901234567');

    $this->assertDatabaseHas('customers', [
        'first_name' => 'Иван',
        'phone' => '+998901234567',
        'organization_id' => $this->organization->id,
    ]);
});

it('can search customers', function (): void {
    Customer::factory()->create([
        'organization_id' => $this->organization->id,
        'first_name' => 'Алексей',
        'phone' => '+998901111111',
    ]);

    Customer::factory()->create([
        'organization_id' => $this->organization->id,
        'first_name' => 'Борис',
        'phone' => '+998902222222',
    ]);

    $response = $this->getJson('/api/v1/customers/search?q=Алексей&branch_id=' . $this->branch->id);

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

it('can add bonus to customer', function (): void {
    $customer = Customer::factory()->create([
        'organization_id' => $this->organization->id,
        'bonus_balance' => 0,
    ]);

    $response = $this->postJson("/api/v1/customers/{$customer->id}/bonus?branch_id=" . $this->branch->id, [
        'amount' => 5000,
        'type' => 'accrual',
        'description' => 'Бонус за первый визит',
    ]);

    $response->assertOk();

    $customer->refresh();
    expect((float) $customer->bonus_balance)->toBe(5000.00);

    $this->assertDatabaseHas('bonus_transactions', [
        'customer_id' => $customer->id,
        'amount' => 5000,
        'type' => 'accrual',
    ]);
});

it('can view customer history', function (): void {
    $customer = Customer::factory()->create([
        'organization_id' => $this->organization->id,
    ]);

    Order::factory()->count(3)->create([
        'branch_id' => $this->branch->id,
        'customer_id' => $customer->id,
    ]);

    $response = $this->getJson("/api/v1/customers/{$customer->id}/history?branch_id=" . $this->branch->id);

    $response->assertOk()
        ->assertJsonPath('data.total', 3);
});

it('can update a customer', function (): void {
    $customer = Customer::factory()->create([
        'organization_id' => $this->organization->id,
        'first_name' => 'Старое Имя',
    ]);

    $response = $this->putJson("/api/v1/customers/{$customer->id}?branch_id=" . $this->branch->id, [
        'first_name' => 'Новое Имя',
    ]);

    $response->assertOk();

    $customer->refresh();
    expect($customer->first_name)->toBe('Новое Имя');
});

it('can delete a customer', function (): void {
    $customer = Customer::factory()->create([
        'organization_id' => $this->organization->id,
    ]);

    $response = $this->deleteJson("/api/v1/customers/{$customer->id}?branch_id=" . $this->branch->id);

    $response->assertOk();

    $this->assertSoftDeleted('customers', ['id' => $customer->id]);
});
