<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\NotificationType;
use App\Models\NotificationHistory;
use App\Models\SmsLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SendSmsNotificationJob implements ShouldQueue
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
            // Create notification history record
            $notification = NotificationHistory::createRecord(
                $this->user->id,
                $this->notificationType->id,
                'sms',
                $this->rendered['subject'] ?? $this->notificationType->display_name,
                $this->rendered['body'],
                $this->data,
                'sent'
            );

            // Send SMS using configured provider
            $result = $this->sendSms($this->user->phone, $this->rendered['body']);

            // Log SMS
            $smsLog = SmsLog::create([
                'user_id' => $this->user->id,
                'phone_number' => $this->user->phone,
                'message' => $this->rendered['body'],
                'provider' => config('sms.default_provider', 'twilio'),
                'provider_message_id' => $result['message_id'] ?? null,
                'status' => $result['status'] ?? 'sent',
                'cost' => $result['cost'] ?? null,
                'sent_at' => now(),
            ]);

            // Update notification status
            $notification->update([
                'status' => $result['status'] === 'sent' ? 'delivered' : 'failed',
                'metadata' => [
                    'sms_log_id' => $smsLog->id,
                    'provider_response' => $result
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to send SMS notification: " . $e->getMessage(), [
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
     * Send SMS using configured provider
     */
    private function sendSms(string $phoneNumber, string $message): array
    {
        $provider = config('sms.default_provider', 'twilio');
        
        switch ($provider) {
            case 'twilio':
                return $this->sendViaTwilio($phoneNumber, $message);
            case 'textlocal':
                return $this->sendViaTextLocal($phoneNumber, $message);
            default:
                throw new \Exception("Unsupported SMS provider: {$provider}");
        }
    }

    /**
     * Send SMS via Twilio
     */
    private function sendViaTwilio(string $phoneNumber, string $message): array
    {
        $accountSid = config('sms.twilio.account_sid');
        $authToken = config('sms.twilio.auth_token');
        $fromNumber = config('sms.twilio.from_number');

        $response = Http::withBasicAuth($accountSid, $authToken)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'From' => $fromNumber,
                'To' => $phoneNumber,
                'Body' => $message,
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'status' => 'sent',
                'message_id' => $data['sid'] ?? null,
                'cost' => $data['price'] ?? null,
            ];
        } else {
            throw new \Exception("Twilio API error: " . $response->body());
        }
    }

    /**
     * Send SMS via TextLocal
     */
    private function sendViaTextLocal(string $phoneNumber, string $message): array
    {
        $apiKey = config('sms.textlocal.api_key');
        $sender = config('sms.textlocal.sender');

        $response = Http::post('https://api.textlocal.in/send/', [
            'apikey' => $apiKey,
            'numbers' => $phoneNumber,
            'message' => $message,
            'sender' => $sender,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'status' => $data['status'] === 'success' ? 'sent' : 'failed',
                'message_id' => $data['batch_id'] ?? null,
                'cost' => $data['cost'] ?? null,
            ];
        } else {
            throw new \Exception("TextLocal API error: " . $response->body());
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SMS notification job failed: " . $exception->getMessage(), [
            'user_id' => $this->user->id,
            'notification_type' => $this->notificationType->name,
            'error' => $exception->getTraceAsString()
        ]);
    }
}




