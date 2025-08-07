<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Todo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'priority',
        'status',
        'is_archived',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'priority' => 'string',
        'status' => 'string',
    ];

    /**
     * Priority levels
     */
    const PRIORITY_HIGH = 'high';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_LOW = 'low';

    /**
     * Status types
     */
    const STATUS_TODO = 'todo';
    const STATUS_ONGOING = 'ongoing';
    const STATUS_PAUSED = 'paused';
    const STATUS_DONE = 'done';

    /**
     * Get the user that owns the todo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the notes for the todo
     */
    public function notes(): HasMany
    {
        return $this->hasMany(TodoNote::class);
    }

    /**
     * Scope a query to only include active (non-archived) todos
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope a query to only include archived todos
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    /**
     * Scope a query to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Get priority levels array
     */
    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_LOW => 'Low',
        ];
    }

    /**
     * Get status types array
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_TODO => 'Todo',
            self::STATUS_ONGOING => 'Ongoing',
            self::STATUS_PAUSED => 'Paused',
            self::STATUS_DONE => 'Done',
        ];
    }
}
