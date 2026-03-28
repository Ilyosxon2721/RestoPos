<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Order;

use App\Domain\Order\Models\Order;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\PaymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function order_status_transitions_are_validated(): void
    {
        $status = OrderStatus::NEW;

        $this->assertTrue($status->canTransitionTo(OrderStatus::ACCEPTED));
        $this->assertTrue($status->canTransitionTo(OrderStatus::CANCELLED));
        $this->assertFalse($status->canTransitionTo(OrderStatus::COMPLETED));
        $this->assertFalse($status->canTransitionTo(OrderStatus::READY));
    }

    /** @test */
    public function completed_order_cannot_transition(): void
    {
        $status = OrderStatus::COMPLETED;

        $this->assertFalse($status->canTransitionTo(OrderStatus::NEW));
        $this->assertFalse($status->canTransitionTo(OrderStatus::CANCELLED));
    }

    /** @test */
    public function cancelled_order_cannot_transition(): void
    {
        $status = OrderStatus::CANCELLED;

        $this->assertFalse($status->canTransitionTo(OrderStatus::NEW));
        $this->assertFalse($status->canTransitionTo(OrderStatus::COMPLETED));
    }

    /** @test */
    public function order_status_has_labels(): void
    {
        $this->assertEquals('Новый', OrderStatus::NEW->label());
        $this->assertEquals('Завершён', OrderStatus::COMPLETED->label());
        $this->assertEquals('Отменён', OrderStatus::CANCELLED->label());
    }

    /** @test */
    public function order_status_has_colors(): void
    {
        $this->assertEquals('blue', OrderStatus::NEW->color());
        $this->assertEquals('red', OrderStatus::CANCELLED->color());
        $this->assertEquals('gray', OrderStatus::COMPLETED->color());
    }

    /** @test */
    public function active_statuses_exclude_completed_and_cancelled(): void
    {
        $active = OrderStatus::activeStatuses();

        $this->assertContains(OrderStatus::NEW, $active);
        $this->assertContains(OrderStatus::PREPARING, $active);
        $this->assertNotContains(OrderStatus::COMPLETED, $active);
        $this->assertNotContains(OrderStatus::CANCELLED, $active);
    }

    /** @test */
    public function payment_status_enum_works(): void
    {
        $this->assertInstanceOf(PaymentStatus::class, PaymentStatus::UNPAID);
        $this->assertInstanceOf(PaymentStatus::class, PaymentStatus::PAID);
    }
}
