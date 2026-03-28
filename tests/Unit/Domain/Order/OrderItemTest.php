<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Order;

use App\Support\Enums\OrderItemStatus;
use Tests\TestCase;

class OrderItemTest extends TestCase
{
    /** @test */
    public function item_status_transitions_are_validated(): void
    {
        $status = OrderItemStatus::PENDING;

        $this->assertTrue($status->canTransitionTo(OrderItemStatus::SENT));
        $this->assertTrue($status->canTransitionTo(OrderItemStatus::CANCELLED));
        $this->assertFalse($status->canTransitionTo(OrderItemStatus::READY));
    }

    /** @test */
    public function sent_item_can_transition_to_preparing(): void
    {
        $status = OrderItemStatus::SENT;

        $this->assertTrue($status->canTransitionTo(OrderItemStatus::PREPARING));
        $this->assertTrue($status->canTransitionTo(OrderItemStatus::CANCELLED));
        $this->assertFalse($status->canTransitionTo(OrderItemStatus::SERVED));
    }

    /** @test */
    public function ready_item_can_only_be_served(): void
    {
        $status = OrderItemStatus::READY;

        $this->assertTrue($status->canTransitionTo(OrderItemStatus::SERVED));
        $this->assertFalse($status->canTransitionTo(OrderItemStatus::CANCELLED));
        $this->assertFalse($status->canTransitionTo(OrderItemStatus::PENDING));
    }

    /** @test */
    public function served_item_cannot_transition(): void
    {
        $status = OrderItemStatus::SERVED;

        $this->assertFalse($status->canTransitionTo(OrderItemStatus::READY));
        $this->assertFalse($status->canTransitionTo(OrderItemStatus::CANCELLED));
    }

    /** @test */
    public function item_status_has_labels(): void
    {
        $this->assertEquals('Ожидает', OrderItemStatus::PENDING->label());
        $this->assertEquals('Готовится', OrderItemStatus::PREPARING->label());
        $this->assertEquals('Готов', OrderItemStatus::READY->label());
    }

    /** @test */
    public function item_status_has_colors(): void
    {
        $this->assertEquals('gray', OrderItemStatus::PENDING->color());
        $this->assertEquals('orange', OrderItemStatus::PREPARING->color());
        $this->assertEquals('green', OrderItemStatus::READY->color());
    }
}
