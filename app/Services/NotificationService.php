<?php

namespace App\Services;

use App\Models\User;
use App\Models\NotificationType;
use App\Models\NotificationTemplate;
use App\Models\UserNotificationPreference;
use App\Models\NotificationHistory;
use App\Models\NotificationAction;
use App\Models\NotificationQueue;
use App\Jobs\SendNotificationJob;
use App\Jobs\SendEmailNotificationJob;
use App\Jobs\SendSmsNotificationJob;
use App\Jobs\SendWhatsAppNotificationJob;
use App\Jobs\SendPushNotificationJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Send notification to user
     */
    public function sendNotification(
        User $user,
        string $notificationTypeName,
        array $data = [],
        array $actions = []
    ): bool {
        try {
            $notificationType = NotificationType::where('name', $notificationTypeName)
                ->where('is_active', true)
                ->first();

            if (!$notificationType) {
                Log::warning("Notification type not found: {$notificationTypeName}");
                return false;
            }

            // Get user preferences for this notification type
            $preferences = UserNotificationPreference::where('user_id', $user->id)
                ->where('notification_type_id', $notificationType->id)
                ->first();

            if (!$preferences) {
                // Create default preferences
                $preferences = UserNotificationPreference::create(
                    array_merge(
                        UserNotificationPreference::getDefaults($user->id, $notificationType->id),
                        ['notification_type_id' => $notificationType->id]
                    )
                );
            }

            $enabledChannels = $preferences->getEnabledChannels();
            
            if (empty($enabledChannels)) {
                Log::info("No channels enabled for user {$user->id} and notification type {$notificationTypeName}");
                return true; // Not an error, just no channels enabled
            }

            $success = true;

            foreach ($enabledChannels as $channel) {
                if (!$notificationType->supportsChannel($channel)) {
                    continue;
                }

                $channelSuccess = $this->sendToChannel(
                    $user,
                    $notificationType,
                    $channel,
                    $data,
                    $actions,
                    $preferences
                );

                if (!$channelSuccess) {
                    $success = false;
                }
            }

            return $success;

        } catch (\Exception $e) {
            Log::error("Failed to send notification: " . $e->getMessage(), [
                'user_id' => $user->id,
                'notification_type' => $notificationTypeName,
                'data' => $data,
                'error' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send notification to multiple users
     */
    public function sendBulkNotification(
        array $userIds,
        string $notificationTypeName,
        array $data = [],
        array $actions = []
    ): array {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (!$user) {
                $results['failed']++;
                $results['errors'][] = "User not found: {$userId}";
                continue;
            }

            $success = $this->sendNotification($user, $notificationTypeName, $data, $actions);
            
            if ($success) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "Failed to send to user: {$userId}";
            }
        }

        return $results;
    }

    /**
     * Send notification to users by role
     */
    public function sendNotificationToRole(
        string $role,
        string $notificationTypeName,
        array $data = [],
        array $actions = []
    ): array {
        $users = User::whereHas('role', function ($query) use ($role) {
            $query->where('name', $role);
        })->get();

        $userIds = $users->pluck('id')->toArray();
        
        return $this->sendBulkNotification($userIds, $notificationTypeName, $data, $actions);
    }

    /**
     * Send notification to users by branch
     */
    public function sendNotificationToBranch(
        int $branchId,
        string $notificationTypeName,
        array $data = [],
        array $actions = []
    ): array {
        $users = User::where('branch_id', $branchId)->get();
        $userIds = $users->pluck('id')->toArray();
        
        return $this->sendBulkNotification($userIds, $notificationTypeName, $data, $actions);
    }

    /**
     * Send notification to specific channel
     */
    private function sendToChannel(
        User $user,
        NotificationType $notificationType,
        string $channel,
        array $data,
        array $actions,
        UserNotificationPreference $preferences
    ): bool {
        try {
            $template = $notificationType->getTemplateForChannel($channel);
            
            if (!$template) {
                Log::warning("No template found for channel: {$channel}");
                return false;
            }

            // Validate template variables
            $errors = $template->validateVariables($data);
            if (!empty($errors)) {
                Log::error("Template validation failed: " . implode(', ', $errors));
                return false;
            }

            // Render template
            $rendered = $template->render($data);
            
            // Add user data to notification data
            $notificationData = array_merge($data, [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '',
                ]
            ]);

            // Handle different channels
            switch ($channel) {
                case 'database':
                    return $this->sendDatabaseNotification($user, $notificationType, $rendered, $notificationData, $actions);
                
                case 'mail':
                    return $this->sendEmailNotification($user, $notificationType, $rendered, $notificationData, $preferences);
                
                case 'sms':
                    return $this->sendSmsNotification($user, $notificationType, $rendered, $notificationData);
                
                case 'whatsapp':
                    return $this->sendWhatsAppNotification($user, $notificationType, $rendered, $notificationData);
                
                case 'push':
                    return $this->sendPushNotification($user, $notificationType, $rendered, $notificationData);
                
                default:
                    Log::warning("Unsupported channel: {$channel}");
                    return false;
            }

        } catch (\Exception $e) {
            Log::error("Failed to send to channel {$channel}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send database notification
     */
    private function sendDatabaseNotification(
        User $user,
        NotificationType $notificationType,
        array $rendered,
        array $data,
        array $actions
    ): bool {
        try {
            DB::beginTransaction();

            // Create notification history record
            $notification = NotificationHistory::createRecord(
                $user->id,
                $notificationType->id,
                'database',
                $rendered['subject'] ?? $notificationType->display_name,
                $rendered['body'],
                $data,
                'sent'
            );

            // Create actions
            foreach ($actions as $action) {
                NotificationAction::createForNotification(
                    $notification->id,
                    $action['type'] ?? 'view',
                    $action['label'] ?? 'View',
                    $action['url'] ?? null,
                    $action['data'] ?? [],
                    $action['primary'] ?? false
                );
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to send database notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification(
        User $user,
        NotificationType $notificationType,
        array $rendered,
        array $data,
        UserNotificationPreference $preferences
    ): bool {
        try {
            if ($preferences->isRealtimeEmail()) {
                // Send immediately
                SendEmailNotificationJob::dispatch($user, $notificationType, $rendered, $data);
            } else {
                // Add to digest queue
                $this->addToDigestQueue($user, $notificationType, $rendered, $data, $preferences);
            }

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send email notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS notification
     */
    private function sendSmsNotification(
        User $user,
        NotificationType $notificationType,
        array $rendered,
        array $data
    ): bool {
        try {
            if (empty($user->phone)) {
                Log::warning("User {$user->id} has no phone number for SMS");
                return false;
            }

            SendSmsNotificationJob::dispatch($user, $notificationType, $rendered, $data);
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send SMS notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send WhatsApp notification
     */
    private function sendWhatsAppNotification(
        User $user,
        NotificationType $notificationType,
        array $rendered,
        array $data
    ): bool {
        try {
            if (empty($user->phone)) {
                Log::warning("User {$user->id} has no phone number for WhatsApp");
                return false;
            }

            SendWhatsAppNotificationJob::dispatch($user, $notificationType, $rendered, $data);
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send push notification
     */
    private function sendPushNotification(
        User $user,
        NotificationType $notificationType,
        array $rendered,
        array $data
    ): bool {
        try {
            SendPushNotificationJob::dispatch($user, $notificationType, $rendered, $data);
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send push notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add notification to digest queue
     */
    private function addToDigestQueue(
        User $user,
        NotificationType $notificationType,
        array $rendered,
        array $data,
        UserNotificationPreference $preferences
    ): void {
        $frequency = $preferences->getDigestFrequency();
        
        if (!$frequency) {
            return;
        }

        // Create digest entry
        $digestDate = $frequency === 'daily' ? now()->toDateString() : now()->startOfWeek()->toDateString();
        
        $digest = \App\Models\NotificationDigest::firstOrCreate([
            'user_id' => $user->id,
            'frequency' => $frequency,
            'digest_date' => $digestDate,
        ]);

        // Add notification to digest
        $notificationIds = $digest->notification_ids ?? [];
        $notificationIds[] = [
            'type' => $notificationType->name,
            'title' => $rendered['subject'] ?? $notificationType->display_name,
            'body' => $rendered['body'],
            'data' => $data,
            'created_at' => now()->toISOString(),
        ];

        $digest->update(['notification_ids' => $notificationIds]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $userId, int $notificationId): bool
    {
        $notification = NotificationHistory::where('user_id', $userId)
            ->where('id', $notificationId)
            ->first();

        if (!$notification) {
            return false;
        }

        return $notification->markAsRead();
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(int $userId, int $notificationId): bool
    {
        $notification = NotificationHistory::where('user_id', $userId)
            ->where('id', $notificationId)
            ->first();

        if (!$notification) {
            return false;
        }

        return $notification->markAsUnread();
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead(int $userId): bool
    {
        try {
            NotificationHistory::where('user_id', $userId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to mark all notifications as read: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount(int $userId): int
    {
        return NotificationHistory::getUnreadCount($userId);
    }

    /**
     * Get recent notifications for user
     */
    public function getRecentNotifications(int $userId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return NotificationHistory::getRecentForUser($userId, $limit);
    }

    /**
     * Update user notification preferences
     */
    public function updateUserPreferences(int $userId, array $preferences): bool
    {
        try {
            foreach ($preferences as $notificationTypeId => $settings) {
                UserNotificationPreference::createOrUpdate(
                    $userId,
                    $notificationTypeId,
                    $settings
                );
            }

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to update user preferences: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user notification preferences
     */
    public function getUserPreferences(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return UserNotificationPreference::where('user_id', $userId)
            ->with('notificationType')
            ->get();
    }
}