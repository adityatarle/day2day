<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocalPurchaseNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'local_purchase_id',
        'user_id',
        'type',
        'is_read',
        'is_email_sent',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_email_sent' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the local purchase associated with this notification.
     */
    public function localPurchase(): BelongsTo
    {
        return $this->belongsTo(LocalPurchase::class);
    }

    /**
     * Get the user who should receive this notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark the email as sent.
     */
    public function markEmailAsSent(): void
    {
        $this->update(['is_email_sent' => true]);
    }

    /**
     * Scope to get unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to get notifications for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get notifications that need email sending.
     */
    public function scopeNeedsEmail($query)
    {
        return $query->where('is_email_sent', false);
    }

    /**
     * Get the notification message.
     */
    public function getMessage(): string
    {
        $purchase = $this->localPurchase;
        $branch = $purchase->branch;
        $manager = $purchase->manager;

        switch ($this->type) {
            case 'created':
                return sprintf(
                    'New local purchase #%s created by %s at %s branch for %s',
                    $purchase->purchase_number,
                    $manager->name,
                    $branch->name,
                    '₹' . number_format($purchase->total_amount, 2)
                );
                
            case 'approved':
                return sprintf(
                    'Local purchase #%s has been approved by %s',
                    $purchase->purchase_number,
                    $purchase->approvedBy->name
                );
                
            case 'rejected':
                return sprintf(
                    'Local purchase #%s has been rejected by %s. Reason: %s',
                    $purchase->purchase_number,
                    $purchase->approvedBy->name,
                    $purchase->rejection_reason ?: 'No reason provided'
                );
                
            case 'updated':
                return sprintf(
                    'Local purchase #%s has been updated by %s',
                    $purchase->purchase_number,
                    $manager->name
                );
                
            default:
                return 'Local purchase notification';
        }
    }

    /**
     * Get the notification title.
     */
    public function getTitle(): string
    {
        switch ($this->type) {
            case 'created':
                return 'New Local Purchase';
            case 'approved':
                return 'Local Purchase Approved';
            case 'rejected':
                return 'Local Purchase Rejected';
            case 'updated':
                return 'Local Purchase Updated';
            default:
                return 'Local Purchase Notification';
        }
    }

    /**
     * Get the notification icon.
     */
    public function getIcon(): string
    {
        switch ($this->type) {
            case 'created':
                return 'fas fa-shopping-cart';
            case 'approved':
                return 'fas fa-check-circle';
            case 'rejected':
                return 'fas fa-times-circle';
            case 'updated':
                return 'fas fa-edit';
            default:
                return 'fas fa-bell';
        }
    }

    /**
     * Get the notification color.
     */
    public function getColor(): string
    {
        switch ($this->type) {
            case 'created':
                return 'blue';
            case 'approved':
                return 'green';
            case 'rejected':
                return 'red';
            case 'updated':
                return 'yellow';
            default:
                return 'gray';
        }
    }
}