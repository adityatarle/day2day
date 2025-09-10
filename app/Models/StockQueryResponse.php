<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockQueryResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_transfer_query_id',
        'user_id',
        'response_type',
        'message',
        'attachments',
        'is_internal',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_internal' => 'boolean',
    ];

    /**
     * Get the query this response belongs to
     */
    public function stockTransferQuery(): BelongsTo
    {
        return $this->belongsTo(StockTransferQuery::class);
    }

    /**
     * Get the user who made this response
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for public responses (not internal)
     */
    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    /**
     * Scope for internal responses
     */
    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    /**
     * Scope for responses by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('response_type', $type);
    }

    /**
     * Get response type display name
     */
    public function getResponseTypeDisplayName(): string
    {
        return match($this->response_type) {
            'comment' => 'Comment',
            'status_update' => 'Status Update',
            'resolution' => 'Resolution',
            'escalation' => 'Escalation',
            default => ucfirst(str_replace('_', ' ', $this->response_type)),
        };
    }

    /**
     * Get response type color for UI
     */
    public function getResponseTypeColor(): string
    {
        return match($this->response_type) {
            'comment' => 'blue',
            'status_update' => 'yellow',
            'resolution' => 'green',
            'escalation' => 'red',
            default => 'gray',
        };
    }

    /**
     * Check if response has attachments
     */
    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    /**
     * Get attachment count
     */
    public function getAttachmentCount(): int
    {
        return count($this->attachments ?? []);
    }
}