<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Domain\Order\Models\Order;
use App\Domain\Organization\Models\Branch;
use App\Domain\Payment\Models\Payment;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\PaymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_order_number(): void
    {
        $branch = Branch::factory()->create();

        $order = Order::factory()->create([
            'branch_id' => $branch->id,
            'order_number' => null,
        ]);

        $this->assertNotNull($order->order_number);
        // Формат: YYYYMMDD-0001
        $this->assertMatchesRegularExpression('/^\d{8}-\d{4}$/', $order->order_number);
    }

    /** @test */
    public function it_generates_sequential_order_numbers(): void
    {
        $branch = Branch::factory()->create();

        $order1 = Order::factory()->create([
            'branch_id' => $branch->id,
            'order_number' => null,
        ]);

        $order2 = Order::factory()->create([
            'branch_id' => $branch->id,
            'order_number' => null,
        ]);

        $number1 = (int) substr($order1->order_number, -4);
        $number2 = (int) substr($order2->order_number, -4);

        $this->assertEquals($number1 + 1, $number2);
    }

    /** @test */
    public function it_calculates_remaining_amount(): void
    {
        $this->markTestSkipped('PaymentFactory requires PaymentMethod factory; rewrite when PaymentMethod factory exists.');
        $order = Order::factory()->create([
            'total_amount' => 100000,
            'status' => 'new',
        ]);

        // Без оплат, остаток = полная сумма
        $this->assertEquals(100000, $order->getRemainingAmount());

        // Частичная оплата
        Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => 30000,
            'status' => 'completed',
        ]);

        $this->assertEquals(70000, $order->getRemainingAmount());

        // Полная оплата
        Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => 70000,
            'status' => 'completed',
        ]);

        $this->assertEquals(0, $order->getRemainingAmount());
    }

    /** @test */
    public function it_ignores_non_completed_payments_in_remaining_amount(): void
    {
        $this->markTestSkipped('PaymentFactory requires PaymentMethod factory; rewrite when PaymentMethod factory exists.');
        $order = Order::factory()->create([
            'total_amount' => 100000,
            'status' => 'new',
        ]);

        Payment::factory()->pending()->create([
            'order_id' => $order->id,
            'amount' => 50000,
        ]);

        // Незавершённый платёж не учитывается
        $this->assertEquals(100000, $order->getRemainingAmount());
    }

    /** @test */
    public function it_checks_if_order_is_open(): void
    {
        $newOrder = Order::factory()->create(['status' => 'new']);
        $this->assertTrue($newOrder->isOpen());

        $preparingOrder = Order::factory()->create(['status' => 'preparing']);
        $this->assertTrue($preparingOrder->isOpen());

        $completedOrder = Order::factory()->completed()->create();
        $this->assertFalse($completedOrder->isOpen());

        $cancelledOrder = Order::factory()->cancelled()->create();
        $this->assertFalse($cancelledOrder->isOpen());
    }

    /** @test */
    public function it_checks_if_order_can_be_modified(): void
    {
        // Открытый неоплаченный заказ можно изменить
        $order = Order::factory()->create([
            'status' => 'new',
            'payment_status' => 'unpaid',
        ]);
        $this->assertTrue($order->canModify());

        // Оплаченный заказ нельзя изменить
        $paidOrder = Order::factory()->create([
            'status' => 'new',
            'payment_status' => 'paid',
        ]);
        $this->assertFalse($paidOrder->canModify());

        // Завершённый заказ нельзя изменить
        $completedOrder = Order::factory()->completed()->create();
        $this->assertFalse($completedOrder->canModify());
    }

    /** @test */
    public function it_transitions_status_correctly(): void
    {
        $order = Order::factory()->create(['status' => 'new']);

        // Допустимый переход
        $result = $order->transitionTo(OrderStatus::ACCEPTED);
        $this->assertTrue($result);
        $this->assertEquals(OrderStatus::ACCEPTED, $order->status);

        // Недопустимый переход
        $result = $order->transitionTo(OrderStatus::COMPLETED);
        $this->assertFalse($result);
        $this->assertEquals(OrderStatus::ACCEPTED, $order->status);
    }

    /** @test */
    public function it_sets_closed_at_when_completing_or_cancelling(): void
    {
        $order = Order::factory()->create(['status' => 'served']);

        $this->assertNull($order->closed_at);

        $order->transitionTo(OrderStatus::COMPLETED);
        $order->refresh();

        $this->assertNotNull($order->closed_at);
    }

    /** @test */
    public function it_updates_payment_status_based_on_payments(): void
    {
        $this->markTestSkipped('PaymentFactory requires PaymentMethod factory; rewrite when PaymentMethod factory exists.');
        $order = Order::factory()->create([
            'total_amount' => 100000,
            'status' => 'new',
            'payment_status' => 'unpaid',
        ]);

        // Без оплат - UNPAID
        $order->updatePaymentStatus();
        $this->assertEquals(PaymentStatus::UNPAID, $order->payment_status);

        // Частичная оплата - PARTIAL
        Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => 50000,
            'status' => 'completed',
        ]);
        $order->updatePaymentStatus();
        $this->assertEquals(PaymentStatus::PARTIAL, $order->payment_status);

        // Полная оплата - PAID
        Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => 50000,
            'status' => 'completed',
        ]);
        $order->updatePaymentStatus();
        $this->assertEquals(PaymentStatus::PAID, $order->payment_status);
    }
}
