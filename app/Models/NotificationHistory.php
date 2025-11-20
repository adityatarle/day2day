<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_type_id',
        'channel',
        'title',
        'body',
        'data',
        'status',
        'read_at',
        'metadata',
    ];

    protected $casts = [
        'data' => 'array',
        'metadata' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Get the user this notification belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the notification type
     */
    public function notificationType(): BelongsTo
    {
        return $this->belongsTo(NotificationType::class);
    }

    /**
     * Get notification actions
     */
    public function actions(): HasMany
    {
        return $this->hasMany(NotificationAction::class, 'notification_id');
    }

    /**
     * Get read status records
     */
    public function readStatus(): HasMany
    {
        return $this->hasMany(NotificationReadStatus::class, 'notification_id');
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope by channel
     */
    public function scopeByChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope by notification type
     */
    public function scopeByType($query, int $notificationTypeId)
    {
        return $query->where('notification_type_id', $notificationTypeId);
    }

    /**
     * Scope by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for recent notifications
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Check if notification is unread
     */
    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): bool
    {
        if ($this->isRead()) {
            return true;
        }

        $this->read_at = now();
        $saved = $this->save();

        if ($saved) {
            // Create read status record
            NotificationReadStatus::create([
                'user_id' => $this->user_id,
                'notification_id' => $this->id,
                'read_at' => $this->read_at,
            ]);
        }

        return $saved;
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(): bool
    {
        if ($this->isUnread()) {
            return true;
        }

        $this->read_at = null;
        $saved = $this->save();

        if ($saved) {
            // Remove read status record
            NotificationReadStatus::where('user_id', $this->user_id)
                ->where('notification_id', $this->id)
                ->delete();
        }

        return $saved;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'sent' => 'bg-green-100 text-green-800',
            'delivered' => 'bg-blue-100 text-blue-800',
            'read' => 'bg-purple-100 text-purple-800',
            'failed' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get channel badge class
     */
    public function getChannelBadgeClassAttribute(): string
    {
        return match($this->channel) {
            'database' => 'bg-blue-100 text-blue-800',
            'mail' => 'bg-green-100 text-green-800',
            'sms' => 'bg-yellow-100 text-yellow-800',
            'whatsapp' => 'bg-green-100 text-green-800',
            'push' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get formatted created time
     */
    public function getFormattedTimeAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get notification icon
     */
    public function getIconAttribute(): string
    {
        return $this->notificationType->icon ?? 'fas fa-bell';
    }

    /**
     * Get notification color
     */
    public function getColorAttribute(): string
    {
        return $this->notificationType->color ?? '#3B82F6';
    }

    /**
     * Get primary action
     */
    public function getPrimaryActionAttribute(): ?NotificationAction
    {
        return $this->actions()->where('is_primary', true)->first();
    }

    /**
     * Get all actions
     */
    public function getAllActionsAttribute(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->actions()->orderBy('is_primary', 'desc')->get();
    }

    /**
     * Create notification history record
     */
    public static function createRecord(
        int $userId,
        int $notificationTypeId,
        string $channel,
        string $title,
        string $body,
        array $data = [],
        string $status = 'sent',
        array $metadata = []
    ): self {
        return self::create([
            'user_id' => $userId,
            'notification_type_id' => $notificationTypeId,
            'channel' => $channel,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'status' => $status,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get unread count for user
     */
    public static function getUnreadCount(int $userId): int
    {
        return self::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Get recent notifications for user
     */
    public static function getRecentForUser(int $userId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('user_id', $userId)
            ->with(['notificationType', 'actions'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

