<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Organization\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'legal_name' => fake()->company().' LLC',
            'inn' => fake()->numerify('#########'),
            'subscription_plan' => 'business',
            'subscription_expires_at' => now()->addYear(),
            'settings' => [
                'currency' => 'UZS',
                'timezone' => 'Asia/Tashkent',
                'tax_rate' => 12,
            ],
            'is_active' => true,
        ];
    }

    /**
     * Организация с истёкшей подпиской.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_expires_at' => now()->subMonth(),
        ]);
    }

    /**
     * Организация на пробном плане.
     */
    public function trial(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_plan' => 'trial',
            'subscription_expires_at' => now()->addDays(14),
        ]);
    }

    /**
     * Неактивная организация.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
