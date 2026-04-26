<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Auth\Models\User;
use App\Domain\Organization\Models\Branch;
use App\Domain\Payment\Models\CashShift;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashShift>
 */
class CashShiftFactory extends Factory
{
    protected $model = CashShift::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'opened_by' => User::factory(),
            'status' => 'open',
            'opening_cash' => 0,
            'opened_at' => now(),
        ];
    }

    /**
     * Закрытая смена.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'closed_by' => $attributes['opened_by'] ?? User::factory(),
            'closed_at' => now(),
            'closing_cash' => fake()->numberBetween(100000, 5000000),
        ]);
    }
}
