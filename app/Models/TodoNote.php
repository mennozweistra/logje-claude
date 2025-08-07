<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TodoNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'todo_id',
        'content',
    ];

    protected $casts = [
        'content' => 'string',
    ];

    /**
     * Get the todo that owns the note
     */
    public function todo(): BelongsTo
    {
        return $this->belongsTo(Todo::class);
    }

    /**
     * Scope a query to order notes by most recent first
     */
    public function scopeRecentFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
