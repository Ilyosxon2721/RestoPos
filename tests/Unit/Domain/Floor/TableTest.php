<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Floor;

use App\Support\Enums\TableStatus;
use Tests\TestCase;

class TableTest extends TestCase
{
    /** @test */
    public function free_table_can_accept_new_order(): void
    {
        $this->assertTrue(TableStatus::FREE->canAcceptNewOrder());
    }

    /** @test */
    public function occupied_table_cannot_accept_new_order(): void
    {
        $this->assertFalse(TableStatus::OCCUPIED->canAcceptNewOrder());
    }

    /** @test */
    public function reserved_table_cannot_accept_new_order(): void
    {
        $this->assertFalse(TableStatus::RESERVED->canAcceptNewOrder());
    }

    /** @test */
    public function free_table_can_be_reserved(): void
    {
        $this->assertTrue(TableStatus::FREE->canBeReserved());
    }

    /** @test */
    public function occupied_table_cannot_be_reserved(): void
    {
        $this->assertFalse(TableStatus::OCCUPIED->canBeReserved());
    }

    /** @test */
    public function table_status_has_labels(): void
    {
        $this->assertEquals('Свободен', TableStatus::FREE->label());
        $this->assertEquals('Занят', TableStatus::OCCUPIED->label());
        $this->assertEquals('Забронирован', TableStatus::RESERVED->label());
    }

    /** @test */
    public function table_status_has_colors(): void
    {
        $this->assertEquals('green', TableStatus::FREE->color());
        $this->assertEquals('red', TableStatus::OCCUPIED->color());
        $this->assertEquals('orange', TableStatus::RESERVED->color());
    }
}
