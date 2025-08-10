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
        'adherence',
    ];

    protected $casts = [
        'adherence' => 'boolean',
    ];

    /**
     * Get the measurement that owns the low carb diet measurement.
     */
    public function measurement(): BelongsTo
    {
        return $this->belongsTo(Measurement::class);
    }
}
