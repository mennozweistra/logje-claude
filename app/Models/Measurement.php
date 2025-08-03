<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Measurement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'measurement_type_id',
        'value',
        'is_fasting',
        'description',
        'duration',
        'date',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'date' => 'date',
        'is_fasting' => 'boolean',
        'value' => 'decimal:2',
        'duration' => 'integer',
    ];

    /**
     * Get the user that owns the measurement
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the measurement type
     */
    public function measurementType(): BelongsTo
    {
        return $this->belongsTo(MeasurementType::class);
    }

    /**
     * Scope a query to only include measurements for a specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope a query to only include measurements of a specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->whereHas('measurementType', function ($q) use ($type) {
            $q->where('slug', $type);
        });
    }

    /**
     * Get the medications associated with this measurement
     */
    public function medications(): BelongsToMany
    {
        return $this->belongsToMany(Medication::class);
    }

    /**
     * Get the food measurements associated with this measurement
     */
    public function foodMeasurements(): HasMany
    {
        return $this->hasMany(FoodMeasurement::class);
    }

    /**
     * Get the foods associated with this measurement through food_measurements
     */
    public function foods(): BelongsToMany
    {
        return $this->belongsToMany(Food::class, 'food_measurements')
            ->withPivot('grams_consumed', 'calculated_calories', 'calculated_carbs')
            ->withTimestamps();
    }
}
