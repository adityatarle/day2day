<?php

namespace App\Mail;

use App\Models\LocalPurchase;
use App\Models\LocalPurchaseNotification as Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LocalPurchaseNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public LocalPurchase $localPurchase,
        public Notification $notification
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match($this->notification->type) {
            'created' => 'New Local Purchase Created - ' . $this->localPurchase->purchase_number,
            'approved' => 'Local Purchase Approved - ' . $this->localPurchase->purchase_number,
            'rejected' => 'Local Purchase Rejected - ' . $this->localPurchase->purchase_number,
            'updated' => 'Local Purchase Updated - ' . $this->localPurchase->purchase_number,
            default => 'Local Purchase Notification - ' . $this->localPurchase->purchase_number,
        };

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.local-purchase-notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}