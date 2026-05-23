<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WildfireIncident>
 */
class WildfireIncidentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'burned_area' => $this->faker->randomFloat(2, 0, 10000),
            'fire_date' => $this->faker->date(),
            'severity' => $this->faker->randomElement(['Low', 'Medium', 'High', 'Extreme']),
            'country' => $this->faker->country(),
            'month' => $this->faker->numberBetween(1, 12),
            'year' => $this->faker->numberBetween(2000, 2024),
            'duration' => $this->faker->numberBetween(1, 30),
            'name' => $this->faker->words(3, true),
        ];
    }
}
