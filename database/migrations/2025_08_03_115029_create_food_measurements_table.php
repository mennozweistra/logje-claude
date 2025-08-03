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
        Schema::create('food_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('measurement_id')->constrained()->onDelete('cascade');
            $table->foreignId('food_id')->constrained('foods')->onDelete('cascade');
            $table->decimal('grams_consumed', 8, 2); // Amount consumed in grams
            $table->decimal('calculated_calories', 8, 2); // Calculated calories for this portion
            $table->decimal('calculated_carbs', 8, 2); // Calculated carbs for this portion
            $table->timestamps();

            // Unique constraint to prevent duplicate food entries per measurement
            $table->unique(['measurement_id', 'food_id']);
            
            // Indexes for performance
            $table->index('measurement_id');
            $table->index('food_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_measurements');
    }
};