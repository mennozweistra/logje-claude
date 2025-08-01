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
        Schema::create('measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('measurement_type_id')->constrained()->onDelete('cascade');
            $table->decimal('value', 8, 2)->nullable(); // For glucose (mg/dL) and weight (kg)
            $table->boolean('is_fasting')->nullable(); // For glucose measurements
            $table->string('description')->nullable(); // For exercise descriptions
            $table->integer('duration')->nullable(); // For exercise duration in minutes
            $table->date('date'); // Date of measurement
            $table->text('notes')->nullable(); // Optional notes for all measurements
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'measurement_type_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('measurements');
    }
};
