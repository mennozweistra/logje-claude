<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    use HasFactory;

    protected $table = 'foods';

    protected $fillable = [
        'name',
        'description',
        'carbs_per_100g',
        'calories_per_100g',
    ];

    protected $casts = [
        'carbs_per_100g' => 'decimal:2',
        'calories_per_100g' => 'decimal:2',
    ];

    /**
     * Calculate calories for a given amount in grams
     */
    public function calculateCalories(float $grams): float
    {
        return round(($this->calories_per_100g * $grams) / 100, 2);
    }

    /**
     * Calculate carbohydrates for a given amount in grams
     */
    public function calculateCarbs(float $grams): float
    {
        return round(($this->carbs_per_100g * $grams) / 100, 2);
    }

    /**
     * Get the food measurements for this food
     */
    public function foodMeasurements()
    {
        return $this->hasMany(FoodMeasurement::class);
    }

    /**
     * Search foods by name
     */
    public static function search(string $query)
    {
        return self::where('name', 'like', '%' . $query . '%')
            ->orderBy('name')
            ->get();
    }
}