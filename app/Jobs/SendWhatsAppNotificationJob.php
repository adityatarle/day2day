<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\NotificationType;
use App\Models\NotificationHistory;
use App\Models\WhatsappLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SendWhatsAppNotificationJob implements ShouldQueue
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
                'whatsapp',
                $this->rendered['subject'] ?? $this->notificationType->display_name,
                $this->rendered['body'],
                $this->data,
                'sent'
            );

            // Send WhatsApp message
            $result = $this->sendWhatsAppMessage($this->user->phone, $this->rendered['body']);

            // Log WhatsApp message
            $whatsappLog = WhatsappLog::create([
                'user_id' => $this->user->id,
                'phone_number' => $this->user->phone,
                'message' => $this->rendered['body'],
                'template_name' => $this->data['template_name'] ?? null,
                'template_params' => $this->data['template_params'] ?? null,
                'provider' => config('whatsapp.default_provider', 'whatsapp_business_api'),
                'provider_message_id' => $result['message_id'] ?? null,
                'status' => $result['status'] ?? 'sent',
                'sent_at' => now(),
            ]);

            // Update notification status
            $notification->update([
                'status' => $result['status'] === 'sent' ? 'delivered' : 'failed',
                'metadata' => [
                    'whatsapp_log_id' => $whatsappLog->id,
                    'provider_response' => $result
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp notification: " . $e->getMessage(), [
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
     * Send WhatsApp message using configured provider
     */
    private function sendWhatsAppMessage(string $phoneNumber, string $message): array
    {
        $provider = config('whatsapp.default_provider', 'whatsapp_business_api');
        
        switch ($provider) {
            case 'whatsapp_business_api':
                return $this->sendViaWhatsAppBusinessApi($phoneNumber, $message);
            case 'twilio_whatsapp':
                return $this->sendViaTwilioWhatsApp($phoneNumber, $message);
            default:
                throw new \Exception("Unsupported WhatsApp provider: {$provider}");
        }
    }

    /**
     * Send WhatsApp message via WhatsApp Business API
     */
    private function sendViaWhatsAppBusinessApi(string $phoneNumber, string $message): array
    {
        $accessToken = config('whatsapp.business_api.access_token');
        $phoneNumberId = config('whatsapp.business_api.phone_number_id');
        $businessAccountId = config('whatsapp.business_api.business_account_id');

        // Format phone number (remove + and ensure it starts with country code)
        $formattedPhone = $this->formatPhoneNumber($phoneNumber);

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$accessToken}",
            'Content-Type' => 'application/json',
        ])->post("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages", [
            'messaging_product' => 'whatsapp',
            'to' => $formattedPhone,
            'type' => 'text',
            'text' => [
                'body' => $message
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'status' => 'sent',
                'message_id' => $data['messages'][0]['id'] ?? null,
            ];
        } else {
            throw new \Exception("WhatsApp Business API error: " . $response->body());
        }
    }

    /**
     * Send WhatsApp message via Twilio
     */
    private function sendViaTwilioWhatsApp(string $phoneNumber, string $message): array
    {
        $accountSid = config('whatsapp.twilio.account_sid');
        $authToken = config('whatsapp.twilio.auth_token');
        $fromNumber = config('whatsapp.twilio.from_number');

        $response = Http::withBasicAuth($accountSid, $authToken)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'From' => "whatsapp:{$fromNumber}",
                'To' => "whatsapp:{$phoneNumber}",
                'Body' => $message,
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'status' => 'sent',
                'message_id' => $data['sid'] ?? null,
            ];
        } else {
            throw new \Exception("Twilio WhatsApp API error: " . $response->body());
        }
    }

    /**
     * Format phone number for WhatsApp
     */
    private function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // If it doesn't start with country code, assume it's Indian number
        if (!str_starts_with($phoneNumber, '91') && strlen($phoneNumber) === 10) {
            $phoneNumber = '91' . $phoneNumber;
        }
        
        return $phoneNumber;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("WhatsApp notification job failed: " . $exception->getMessage(), [
            'user_id' => $this->user->id,
            'notification_type' => $this->notificationType->name,
            'error' => $exception->getTraceAsString()
        ]);
    }
}




