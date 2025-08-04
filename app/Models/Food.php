<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Food extends Model
{
    use HasFactory;

    protected $table = 'foods';

    protected $fillable = [
        'name',
        'description',
        'carbs_per_100g',
        'calories_per_100g',
        'user_id',
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
     * Get the user that owns the food
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the food measurements for this food
     */
    public function foodMeasurements()
    {
        return $this->hasMany(FoodMeasurement::class);
    }

    /**
     * Boot the model and add global scope for user filtering
     */
    protected static function boot()
    {
        parent::boot();

        // Add global scope to filter by authenticated user
        static::addGlobalScope('user', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });

        // Automatically set user_id when creating
        static::creating(function ($food) {
            if (auth()->check() && !$food->user_id) {
                $food->user_id = auth()->id();
            }
        });
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