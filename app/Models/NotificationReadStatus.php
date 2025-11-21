<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationReadStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Get the user this read status belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the notification this read status belongs to
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(NotificationHistory::class, 'notification_id');
    }

    /**
     * Scope for recent reads
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('read_at', '>=', now()->subDays($days));
    }

    /**
     * Get formatted read time
     */
    public function getFormattedReadTimeAttribute(): string
    {
        return $this->read_at->diffForHumans();
    }
}










