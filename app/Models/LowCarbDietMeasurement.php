<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LowCarbDietMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'measurement_id',
        'carb_level',
    ];

    protected $casts = [
        'carb_level' => 'string',
    ];

    /**
     * Valid carb levels
     */
    public const CARB_LEVELS = [
        'low' => 'low',
        'medium' => 'medium', 
        'high' => 'high',
    ];

    /**
     * Get the measurement that owns the low carb diet measurement.
     */
    public function measurement(): BelongsTo
    {
        return $this->belongsTo(Measurement::class);
    }

    /**
     * Get the emoji representation for the carb level
     */
    public function getCarbLevelEmojiAttribute(): string
    {
        return match($this->carb_level) {
            'low' => '😊',    // Happy smiley - good carb control
            'medium' => '😐',  // Plain smiley - moderate carb intake
            'high' => '😔',   // Sad smiley - high carb intake
            default => '😐',
        };
    }

    /**
     * Get the display label for the carb level
     */
    public function getCarbLevelLabelAttribute(): string
    {
        return match($this->carb_level) {
            'low' => 'Low Carb',
            'medium' => 'Medium Carb', 
            'high' => 'High Carb',
            default => 'Medium Carb',
        };
    }
}
