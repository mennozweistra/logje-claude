<?php

namespace Database\Factories;

use App\Models\Measurement;
use App\Models\MeasurementType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Measurement>
 */
class MeasurementFactory extends Factory
{
    protected $model = Measurement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 1, // Default to user id 1 for testing
            'measurement_type_id' => 1, // Default to measurement type id 1 for testing
            'date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    /**
     * Glucose measurement state
     */
    public function glucose(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $this->faker->numberBetween(80, 200),
            'is_fasting' => $this->faker->boolean(),
            'description' => null,
            'duration' => null,
        ]);
    }

    /**
     * Weight measurement state
     */
    public function weight(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $this->faker->randomFloat(1, 50, 120),
            'is_fasting' => null,
            'description' => null,
            'duration' => null,
        ]);
    }

    /**
     * Exercise measurement state
     */
    public function exercise(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => null,
            'is_fasting' => null,
            'description' => $this->faker->randomElement(['Running', 'Cycling', 'Swimming', 'Yoga', 'Weight training']),
            'duration' => $this->faker->numberBetween(15, 120),
        ]);
    }

    /**
     * Notes measurement state
     */
    public function notes(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => null,
            'is_fasting' => null,
            'description' => null,
            'duration' => null,
            'notes' => $this->faker->paragraph(),
        ]);
    }
}
