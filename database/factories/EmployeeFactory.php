<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Auth\Models\User;
use App\Domain\Organization\Models\Branch;
use App\Domain\Staff\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'branch_id' => Branch::factory(),
            'position' => fake()->randomElement(['waiter', 'cook', 'bartender', 'manager', 'cashier']),
            'hire_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'salary_type' => 'fixed',
            'monthly_salary' => fake()->numberBetween(3000000, 10000000),
        ];
    }

    /**
     * Сотрудник с почасовой оплатой.
     */
    public function hourly(): static
    {
        return $this->state(fn (array $attributes) => [
            'salary_type' => 'hourly',
            'hourly_rate' => fake()->numberBetween(20000, 50000),
            'monthly_salary' => null,
        ]);
    }
}
