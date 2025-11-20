<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'icon',
        'color',
        'channels',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'channels' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get notification templates for this type
     */
    public function templates(): HasMany
    {
        return $this->hasMany(NotificationTemplate::class);
    }

    /**
     * Get user preferences for this notification type
     */
    public function userPreferences(): HasMany
    {
        return $this->hasMany(UserNotificationPreference::class);
    }

    /**
     * Get notification history for this type
     */
    public function notificationHistory(): HasMany
    {
        return $this->hasMany(NotificationHistory::class);
    }

    /**
     * Scope for active notification types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by priority
     */
    public function scopeByPriority($query, int $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope by channel
     */
    public function scopeByChannel($query, string $channel)
    {
        return $query->whereJsonContains('channels', $channel);
    }

    /**
     * Check if this notification type supports a specific channel
     */
    public function supportsChannel(string $channel): bool
    {
        return in_array($channel, $this->channels ?? []);
    }

    /**
     * Get priority display name
     */
    public function getPriorityDisplayNameAttribute(): string
    {
        return match($this->priority) {
            1 => 'Low',
            2 => 'Medium',
            3 => 'High',
            4 => 'Critical',
            default => 'Unknown'
        };
    }

    /**
     * Get priority badge class
     */
    public function getPriorityBadgeClassAttribute(): string
    {
        return match($this->priority) {
            1 => 'bg-gray-100 text-gray-800',
            2 => 'bg-blue-100 text-blue-800',
            3 => 'bg-orange-100 text-orange-800',
            4 => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get template for specific channel
     */
    public function getTemplateForChannel(string $channel): ?NotificationTemplate
    {
        return $this->templates()
            ->where('channel', $channel)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get default user preferences for this notification type
     */
    public function getDefaultUserPreferences(): array
    {
        return [
            'database_enabled' => true,
            'email_enabled' => in_array('mail', $this->channels),
            'sms_enabled' => in_array('sms', $this->channels) && $this->priority >= 3,
            'whatsapp_enabled' => in_array('whatsapp', $this->channels),
            'push_enabled' => in_array('push', $this->channels),
            'email_frequency' => $this->priority >= 3 ? 'realtime' : 'digest_daily',
        ];
    }
}

