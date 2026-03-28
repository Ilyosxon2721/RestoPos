<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Support\Enums\OrderItemStatus;
use Tests\TestCase;

class OrderItemStatusTest extends TestCase
{
    /** @test */
    public function it_has_correct_labels(): void
    {
        $this->assertEquals('Ожидает', OrderItemStatus::PENDING->label());
        $this->assertEquals('Отправлен', OrderItemStatus::SENT->label());
        $this->assertEquals('Готовится', OrderItemStatus::PREPARING->label());
        $this->assertEquals('Готов', OrderItemStatus::READY->label());
        $this->assertEquals('Подан', OrderItemStatus::SERVED->label());
        $this->assertEquals('Отменён', OrderItemStatus::CANCELLED->label());
    }

    /** @test */
    public function it_allows_valid_transitions(): void
    {
        $this->assertTrue(OrderItemStatus::PENDING->canTransitionTo(OrderItemStatus::SENT));
        $this->assertTrue(OrderItemStatus::PENDING->canTransitionTo(OrderItemStatus::CANCELLED));
        $this->assertTrue(OrderItemStatus::SENT->canTransitionTo(OrderItemStatus::PREPARING));
        $this->assertTrue(OrderItemStatus::SENT->canTransitionTo(OrderItemStatus::CANCELLED));
        $this->assertTrue(OrderItemStatus::PREPARING->canTransitionTo(OrderItemStatus::READY));
        $this->assertTrue(OrderItemStatus::PREPARING->canTransitionTo(OrderItemStatus::CANCELLED));
        $this->assertTrue(OrderItemStatus::READY->canTransitionTo(OrderItemStatus::SERVED));
    }

    /** @test */
    public function it_rejects_invalid_transitions(): void
    {
        // SERVED - терминальный статус
        $this->assertFalse(OrderItemStatus::SERVED->canTransitionTo(OrderItemStatus::PENDING));
        $this->assertFalse(OrderItemStatus::SERVED->canTransitionTo(OrderItemStatus::CANCELLED));

        // CANCELLED - терминальный статус
        $this->assertFalse(OrderItemStatus::CANCELLED->canTransitionTo(OrderItemStatus::PENDING));
        $this->assertFalse(OrderItemStatus::CANCELLED->canTransitionTo(OrderItemStatus::SENT));

        // Нельзя пропускать статусы
        $this->assertFalse(OrderItemStatus::PENDING->canTransitionTo(OrderItemStatus::READY));
        $this->assertFalse(OrderItemStatus::PENDING->canTransitionTo(OrderItemStatus::SERVED));
        $this->assertFalse(OrderItemStatus::SENT->canTransitionTo(OrderItemStatus::SERVED));

        // Нельзя идти назад
        $this->assertFalse(OrderItemStatus::READY->canTransitionTo(OrderItemStatus::PREPARING));
        $this->assertFalse(OrderItemStatus::READY->canTransitionTo(OrderItemStatus::CANCELLED));
    }

    /** @test */
    public function it_has_correct_colors(): void
    {
        $this->assertEquals('gray', OrderItemStatus::PENDING->color());
        $this->assertEquals('blue', OrderItemStatus::SENT->color());
        $this->assertEquals('orange', OrderItemStatus::PREPARING->color());
        $this->assertEquals('green', OrderItemStatus::READY->color());
        $this->assertEquals('purple', OrderItemStatus::SERVED->color());
        $this->assertEquals('red', OrderItemStatus::CANCELLED->color());
    }
}
