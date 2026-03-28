<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Auth\Models\User;
use App\Domain\Organization\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->phoneNumber(),
            'password' => static::$password ??= Hash::make('password'),
            'pin_code' => '1234',
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'locale' => 'ru',
            'is_active' => true,
        ];
    }

    /**
     * Неактивный пользователь.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Пользователь с подтверждённым email.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => now(),
        ]);
    }
}
