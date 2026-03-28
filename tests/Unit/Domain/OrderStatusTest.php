<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Support\Enums\OrderStatus;
use Tests\TestCase;

class OrderStatusTest extends TestCase
{
    /** @test */
    public function it_has_correct_labels(): void
    {
        $this->assertEquals('Новый', OrderStatus::NEW->label());
        $this->assertEquals('Принят', OrderStatus::ACCEPTED->label());
        $this->assertEquals('Готовится', OrderStatus::PREPARING->label());
        $this->assertEquals('Готов', OrderStatus::READY->label());
        $this->assertEquals('Подан', OrderStatus::SERVED->label());
        $this->assertEquals('Завершён', OrderStatus::COMPLETED->label());
        $this->assertEquals('Отменён', OrderStatus::CANCELLED->label());
    }

    /** @test */
    public function it_allows_valid_transitions(): void
    {
        $this->assertTrue(OrderStatus::NEW->canTransitionTo(OrderStatus::ACCEPTED));
        $this->assertTrue(OrderStatus::NEW->canTransitionTo(OrderStatus::CANCELLED));
        $this->assertTrue(OrderStatus::ACCEPTED->canTransitionTo(OrderStatus::PREPARING));
        $this->assertTrue(OrderStatus::ACCEPTED->canTransitionTo(OrderStatus::CANCELLED));
        $this->assertTrue(OrderStatus::PREPARING->canTransitionTo(OrderStatus::READY));
        $this->assertTrue(OrderStatus::READY->canTransitionTo(OrderStatus::SERVED));
        $this->assertTrue(OrderStatus::SERVED->canTransitionTo(OrderStatus::COMPLETED));
    }

    /** @test */
    public function it_rejects_invalid_transitions(): void
    {
        // COMPLETED не может перейти ни в какой статус
        $this->assertFalse(OrderStatus::COMPLETED->canTransitionTo(OrderStatus::NEW));
        $this->assertFalse(OrderStatus::COMPLETED->canTransitionTo(OrderStatus::ACCEPTED));
        $this->assertFalse(OrderStatus::COMPLETED->canTransitionTo(OrderStatus::CANCELLED));

        // CANCELLED не может перейти ни в какой статус
        $this->assertFalse(OrderStatus::CANCELLED->canTransitionTo(OrderStatus::NEW));
        $this->assertFalse(OrderStatus::CANCELLED->canTransitionTo(OrderStatus::COMPLETED));

        // Нельзя пропускать статусы
        $this->assertFalse(OrderStatus::NEW->canTransitionTo(OrderStatus::COMPLETED));
        $this->assertFalse(OrderStatus::NEW->canTransitionTo(OrderStatus::READY));
        $this->assertFalse(OrderStatus::PREPARING->canTransitionTo(OrderStatus::SERVED));

        // Нельзя идти назад
        $this->assertFalse(OrderStatus::SERVED->canTransitionTo(OrderStatus::NEW));
        $this->assertFalse(OrderStatus::READY->canTransitionTo(OrderStatus::PREPARING));
    }

    /** @test */
    public function it_returns_active_statuses(): void
    {
        $active = OrderStatus::activeStatuses();

        $this->assertContains(OrderStatus::NEW, $active);
        $this->assertContains(OrderStatus::ACCEPTED, $active);
        $this->assertContains(OrderStatus::PREPARING, $active);
        $this->assertContains(OrderStatus::READY, $active);
        $this->assertContains(OrderStatus::SERVED, $active);

        $this->assertNotContains(OrderStatus::COMPLETED, $active);
        $this->assertNotContains(OrderStatus::CANCELLED, $active);
    }

    /** @test */
    public function it_has_correct_colors(): void
    {
        $this->assertEquals('blue', OrderStatus::NEW->color());
        $this->assertEquals('cyan', OrderStatus::ACCEPTED->color());
        $this->assertEquals('orange', OrderStatus::PREPARING->color());
        $this->assertEquals('green', OrderStatus::READY->color());
        $this->assertEquals('purple', OrderStatus::SERVED->color());
        $this->assertEquals('gray', OrderStatus::COMPLETED->color());
        $this->assertEquals('red', OrderStatus::CANCELLED->color());
    }
}
