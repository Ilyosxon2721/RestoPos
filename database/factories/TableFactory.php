<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Floor\Models\Hall;
use App\Domain\Floor\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Table>
 */
class TableFactory extends Factory
{
    protected $model = Table::class;

    public function definition(): array
    {
        return [
            'hall_id' => Hall::factory(),
            'name' => 'Стол '.fake()->numberBetween(1, 50),
            'capacity' => fake()->numberBetween(2, 8),
            'shape' => 'square',
            'pos_x' => fake()->numberBetween(0, 500),
            'pos_y' => fake()->numberBetween(0, 500),
            'width' => 100,
            'height' => 100,
            'status' => 'free',
            'is_active' => true,
        ];
    }

    /**
     * Занятый стол.
     */
    public function occupied(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'occupied',
        ]);
    }

    /**
     * Зарезервированный стол.
     */
    public function reserved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'reserved',
        ]);
    }

    /**
     * Неактивный стол.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
