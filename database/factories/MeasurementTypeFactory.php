<?php

namespace Database\Factories;

use App\Models\MeasurementType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MeasurementType>
 */
class MeasurementTypeFactory extends Factory
{
    protected $model = MeasurementType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            ['name' => 'Glucose', 'slug' => 'glucose', 'description' => 'Blood glucose measurement'],
            ['name' => 'Weight', 'slug' => 'weight', 'description' => 'Body weight measurement'],
            ['name' => 'Exercise', 'slug' => 'exercise', 'description' => 'Physical activity tracking'],
            ['name' => 'Notes', 'slug' => 'notes', 'description' => 'Daily notes and observations'],
        ];

        $type = $this->faker->randomElement($types);

        return [
            'name' => $type['name'],
            'slug' => $type['slug'],
            'description' => $type['description'],
        ];
    }
}
