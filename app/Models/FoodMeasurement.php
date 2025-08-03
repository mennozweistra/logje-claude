<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'measurement_id',
        'food_id',
        'grams_consumed',
        'calculated_calories',
        'calculated_carbs',
    ];

    protected $casts = [
        'grams_consumed' => 'decimal:2',
        'calculated_calories' => 'decimal:2',
        'calculated_carbs' => 'decimal:2',
    ];

    /**
     * Get the measurement that owns the food measurement.
     */
    public function measurement(): BelongsTo
    {
        return $this->belongsTo(Measurement::class);
    }

    /**
     * Get the food that this measurement refers to.
     */
    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }

    /**
     * Calculate and set nutritional values based on food and grams
     */
    public function calculateNutrition(): void
    {
        if ($this->food && $this->grams_consumed) {
            $this->calculated_calories = $this->food->calculateCalories($this->grams_consumed);
            $this->calculated_carbs = $this->food->calculateCarbs($this->grams_consumed);
        }
    }

    /**
     * Boot the model and automatically calculate nutrition on creation/update
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($foodMeasurement) {
            $foodMeasurement->calculateNutrition();
        });

        static::updating(function ($foodMeasurement) {
            $foodMeasurement->calculateNutrition();
        });
    }
}