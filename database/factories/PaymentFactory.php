<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Order\Models\Order;
use App\Domain\Payment\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'payment_method_id' => 1,
            'user_id' => 1,
            'amount' => fake()->numberBetween(10000, 500000),
            'status' => 'completed',
            'paid_at' => now(),
        ];
    }

    /**
     * Ожидающий оплату.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_at' => null,
        ]);
    }

    /**
     * Возврат.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'refunded',
        ]);
    }
}
