<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransferQuery extends Model
{
    use HasFactory;

    protected $fillable = [
        'query_number',
        'stock_transfer_id',
        'stock_transfer_item_id',
        'raised_by',
        'assigned_to',
        'query_type',
        'priority',
        'status',
        'title',
        'description',
        'expected_quantity',
        'actual_quantity',
        'difference_quantity',
        'financial_impact',
        'evidence_photos',
        'documents',
        'resolution',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'expected_quantity' => 'decimal:3',
        'actual_quantity' => 'decimal:3',
        'difference_quantity' => 'decimal:3',
        'financial_impact' => 'decimal:2',
        'evidence_photos' => 'array',
        'documents' => 'array',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->query_number)) {
                $model->query_number = static::generateQueryNumber();
            }

            // Auto-calculate difference if quantities are provided
            if ($model->expected_quantity && $model->actual_quantity) {
                $model->difference_quantity = $model->actual_quantity - $model->expected_quantity;
            }
        });

        static::updating(function ($model) {
            // Update timestamps when status changes
            if ($model->isDirty('status')) {
                if ($model->status === 'resolved' && !$model->resolved_at) {
                    $model->resolved_at = now();
                }
                if ($model->status === 'closed' && !$model->closed_at) {
                    $model->closed_at = now();
                }
            }
        });
    }

    /**
     * Generate unique query number
     */
    public static function generateQueryNumber(): string
    {
        do {
            $number = 'SQ' . date('Ym') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (static::where('query_number', $number)->exists());

        return $number;
    }

    /**
     * Get the stock transfer this query belongs to
     */
    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    /**
     * Get the specific stock transfer item this query is about
     */
    public function stockTransferItem(): BelongsTo
    {
        return $this->belongsTo(StockTransferItem::class);
    }

    /**
     * Get the user who raised this query
     */
    public function raisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by');
    }

    /**
     * Get the user assigned to handle this query
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the responses to this query
     */
    public function responses(): HasMany
    {
        return $this->hasMany(StockQueryResponse::class);
    }

    /**
     * Get the financial impacts of this query
     */
    public function financialImpacts()
    {
        return $this->morphMany(StockFinancialImpact::class, 'impactable');
    }

    /**
     * Scope for queries by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for queries by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for queries by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('query_type', $type);
    }

    /**
     * Scope for open queries
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    /**
     * Scope for critical queries
     */
    public function scopeCritical($query)
    {
        return $query->where('priority', 'critical');
    }

    /**
     * Scope for overdue queries (open for more than 24 hours)
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'open')
                    ->where('created_at', '<', now()->subDay());
    }

    /**
     * Check if query is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === 'open' && $this->created_at->lt(now()->subDay());
    }

    /**
     * Get age in hours
     */
    public function getAgeInHours(): int
    {
        return $this->created_at->diffInHours(now());
    }

    /**
     * Get resolution time in hours
     */
    public function getResolutionTimeInHours(): ?int
    {
        if (!$this->resolved_at) {
            return null;
        }

        return $this->created_at->diffInHours($this->resolved_at);
    }

    /**
     * Assign to admin user
     */
    public function assignTo(User $user): bool
    {
        return $this->update([
            'assigned_to' => $user->id,
            'status' => 'in_progress'
        ]);
    }

    /**
     * Mark as resolved
     */
    public function markResolved(string $resolution, ?User $resolvedBy = null): bool
    {
        $updates = [
            'status' => 'resolved',
            'resolution' => $resolution,
            'resolved_at' => now(),
        ];

        if ($resolvedBy) {
            $updates['assigned_to'] = $resolvedBy->id;
        }

        return $this->update($updates);
    }

    /**
     * Mark as closed
     */
    public function markClosed(?string $notes = null): bool
    {
        $updates = [
            'status' => 'closed',
            'closed_at' => now(),
        ];

        if ($notes) {
            $updates['resolution'] = $this->resolution ? $this->resolution . "\n\nClosure Notes: " . $notes : $notes;
        }

        return $this->update($updates);
    }

    /**
     * Escalate query
     */
    public function escalate(?string $reason = null): bool
    {
        $this->update([
            'status' => 'escalated',
            'priority' => 'critical',
        ]);

        // Add escalation response
        $this->responses()->create([
            'user_id' => auth()->id(),
            'response_type' => 'escalation',
            'message' => $reason ?? 'Query has been escalated due to urgency.',
            'is_internal' => false,
        ]);

        return true;
    }

    /**
     * Add response to query
     */
    public function addResponse(string $message, string $type = 'comment', ?array $attachments = null, bool $isInternal = false): StockQueryResponse
    {
        return $this->responses()->create([
            'user_id' => auth()->id(),
            'response_type' => $type,
            'message' => $message,
            'attachments' => $attachments,
            'is_internal' => $isInternal,
        ]);
    }

    /**
     * Calculate automatic financial impact
     */
    public function calculateFinancialImpact(): float
    {
        if (!$this->stockTransferItem) {
            return 0;
        }

        $item = $this->stockTransferItem;
        
        return match($this->query_type) {
            'weight_difference', 'quantity_shortage' => abs($this->difference_quantity ?? 0) * $item->unit_price,
            'damaged_goods', 'expired_goods' => ($this->actual_quantity ?? 0) * $item->unit_price,
            'missing_items' => ($this->expected_quantity ?? 0) * $item->unit_price,
            default => 0,
        };
    }

    /**
     * Update financial impact
     */
    public function updateFinancialImpact(?float $amount = null): void
    {
        $impact = $amount ?? $this->calculateFinancialImpact();
        
        $this->update(['financial_impact' => $impact]);

        // Create financial impact record
        if ($impact > 0) {
            $this->createFinancialImpactRecord($impact);
        }
    }

    /**
     * Create financial impact record
     */
    protected function createFinancialImpactRecord(float $amount): void
    {
        $impactType = match($this->query_type) {
            'weight_difference', 'quantity_shortage' => 'loss_shortage',
            'damaged_goods' => 'loss_damaged',
            'expired_goods' => 'loss_expired',
            'quality_issue' => 'loss_quality',
            default => 'other',
        };

        $this->financialImpacts()->create([
            'branch_id' => $this->stockTransfer->to_branch_id,
            'impact_type' => $impactType,
            'amount' => $amount,
            'impact_category' => 'direct_loss',
            'description' => "Loss due to {$this->getQueryTypeDisplayName()}: {$this->title}",
            'impact_date' => now()->toDateString(),
            'is_recoverable' => in_array($this->query_type, ['weight_difference', 'quantity_shortage']),
        ]);
    }

    /**
     * Get query type display name
     */
    public function getQueryTypeDisplayName(): string
    {
        return match($this->query_type) {
            'weight_difference' => 'Weight Difference',
            'quantity_shortage' => 'Quantity Shortage',
            'quality_issue' => 'Quality Issue',
            'damaged_goods' => 'Damaged Goods',
            'expired_goods' => 'Expired Goods',
            'missing_items' => 'Missing Items',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->query_type)),
        };
    }

    /**
     * Get priority display name
     */
    public function getPriorityDisplayName(): string
    {
        return match($this->priority) {
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'critical' => 'Critical',
            default => ucfirst($this->priority),
        };
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayName(): string
    {
        return match($this->status) {
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
            'escalated' => 'Escalated',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get priority color for UI
     */
    public function getPriorityColor(): string
    {
        return match($this->priority) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'orange',
            'critical' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'open' => 'red',
            'in_progress' => 'yellow',
            'resolved' => 'blue',
            'closed' => 'green',
            'escalated' => 'purple',
            default => 'gray',
        };
    }
}