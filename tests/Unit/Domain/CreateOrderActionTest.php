<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Floor\Models\Table;
use App\Domain\Order\Actions\CreateOrderAction;
use App\Domain\Order\Models\Order;
use App\Domain\Organization\Models\Branch;
use App\Domain\Payment\Models\CashShift;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\OrderType;
use App\Support\Enums\OrderSource;
use App\Support\Enums\PaymentStatus;
use App\Support\Enums\TableStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateOrderActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateOrderAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CreateOrderAction();
    }

    /** @test */
    public function it_creates_order_with_correct_data(): void
    {
        $branch = Branch::factory()->create();
        $cashShift = CashShift::create([
            'branch_id' => $branch->id,
            'opened_by' => 1,
            'opened_at' => now(),
            'opening_cash' => 0,
            'status' => 'open',
        ]);

        $data = [
            'branch_id' => $branch->id,
            'type' => OrderType::DINE_IN,
            'source' => OrderSource::POS,
            'guests_count' => 4,
            'notes' => 'Тестовый заказ',
        ];

        $order = $this->action->execute($data);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($branch->id, $order->branch_id);
        $this->assertEquals($cashShift->id, $order->cash_shift_id);
        $this->assertEquals(OrderStatus::NEW, $order->status);
        $this->assertEquals(PaymentStatus::UNPAID, $order->payment_status);
        $this->assertEquals(4, $order->guests_count);
        $this->assertEquals('Тестовый заказ', $order->notes);
        $this->assertNotNull($order->order_number);
        $this->assertNotNull($order->opened_at);
    }

    /** @test */
    public function it_throws_exception_without_open_cash_shift(): void
    {
        $branch = Branch::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Нет открытой кассовой смены.');

        $this->action->execute([
            'branch_id' => $branch->id,
            'type' => OrderType::DINE_IN,
        ]);
    }

    /** @test */
    public function it_occupies_table_for_dine_in_orders(): void
    {
        $branch = Branch::factory()->create();
        CashShift::create([
            'branch_id' => $branch->id,
            'opened_by' => 1,
            'opened_at' => now(),
            'opening_cash' => 0,
            'status' => 'open',
        ]);

        $table = Table::factory()->create(['status' => 'free']);

        $order = $this->action->execute([
            'branch_id' => $branch->id,
            'table_id' => $table->id,
            'type' => OrderType::DINE_IN,
        ]);

        $table->refresh();

        $this->assertEquals($table->id, $order->table_id);
        $this->assertEquals(TableStatus::OCCUPIED, $table->status);
    }

    /** @test */
    public function it_does_not_occupy_table_for_takeaway_orders(): void
    {
        $branch = Branch::factory()->create();
        CashShift::create([
            'branch_id' => $branch->id,
            'opened_by' => 1,
            'opened_at' => now(),
            'opening_cash' => 0,
            'status' => 'open',
        ]);

        $table = Table::factory()->create(['status' => 'free']);

        $order = $this->action->execute([
            'branch_id' => $branch->id,
            'table_id' => $table->id,
            'type' => OrderType::TAKEAWAY,
        ]);

        $table->refresh();

        // Стол не должен быть занят для заказа навынос
        $this->assertEquals(TableStatus::FREE, $table->status);
    }
}
