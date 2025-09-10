<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'product_id',
        'stock_transfer_id',
        'alert_type',
        'severity',
        'title',
        'message',
        'recipients',
        'is_read',
        'is_resolved',
        'resolved_at',
    ];

    protected $casts = [
        'recipients' => 'array',
        'is_read' => 'boolean',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the branch this alert belongs to
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the product this alert is about (if applicable)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the stock transfer this alert is about (if applicable)
     */
    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    /**
     * Scope for unread alerts
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for unresolved alerts
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Scope for alerts by severity
     */
    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope for critical alerts
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    /**
     * Scope for alerts by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('alert_type', $type);
    }

    /**
     * Scope for alerts by branch
     */
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Mark alert as read
     */
    public function markAsRead(): bool
    {
        return $this->update(['is_read' => true]);
    }

    /**
     * Mark alert as resolved
     */
    public function markAsResolved(): bool
    {
        return $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
        ]);
    }

    /**
     * Get alert type display name
     */
    public function getAlertTypeDisplayName(): string
    {
        return match($this->alert_type) {
            'low_stock' => 'Low Stock',
            'expiry_warning' => 'Expiry Warning',
            'transfer_delay' => 'Transfer Delay',
            'query_pending' => 'Query Pending',
            'reconciliation_required' => 'Reconciliation Required',
            'financial_impact' => 'Financial Impact',
            'quality_issue' => 'Quality Issue',
            default => ucfirst(str_replace('_', ' ', $this->alert_type)),
        };
    }

    /**
     * Get severity display name
     */
    public function getSeverityDisplayName(): string
    {
        return match($this->severity) {
            'info' => 'Info',
            'warning' => 'Warning',
            'critical' => 'Critical',
            default => ucfirst($this->severity),
        };
    }

    /**
     * Get severity color for UI
     */
    public function getSeverityColor(): string
    {
        return match($this->severity) {
            'info' => 'blue',
            'warning' => 'yellow',
            'critical' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get alert icon for UI
     */
    public function getAlertIcon(): string
    {
        return match($this->alert_type) {
            'low_stock' => 'exclamation-triangle',
            'expiry_warning' => 'clock',
            'transfer_delay' => 'truck',
            'query_pending' => 'question-circle',
            'reconciliation_required' => 'balance-scale',
            'financial_impact' => 'dollar-sign',
            'quality_issue' => 'times-circle',
            default => 'bell',
        };
    }

    /**
     * Check if alert is for current user
     */
    public function isForUser(int $userId): bool
    {
        return empty($this->recipients) || in_array($userId, $this->recipients ?? []);
    }

    /**
     * Get age in hours
     */
    public function getAgeInHours(): int
    {
        return $this->created_at->diffInHours(now());
    }

    /**
     * Check if alert is stale (older than 24 hours and unresolved)
     */
    public function isStale(): bool
    {
        return !$this->is_resolved && $this->getAgeInHours() > 24;
    }

    /**
     * Static method to create low stock alert
     */
    public static function createLowStockAlert(int $branchId, int $productId, float $currentStock, float $threshold): self
    {
        return static::create([
            'branch_id' => $branchId,
            'product_id' => $productId,
            'alert_type' => 'low_stock',
            'severity' => $currentStock <= 0 ? 'critical' : 'warning',
            'title' => 'Low Stock Alert',
            'message' => "Stock is running low. Current: {$currentStock}, Threshold: {$threshold}",
        ]);
    }

    /**
     * Static method to create expiry warning alert
     */
    public static function createExpiryWarningAlert(int $branchId, int $productId, string $expiryDate, int $daysUntilExpiry): self
    {
        $severity = $daysUntilExpiry <= 1 ? 'critical' : ($daysUntilExpiry <= 3 ? 'warning' : 'info');

        return static::create([
            'branch_id' => $branchId,
            'product_id' => $productId,
            'alert_type' => 'expiry_warning',
            'severity' => $severity,
            'title' => 'Product Expiry Warning',
            'message' => "Product expires on {$expiryDate} ({$daysUntilExpiry} days remaining)",
        ]);
    }

    /**
     * Static method to create transfer delay alert
     */
    public static function createTransferDelayAlert(int $branchId, int $stockTransferId, string $transferNumber, int $daysOverdue): self
    {
        return static::create([
            'branch_id' => $branchId,
            'stock_transfer_id' => $stockTransferId,
            'alert_type' => 'transfer_delay',
            'severity' => $daysOverdue > 3 ? 'critical' : 'warning',
            'title' => 'Transfer Delay',
            'message' => "Transfer {$transferNumber} is {$daysOverdue} days overdue",
        ]);
    }

    /**
     * Static method to create query pending alert
     */
    public static function createQueryPendingAlert(int $branchId, string $queryNumber, int $hoursOld): self
    {
        return static::create([
            'branch_id' => $branchId,
            'alert_type' => 'query_pending',
            'severity' => $hoursOld > 48 ? 'critical' : ($hoursOld > 24 ? 'warning' : 'info'),
            'title' => 'Pending Query',
            'message' => "Query {$queryNumber} has been pending for {$hoursOld} hours",
        ]);
    }
}