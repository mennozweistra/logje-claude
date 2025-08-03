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
        // Add food measurement type
        DB::table('measurement_types')->insertOrIgnore([
            'name' => 'Food',
            'slug' => 'food',
            'description' => 'Food consumption tracking with nutritional information',
            'unit' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('measurement_types')->where('slug', 'food')->delete();
    }
};