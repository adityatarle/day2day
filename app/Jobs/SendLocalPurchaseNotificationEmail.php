<?php

namespace App\Jobs;

use App\Mail\LocalPurchaseNotification as NotificationMail;
use App\Models\LocalPurchaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendLocalPurchaseNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public LocalPurchaseNotification $notification
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Load relationships
            $this->notification->load(['localPurchase.branch', 'localPurchase.manager', 'localPurchase.items.product', 'user']);

            // Send email
            Mail::to($this->notification->user->email)
                ->send(new NotificationMail(
                    $this->notification->localPurchase,
                    $this->notification
                ));

            // Mark email as sent
            $this->notification->markEmailAsSent();

            Log::info('Local purchase notification email sent', [
                'notification_id' => $this->notification->id,
                'user_email' => $this->notification->user->email,
                'purchase_number' => $this->notification->localPurchase->purchase_number,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send local purchase notification email', [
                'notification_id' => $this->notification->id,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to allow retry
            throw $e;
        }
    }

    /**
     * Get the number of seconds to wait before retrying the job.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 120, 300]; // 1 min, 2 min, 5 min
    }

    /**
     * Determine the number of times the job may be attempted.
     */
    public function tries(): int
    {
        return 3;
    }
}