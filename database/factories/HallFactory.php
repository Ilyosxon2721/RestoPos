<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Floor\Models\Hall;
use App\Domain\Organization\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hall>
 */
class HallFactory extends Factory
{
    protected $model = Hall::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name' => fake()->randomElement(['Основной зал', 'VIP зал', 'Терраса', 'Банкетный зал', 'Бар']),
            'sort_order' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }

    /**
     * Неактивный зал.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
