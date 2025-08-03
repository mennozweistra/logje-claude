<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert default measurement types that are required for the application to function
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

        // Only insert if measurement types table is empty (avoid duplicates)
        if (DB::table('measurement_types')->count() === 0) {
            DB::table('measurement_types')->insert($measurementTypes);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the default measurement types
        DB::table('measurement_types')->whereIn('slug', ['glucose', 'weight', 'exercise', 'notes'])->delete();
    }
};
