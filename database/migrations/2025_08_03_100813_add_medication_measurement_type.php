<?php

use App\Models\MeasurementType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        MeasurementType::firstOrCreate(
            ['slug' => 'medication'],
            [
                'name' => 'Medication',
                'description' => 'Medication intake tracking with multiple selection',
                'unit' => '?',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        MeasurementType::where('slug', 'medication')->delete();
    }
};
