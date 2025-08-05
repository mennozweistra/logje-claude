<?php

namespace Database\Factories;

use App\Models\Food;
use App\Models\FoodMeasurement;
use App\Models\Measurement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FoodMeasurement>
 */
class FoodMeasurementFactory extends Factory
{
    protected $model = FoodMeasurement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gramsConsumed = $this->faker->numberBetween(50, 300);
        
        return [
            'measurement_id' => Measurement::factory(),
            'food_id' => Food::factory(),
            'grams_consumed' => $gramsConsumed,
            'calculated_calories' => $this->faker->randomFloat(2, 20, 500),
            'calculated_carbs' => $this->faker->randomFloat(2, 5, 80),
        ];
    }
}