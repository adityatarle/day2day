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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendEmailNotificationJob implements ShouldQueue
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
                'mail',
                $this->rendered['subject'] ?? $this->notificationType->display_name,
                $this->rendered['body'],
                $this->data,
                'sent'
            );

            // Send email
            Mail::to($this->user->email)->send(
                new \App\Mail\NotificationMail(
                    $this->rendered['subject'] ?? $this->notificationType->display_name,
                    $this->rendered['body'],
                    $this->data,
                    $this->notificationType
                )
            );

            // Update notification status
            $notification->update(['status' => 'delivered']);

        } catch (\Exception $e) {
            Log::error("Failed to send email notification: " . $e->getMessage(), [
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
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Email notification job failed: " . $exception->getMessage(), [
            'user_id' => $this->user->id,
            'notification_type' => $this->notificationType->name,
            'error' => $exception->getTraceAsString()
        ]);
    }
}





