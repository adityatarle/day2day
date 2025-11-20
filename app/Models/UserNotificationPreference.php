<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_type_id',
        'database_enabled',
        'email_enabled',
        'sms_enabled',
        'whatsapp_enabled',
        'push_enabled',
        'email_frequency',
        'digest_settings',
    ];

    protected $casts = [
        'database_enabled' => 'boolean',
        'email_enabled' => 'boolean',
        'sms_enabled' => 'boolean',
        'whatsapp_enabled' => 'boolean',
        'push_enabled' => 'boolean',
        'digest_settings' => 'array',
    ];

    /**
     * Get the user this preference belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the notification type this preference is for
     */
    public function notificationType(): BelongsTo
    {
        return $this->belongsTo(NotificationType::class);
    }

    /**
     * Scope for enabled channels
     */
    public function scopeEnabledChannels($query, string $channel)
    {
        return $query->where("{$channel}_enabled", true);
    }

    /**
     * Check if a specific channel is enabled
     */
    public function isChannelEnabled(string $channel): bool
    {
        return match($channel) {
            'database' => $this->database_enabled,
            'mail' => $this->email_enabled,
            'sms' => $this->sms_enabled,
            'whatsapp' => $this->whatsapp_enabled,
            'push' => $this->push_enabled,
            default => false
        };
    }

    /**
     * Get enabled channels
     */
    public function getEnabledChannels(): array
    {
        $channels = [];
        
        if ($this->database_enabled) $channels[] = 'database';
        if ($this->email_enabled) $channels[] = 'mail';
        if ($this->sms_enabled) $channels[] = 'sms';
        if ($this->whatsapp_enabled) $channels[] = 'whatsapp';
        if ($this->push_enabled) $channels[] = 'push';
        
        return $channels;
    }

    /**
     * Check if email should be sent in real-time
     */
    public function isRealtimeEmail(): bool
    {
        return $this->email_frequency === 'realtime';
    }

    /**
     * Check if email should be included in digest
     */
    public function isDigestEmail(): bool
    {
        return in_array($this->email_frequency, ['digest_daily', 'digest_weekly']);
    }

    /**
     * Get digest frequency
     */
    public function getDigestFrequency(): ?string
    {
        return match($this->email_frequency) {
            'digest_daily' => 'daily',
            'digest_weekly' => 'weekly',
            default => null
        };
    }

    /**
     * Get digest time preference
     */
    public function getDigestTime(): string
    {
        $settings = $this->digest_settings ?? [];
        return $settings['time'] ?? '09:00';
    }

    /**
     * Get digest timezone
     */
    public function getDigestTimezone(): string
    {
        $settings = $this->digest_settings ?? [];
        return $settings['timezone'] ?? 'Asia/Kolkata';
    }

    /**
     * Update channel preference
     */
    public function updateChannelPreference(string $channel, bool $enabled): bool
    {
        $field = match($channel) {
            'database' => 'database_enabled',
            'mail' => 'email_enabled',
            'sms' => 'sms_enabled',
            'whatsapp' => 'whatsapp_enabled',
            'push' => 'push_enabled',
            default => null
        };

        if ($field) {
            return $this->update([$field => $enabled]);
        }

        return false;
    }

    /**
     * Update email frequency
     */
    public function updateEmailFrequency(string $frequency): bool
    {
        $validFrequencies = ['realtime', 'digest_daily', 'digest_weekly', 'disabled'];
        
        if (in_array($frequency, $validFrequencies)) {
            return $this->update(['email_frequency' => $frequency]);
        }

        return false;
    }

    /**
     * Update digest settings
     */
    public function updateDigestSettings(array $settings): bool
    {
        return $this->update(['digest_settings' => $settings]);
    }

    /**
     * Get preference summary
     */
    public function getSummary(): array
    {
        return [
            'channels' => $this->getEnabledChannels(),
            'email_frequency' => $this->email_frequency,
            'digest_time' => $this->getDigestTime(),
            'digest_timezone' => $this->getDigestTimezone(),
        ];
    }

    /**
     * Create or update user preference for notification type
     */
    public static function createOrUpdate(
        int $userId,
        int $notificationTypeId,
        array $preferences
    ): self {
        return self::updateOrCreate(
            [
                'user_id' => $userId,
                'notification_type_id' => $notificationTypeId,
            ],
            $preferences
        );
    }

    /**
     * Get default preferences for user and notification type
     */
    public static function getDefaults(int $userId, int $notificationTypeId): array
    {
        $notificationType = NotificationType::find($notificationTypeId);
        
        if (!$notificationType) {
            return [];
        }

        $defaults = $notificationType->getDefaultUserPreferences();
        $defaults['user_id'] = $userId;
        $defaults['notification_type_id'] = $notificationTypeId;

        return $defaults;
    }
}

