<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Customer\Models\Customer;
use App\Domain\Organization\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->unique()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'bonus_balance' => 0,
        ];
    }

    /**
     * Клиент с бонусами.
     */
    public function withBonuses(float $amount = 1000): static
    {
        return $this->state(fn (array $attributes) => [
            'bonus_balance' => $amount,
        ]);
    }
}
