<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Medication extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];

    /**
     * Get the user that owns the medication
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function measurements(): BelongsToMany
    {
        return $this->belongsToMany(Measurement::class);
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
        static::creating(function ($medication) {
            if (auth()->check() && !$medication->user_id) {
                $medication->user_id = auth()->id();
            }
        });
    }
}
