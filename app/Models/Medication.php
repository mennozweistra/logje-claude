<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Medication extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
    ];

    public function measurements(): BelongsToMany
    {
        return $this->belongsToMany(Measurement::class);
    }
}
