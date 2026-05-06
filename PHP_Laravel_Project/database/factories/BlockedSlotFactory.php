<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BlockedSlot;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlockedSlot>
 */
final class BlockedSlotFactory extends Factory
{
    protected $model = BlockedSlot::class;

    public function definition(): array
    {
        $start = Carbon::instance($this->faker->dateTimeBetween('now', '+30 days'));
        $end   = $start->copy()->addHours($this->faker->numberBetween(1, 4));

        return [
            'space_id'   => Space::factory(),
            'start_time' => $start,
            'end_time'   => $end,
            'reason'     => $this->faker->optional(0.7)->sentence(5),
        ];
    }

    /** Bloqueo en un rango específico */
    public function between(string $start, string $end): static
    {
        return $this->state([
            'start_time' => $start,
            'end_time'   => $end,
        ]);
    }
}
