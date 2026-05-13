<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Models\Availability;
use App\Domain\Models\Space;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Availability>
 */
final class AvailabilityFactory extends Factory
{
    protected $model = Availability::class;

    public function definition(): array
    {
        return [
            'space_id'    => Space::factory(),
            'day_of_week' => $this->faker->numberBetween(1, 5),  // Lun–Vie por defecto
            'start_time'  => '08:00:00',
            'end_time'    => '18:00:00',
        ];
    }

    /** Disponibilidad de lunes a viernes estándar (08:00–18:00) */
    public function weekdays(): static
    {
        return $this->state([
            'day_of_week' => $this->faker->numberBetween(1, 5),
            'start_time'  => '08:00:00',
            'end_time'    => '18:00:00',
        ]);
    }

    /** Disponibilidad un día específico */
    public function forDay(int $dayOfWeek): static
    {
        return $this->state(['day_of_week' => $dayOfWeek]);
    }
}
