<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Order\Models\Order;
use App\Domain\Organization\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'order_number' => now()->format('Ymd') . '-' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'type' => 'dine_in',
            'source' => 'pos',
            'status' => 'new',
            'payment_status' => 'unpaid',
            'guests_count' => 1,
            'subtotal' => 0,
            'total_amount' => 0,
        ];
    }

    /**
     * Заказ на вынос.
     */
    public function takeaway(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'takeaway',
        ]);
    }

    /**
     * Заказ на доставку.
     */
    public function delivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'delivery',
        ]);
    }

    /**
     * Завершённый заказ.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'payment_status' => 'paid',
            'closed_at' => now(),
        ]);
    }

    /**
     * Отменённый заказ.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'closed_at' => now(),
        ]);
    }
}
