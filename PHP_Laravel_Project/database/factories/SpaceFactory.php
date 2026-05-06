<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Space>
 */
final class SpaceFactory extends Factory
{
    protected $model = Space::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement([
            'Sala Andina', 'Sala Pacífico', 'Sala Caribe',
            'Sala Norte', 'Sala Cumbre', 'Sala Horizonte',
            'Sala Cóndor', 'Sala Selva',
        ]) . ' ' . $this->faker->bothify('??##');

        return [
            'name'           => $name,
            'slug'           => Str::slug($name),
            'type'           => $this->faker->randomElement(['conferencia', 'reunión', 'webinar']),
            'capacity'       => $this->faker->numberBetween(5, 50),
            'description'    => $this->faker->sentence(12),
            'price_per_hour' => $this->faker->randomFloat(2, 20000, 200000),
            'is_active'      => true,
        ];
    }

    /** Sala inactiva */
    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    /** Sala de conferencias grande */
    public function conferencia(): static
    {
        return $this->state([
            'type'     => 'conferencia',
            'capacity' => $this->faker->numberBetween(20, 50),
        ]);
    }
}
