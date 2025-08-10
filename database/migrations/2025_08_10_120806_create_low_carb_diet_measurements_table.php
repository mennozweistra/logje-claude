<?php

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
        Schema::create('low_carb_diet_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('measurement_id')->constrained()->onDelete('cascade');
            $table->boolean('adherence')->default(false); // Checkbox: kept to low carb diet
            $table->timestamps();

            // Each measurement can only have one low carb diet record
            $table->unique('measurement_id');
            
            // Index for performance
            $table->index('measurement_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('low_carb_diet_measurements');
    }
};
