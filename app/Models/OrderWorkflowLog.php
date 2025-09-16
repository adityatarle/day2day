<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderWorkflowLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'user_id',
        'notes',
        'metadata',
        'transitioned_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'transitioned_at' => 'datetime'
    ];

    /**
     * Get the order for this workflow log.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who made this transition.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the duration since this transition.
     */
    public function getDurationAttribute(): string
    {
        return $this->transitioned_at->diffForHumans();
    }

    /**
     * Get the status display name.
     */
    public function getToStatusNameAttribute(): string
    {
        $workflowService = app(\App\Services\OrderWorkflowService::class);
        $states = $workflowService::WORKFLOW_STATES;
        
        return $states[$this->to_status]['name'] ?? ucfirst($this->to_status);
    }

    /**
     * Get the from status display name.
     */
    public function getFromStatusNameAttribute(): string
    {
        $workflowService = app(\App\Services\OrderWorkflowService::class);
        $states = $workflowService::WORKFLOW_STATES;
        
        return $states[$this->from_status]['name'] ?? ucfirst($this->from_status);
    }
}