<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->company().' - '.fake()->city(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'timezone' => 'Asia/Tashkent',
            'currency_code' => 'UZS',
            'working_hours' => [
                'mon' => ['09:00', '23:00'],
                'tue' => ['09:00', '23:00'],
                'wed' => ['09:00', '23:00'],
                'thu' => ['09:00', '23:00'],
                'fri' => ['09:00', '00:00'],
                'sat' => ['10:00', '00:00'],
                'sun' => ['10:00', '22:00'],
            ],
            'is_active' => true,
        ];
    }

    /**
     * Неактивный филиал.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
