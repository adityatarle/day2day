<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_id',
        'action_type',
        'action_label',
        'action_url',
        'action_data',
        'is_primary',
    ];

    protected $casts = [
        'action_data' => 'array',
        'is_primary' => 'boolean',
    ];

    /**
     * Get the notification this action belongs to
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(NotificationHistory::class, 'notification_id');
    }

    /**
     * Scope for primary actions
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope by action type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('action_type', $type);
    }

    /**
     * Get action button class
     */
    public function getButtonClassAttribute(): string
    {
        return match($this->action_type) {
            'approve' => 'btn-success',
            'view' => 'btn-primary',
            'dismiss' => 'btn-secondary',
            'reject' => 'btn-danger',
            'edit' => 'btn-warning',
            default => 'btn-primary'
        };
    }

    /**
     * Get action icon
     */
    public function getIconAttribute(): string
    {
        return match($this->action_type) {
            'approve' => 'fas fa-check',
            'view' => 'fas fa-eye',
            'dismiss' => 'fas fa-times',
            'reject' => 'fas fa-ban',
            'edit' => 'fas fa-edit',
            default => 'fas fa-arrow-right'
        };
    }

    /**
     * Check if action has URL
     */
    public function hasUrl(): bool
    {
        return !empty($this->action_url);
    }

    /**
     * Get action URL with parameters
     */
    public function getUrlWithParams(): string
    {
        if (!$this->hasUrl()) {
            return '#';
        }

        $url = $this->action_url;
        $data = $this->action_data ?? [];

        // Replace URL parameters with data values
        foreach ($data as $key => $value) {
            $url = str_replace("{{$key}}", $value, $url);
        }

        return $url;
    }

    /**
     * Create action for notification
     */
    public static function createForNotification(
        int $notificationId,
        string $actionType,
        string $actionLabel,
        ?string $actionUrl = null,
        array $actionData = [],
        bool $isPrimary = false
    ): self {
        return self::create([
            'notification_id' => $notificationId,
            'action_type' => $actionType,
            'action_label' => $actionLabel,
            'action_url' => $actionUrl,
            'action_data' => $actionData,
            'is_primary' => $isPrimary,
        ]);
    }
}












