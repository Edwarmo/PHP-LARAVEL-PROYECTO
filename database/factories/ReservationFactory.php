<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Reservation>
 */
final class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        $start = Carbon::instance($this->faker->dateTimeBetween('+1 day', '+30 days'))
            ->setMinute(0)->setSecond(0);
        $end   = $start->copy()->addHour();

        return [
            'space_id'   => Space::factory(),
            'user_name'  => $this->faker->name(),
            'user_email' => $this->faker->safeEmail(),
            'start_time' => $start,
            'end_time'   => $end,
            'status'     => Reservation::STATUS_PENDIENTE,
            'notes'      => $this->faker->optional(0.4)->sentence(),
            'slug'       => (string) Str::uuid(),
        ];
    }

    public function confirmada(): static
    {
        return $this->state(['status' => Reservation::STATUS_CONFIRMADA]);
    }

    public function rechazada(): static
    {
        return $this->state(['status' => Reservation::STATUS_RECHAZADA]);
    }

    public function cancelada(): static
    {
        return $this->state(['status' => Reservation::STATUS_CANCELADA]);
    }

    public function finalizada(): static
    {
        return $this->state(['status' => Reservation::STATUS_FINALIZADA]);
    }

    /** Reserva que ya terminó (en el pasado) */
    public function pasada(): static
    {
        $start = Carbon::now()->subHours(3);
        return $this->state([
            'start_time' => $start,
            'end_time'   => $start->copy()->addHour(),
        ]);
    }

    /** Reserva para un rango específico */
    public function between(string $start, string $end): static
    {
        return $this->state([
            'start_time' => $start,
            'end_time'   => $end,
        ]);
    }
}
