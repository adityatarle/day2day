<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\NotificationType;
use App\Models\NotificationHistory;
use App\Models\PushNotificationToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $notificationType;
    protected $rendered;
    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, NotificationType $notificationType, array $rendered, array $data)
    {
        $this->user = $user;
        $this->notificationType = $notificationType;
        $this->rendered = $rendered;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Get user's push notification tokens
            $tokens = PushNotificationToken::where('user_id', $this->user->id)
                ->where('is_active', true)
                ->get();

            if ($tokens->isEmpty()) {
                Log::info("No active push tokens found for user {$this->user->id}");
                return;
            }

            // Create notification history record
            $notification = NotificationHistory::createRecord(
                $this->user->id,
                $this->notificationType->id,
                'push',
                $this->rendered['subject'] ?? $this->notificationType->display_name,
                $this->rendered['body'],
                $this->data,
                'sent'
            );

            // Send push notifications to all tokens
            $successCount = 0;
            $failedCount = 0;

            foreach ($tokens as $token) {
                try {
                    $result = $this->sendPushToToken($token, $this->rendered);
                    
                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $failedCount++;
                        Log::warning("Failed to send push to token {$token->id}: {$result['error']}");
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    Log::error("Exception sending push to token {$token->id}: " . $e->getMessage());
                }
            }

            // Update notification status
            $status = $successCount > 0 ? 'delivered' : 'failed';
            $notification->update([
                'status' => $status,
                'metadata' => [
                    'success_count' => $successCount,
                    'failed_count' => $failedCount,
                    'total_tokens' => $tokens->count(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to send push notification: " . $e->getMessage(), [
                'user_id' => $this->user->id,
                'notification_type' => $this->notificationType->name,
                'error' => $e->getTraceAsString()
            ]);

            // Update notification status to failed
            if (isset($notification)) {
                $notification->update([
                    'status' => 'failed',
                    'metadata' => ['error' => $e->getMessage()]
                ]);
            }

            throw $e;
        }
    }

    /**
     * Send push notification to specific token
     */
    private function sendPushToToken(PushNotificationToken $token, array $rendered): array
    {
        switch ($token->platform) {
            case 'ios':
                return $this->sendToIOS($token, $rendered);
            case 'android':
                return $this->sendToAndroid($token, $rendered);
            case 'web':
                return $this->sendToWeb($token, $rendered);
            default:
                return ['success' => false, 'error' => "Unsupported platform: {$token->platform}"];
        }
    }

    /**
     * Send push notification to iOS device
     */
    private function sendToIOS(PushNotificationToken $token, array $rendered): array
    {
        try {
            $serverKey = config('push.ios.server_key');
            $bundleId = config('push.ios.bundle_id');

            $payload = [
                'to' => $token->token,
                'notification' => [
                    'title' => $rendered['subject'] ?? $this->notificationType->display_name,
                    'body' => $rendered['body'],
                    'sound' => 'default',
                    'badge' => 1,
                ],
                'data' => [
                    'notification_type' => $this->notificationType->name,
                    'user_id' => $this->user->id,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10',
                    ],
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1,
                        ],
                    ],
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => "key={$serverKey}",
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => $data['success'] > 0,
                    'message_id' => $data['results'][0]['message_id'] ?? null,
                ];
            } else {
                return ['success' => false, 'error' => $response->body()];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send push notification to Android device
     */
    private function sendToAndroid(PushNotificationToken $token, array $rendered): array
    {
        try {
            $serverKey = config('push.android.server_key');

            $payload = [
                'to' => $token->token,
                'notification' => [
                    'title' => $rendered['subject'] ?? $this->notificationType->display_name,
                    'body' => $rendered['body'],
                    'sound' => 'default',
                    'icon' => 'ic_notification',
                ],
                'data' => [
                    'notification_type' => $this->notificationType->name,
                    'user_id' => $this->user->id,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
                'priority' => 'high',
            ];

            $response = Http::withHeaders([
                'Authorization' => "key={$serverKey}",
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => $data['success'] > 0,
                    'message_id' => $data['results'][0]['message_id'] ?? null,
                ];
            } else {
                return ['success' => false, 'error' => $response->body()];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send push notification to Web browser
     */
    private function sendToWeb(PushNotificationToken $token, array $rendered): array
    {
        try {
            $serverKey = config('push.web.server_key');

            $payload = [
                'to' => $token->token,
                'notification' => [
                    'title' => $rendered['subject'] ?? $this->notificationType->display_name,
                    'body' => $rendered['body'],
                    'icon' => '/images/notification-icon.png',
                    'badge' => '/images/badge-icon.png',
                    'requireInteraction' => true,
                ],
                'data' => [
                    'notification_type' => $this->notificationType->name,
                    'user_id' => $this->user->id,
                    'url' => $this->data['url'] ?? '/',
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => "key={$serverKey}",
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => $data['success'] > 0,
                    'message_id' => $data['results'][0]['message_id'] ?? null,
                ];
            } else {
                return ['success' => false, 'error' => $response->body()];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Push notification job failed: " . $exception->getMessage(), [
            'user_id' => $this->user->id,
            'notification_type' => $this->notificationType->name,
            'error' => $exception->getTraceAsString()
        ]);
    }
}




