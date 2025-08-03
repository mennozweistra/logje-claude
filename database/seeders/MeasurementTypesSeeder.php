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
                'description' => 'Blood glucose measurement in mmol/L with optional fasting indicator',
                'unit' => 'mmol/L',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Weight',
                'slug' => 'weight',
                'description' => 'Body weight measurement in kilograms',
                'unit' => 'kg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Exercise',
                'slug' => 'exercise',
                'description' => 'Physical activity with description and duration in minutes',
                'unit' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Notes',
                'slug' => 'notes',
                'description' => 'Daily notes and observations',
                'unit' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('measurement_types')->insertOrIgnore($measurementTypes);
    }
}
