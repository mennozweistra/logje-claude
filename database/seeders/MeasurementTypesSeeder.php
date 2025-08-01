<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MeasurementTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $measurementTypes = [
            [
                'name' => 'Glucose',
                'slug' => 'glucose',
                'description' => 'Blood glucose measurement in mg/dL with optional fasting indicator',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Weight',
                'slug' => 'weight',
                'description' => 'Body weight measurement in kilograms',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Exercise',
                'slug' => 'exercise',
                'description' => 'Physical activity with description and duration in minutes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Notes',
                'slug' => 'notes',
                'description' => 'Daily notes and observations',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('measurement_types')->insert($measurementTypes);
    }
}
