<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Floor\Models\Table;
use App\Domain\Organization\Models\Branch;
use App\Domain\Reservation\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'table_id' => Table::factory(),
            'guest_name' => fake()->name(),
            'guest_phone' => fake()->phoneNumber(),
            'guests_count' => fake()->numberBetween(1, 8),
            'reservation_date' => fake()->dateTimeBetween('now', '+2 weeks'),
            'reservation_time' => fake()->time('H:i'),
            'duration_minutes' => fake()->randomElement([60, 90, 120, 180]),
            'status' => 'pending',
        ];
    }

    /**
     * Подтверждённая бронь.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Отменённая бронь.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'Отменено клиентом',
        ]);
    }
}
