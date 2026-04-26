<?php

declare(strict_types=1);

use App\Domain\Auth\Models\User;
use App\Domain\Floor\Models\Hall;
use App\Domain\Floor\Models\Table;
use App\Domain\Order\Models\Order;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Organization;
use App\Domain\Payment\Models\CashShift;
use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Models\PaymentMethod;
use App\Support\Enums\PaymentStatus;
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

    $this->cashPaymentMethod = PaymentMethod::create([
        'organization_id' => $this->organization->id,
        'name' => 'Наличные',
        'type' => 'cash',
        'is_fiscal' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->cardPaymentMethod = PaymentMethod::create([
        'organization_id' => $this->organization->id,
        'name' => 'Карта',
        'type' => 'card',
        'is_fiscal' => true,
        'is_active' => true,
        'sort_order' => 2,
    ]);

    $this->cashShift = CashShift::factory()->create([
        'branch_id' => $this->branch->id,
        'opened_by' => $this->user->id,
    ]);

    Sanctum::actingAs($this->user);
});

it('can list payment methods', function (): void {
    $response = $this->getJson('/api/v1/payments/methods?branch_id='.$this->branch->id);

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

it('can process a cash payment', function (): void {
    $order = Order::factory()->create([
        'branch_id' => $this->branch->id,
        'table_id' => $this->table->id,
        'total_amount' => 50000,
        'payment_status' => PaymentStatus::UNPAID,
    ]);

    $response = $this->postJson('/api/v1/payments/process?branch_id='.$this->branch->id, [
        'order_id' => $order->id,
        'method' => 'cash',
        'amount' => 50000,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.payment.status', 'completed');

    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'status' => 'completed',
    ]);
});

it('can process a card payment', function (): void {
    $order = Order::factory()->create([
        'branch_id' => $this->branch->id,
        'table_id' => $this->table->id,
        'total_amount' => 75000,
        'payment_status' => PaymentStatus::UNPAID,
    ]);

    $response = $this->postJson('/api/v1/payments/process?branch_id='.$this->branch->id, [
        'order_id' => $order->id,
        'method' => 'card',
        'amount' => 75000,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.payment.status', 'completed');
});

it('rejects payment for already paid order', function (): void {
    $order = Order::factory()->create([
        'branch_id' => $this->branch->id,
        'table_id' => $this->table->id,
        'total_amount' => 50000,
        'payment_status' => PaymentStatus::PAID,
    ]);

    $response = $this->postJson('/api/v1/payments/process?branch_id='.$this->branch->id, [
        'order_id' => $order->id,
        'method' => 'cash',
        'amount' => 50000,
    ]);

    $response->assertStatus(422);
});

it('can refund a payment', function (): void {
    $order = Order::factory()->create([
        'branch_id' => $this->branch->id,
        'table_id' => $this->table->id,
        'total_amount' => 50000,
        'payment_status' => PaymentStatus::PAID,
    ]);

    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'payment_method_id' => $this->cashPaymentMethod->id,
        'cash_shift_id' => $this->cashShift->id,
        'user_id' => $this->user->id,
        'amount' => 50000,
        'status' => 'completed',
        'paid_at' => now(),
    ]);

    $response = $this->postJson("/api/v1/payments/{$payment->id}/refund?branch_id=".$this->branch->id, [
        'reason' => 'Ошибка в заказе',
    ]);

    $response->assertOk();

    // Проверяем, что создан платёж с отрицательной суммой
    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'amount' => -50000,
        'status' => 'completed',
    ]);
});
