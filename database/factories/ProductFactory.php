<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Menu\Models\Category;
use App\Domain\Menu\Models\Product;
use App\Domain\Organization\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $price = fake()->numberBetween(10000, 100000);

        return [
            'organization_id' => Organization::factory(),
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true),
            'type' => 'dish',
            'price' => $price,
            'cost_price' => (int) ($price * 0.3),
            'cooking_time' => fake()->numberBetween(5, 45),
            'is_visible' => true,
            'is_available' => true,
        ];
    }

    /**
     * Продукт типа "напиток".
     */
    public function drink(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'drink',
            'cooking_time' => fake()->numberBetween(1, 10),
        ]);
    }

    /**
     * Продукт в стоп-листе.
     */
    public function inStopList(): static
    {
        return $this->state(fn (array $attributes) => [
            'in_stop_list' => true,
            'stop_list_reason' => 'Нет в наличии',
        ]);
    }

    /**
     * Недоступный продукт.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }
}
