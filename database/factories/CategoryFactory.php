<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Menu\Models\Category;
use App\Domain\Organization\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->words(2, true),
            'color' => fake()->hexColor(),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_visible' => true,
        ];
    }

    /**
     * Скрытая категория.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => false,
        ]);
    }
}
