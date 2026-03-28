<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Menu\Models\Product;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $unitPrice = fake()->numberBetween(10000, 100000);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'name' => fake()->words(3, true),
            'quantity' => 1,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice,
            'status' => 'pending',
        ];
    }

    /**
     * Позиция с указанным количеством.
     */
    public function withQuantity(int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $quantity,
            'total_price' => ($attributes['unit_price'] ?? 50000) * $quantity,
        ]);
    }

    /**
     * Отменённая позиция.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_reason' => 'Отменено гостем',
        ]);
    }
}
