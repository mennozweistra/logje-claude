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
        Schema::table('low_carb_diet_measurements', function (Blueprint $table) {
            // Add new carb_level enum column
            $table->enum('carb_level', ['low', 'medium', 'high'])->default('medium')->after('measurement_id');
            
            // Migrate existing data: adherence=true becomes 'low', adherence=false becomes 'medium'
            // This preserves the intent where adherence=true meant following low carb diet
        });
        
        // Update existing records based on adherence value
        DB::statement("UPDATE low_carb_diet_measurements SET carb_level = CASE WHEN adherence = 1 THEN 'low' ELSE 'medium' END");
        
        Schema::table('low_carb_diet_measurements', function (Blueprint $table) {
            // Remove the old adherence column
            $table->dropColumn('adherence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('low_carb_diet_measurements', function (Blueprint $table) {
            // Add back adherence column
            $table->boolean('adherence')->default(false)->after('measurement_id');
        });
        
        // Migrate data back: 'low' becomes true, everything else becomes false
        DB::statement("UPDATE low_carb_diet_measurements SET adherence = CASE WHEN carb_level = 'low' THEN 1 ELSE 0 END");
        
        Schema::table('low_carb_diet_measurements', function (Blueprint $table) {
            // Remove the carb_level column
            $table->dropColumn('carb_level');
        });
    }
};
