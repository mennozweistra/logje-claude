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
        DB::table('measurement_types')->insertOrIgnore([
            [
                'name' => 'Low Carb Diet',
                'slug' => 'low-carb-diet',
                'description' => 'Daily adherence to low carbohydrate diet with checkbox tracking',
                'unit' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('measurement_types')->where('slug', 'low-carb-diet')->delete();
    }
};
