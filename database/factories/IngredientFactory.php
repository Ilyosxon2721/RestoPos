<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Organization\Models\Organization;
use App\Domain\Warehouse\Models\Ingredient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ingredient>
 */
class IngredientFactory extends Factory
{
    protected $model = Ingredient::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->word(),
            'unit_id' => 1,
            'current_cost' => fake()->randomFloat(4, 100, 50000),
            'min_stock' => fake()->randomFloat(3, 0.5, 10),
        ];
    }
}
