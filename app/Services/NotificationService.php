<?php

namespace App\Services;

use App\Models\StockAlert;
use App\Models\StockTransfer;
use App\Models\StockTransferQuery;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Exception;

class NotificationService
{
    /**
     * Create stock alert
     */
    public function createAlert(array $alertData): StockAlert
    {
        try {
            $alert = StockAlert::create([
                'branch_id' => $alertData['branch_id'],
                'product_id' => $alertData['product_id'] ?? null,
                'stock_transfer_id' => $alertData['stock_transfer_id'] ?? null,
                'alert_type' => $alertData['alert_type'],
                'severity' => $alertData['severity'],
                'title' => $alertData['title'],
                'message' => $alertData['message'],
                'recipients' => $alertData['recipients'] ?? null,
            ]);

            // Send notifications based on severity
            $this->processAlert($alert);

            Log::info("Stock alert created", [
                'alert_id' => $alert->id,
                'type' => $alert->alert_type,
                'severity' => $alert->severity,
                'branch_id' => $alert->branch_id,
            ]);

            return $alert;

        } catch (Exception $e) {
            Log::error("Failed to create stock alert", [
                'alert_data' => $alertData,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Process alert and send notifications
     */
    public function processAlert(StockAlert $alert): void
    {
        try {
            // Determine recipients based on alert type and severity
            $recipients = $this->determineRecipients($alert);

            // Send notifications to each recipient
            foreach ($recipients as $recipient) {
                $this->sendNotification($recipient, $alert);
            }

            // Update alert with recipients
            $alert->update([
                'recipients' => $recipients->pluck('id')->toArray()
            ]);

        } catch (Exception $e) {
            Log::error("Failed to process alert", [
                'alert_id' => $alert->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send notification to user
     */
    protected function sendNotification(User $user, StockAlert $alert): void
    {
        try {
            // Send in-app notification
            $this->sendInAppNotification($user, $alert);

            // Send email for critical alerts or user preference
            if ($alert->severity === 'critical' || $this->shouldSendEmail($user, $alert)) {
                $this->sendEmailNotification($user, $alert);
            }

            // Send SMS for critical alerts (if phone number available)
            if ($alert->severity === 'critical' && $user->phone) {
                $this->sendSMSNotification($user, $alert);
            }

        } catch (Exception $e) {
            Log::error("Failed to send notification", [
                'user_id' => $user->id,
                'alert_id' => $alert->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send in-app notification
     */
    protected function sendInAppNotification(User $user, StockAlert $alert): void
    {
        // This would integrate with your notification system
        // For now, we'll just log it
        Log::info("In-app notification sent", [
            'user_id' => $user->id,
            'alert_id' => $alert->id,
            'title' => $alert->title,
        ]);
    }

    /**
     * Send email notification
     */
    protected function sendEmailNotification(User $user, StockAlert $alert): void
    {
        try {
            // This would send actual email using Laravel's mail system
            Log::info("Email notification sent", [
                'user_id' => $user->id,
                'email' => $user->email,
                'alert_id' => $alert->id,
                'title' => $alert->title,
            ]);

            // Actual implementation would be:
            // Mail::to($user->email)->send(new StockAlertMail($alert));

        } catch (Exception $e) {
            Log::error("Failed to send email notification", [
                'user_id' => $user->id,
                'alert_id' => $alert->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send SMS notification
     */
    protected function sendSMSNotification(User $user, StockAlert $alert): void
    {
        try {
            // This would integrate with SMS service like Twilio
            Log::info("SMS notification sent", [
                'user_id' => $user->id,
                'phone' => $user->phone,
                'alert_id' => $alert->id,
                'title' => $alert->title,
            ]);

        } catch (Exception $e) {
            Log::error("Failed to send SMS notification", [
                'user_id' => $user->id,
                'alert_id' => $alert->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Determine alert recipients
     */
    protected function determineRecipients(StockAlert $alert): \Illuminate\Database\Eloquent\Collection
    {
        $recipients = collect();

        switch ($alert->alert_type) {
            case 'transfer_delay':
            case 'reconciliation_required':
                // Notify branch managers and admins
                $recipients = $recipients->merge($this->getBranchManagers($alert->branch_id));
                if ($alert->severity === 'critical') {
                    $recipients = $recipients->merge($this->getAdmins());
                }
                break;

            case 'query_pending':
                // Notify admins and assigned users
                $recipients = $recipients->merge($this->getAdmins());
                if ($alert->stock_transfer_id) {
                    $transfer = StockTransfer::find($alert->stock_transfer_id);
                    if ($transfer) {
                        $recipients->push($transfer->initiatedBy);
                    }
                }
                break;

            case 'financial_impact':
                // Notify admins and finance team
                $recipients = $recipients->merge($this->getAdmins());
                $recipients = $recipients->merge($this->getFinanceTeam());
                break;

            case 'quality_issue':
            case 'expiry_warning':
                // Notify branch managers, quality team, and admins
                $recipients = $recipients->merge($this->getBranchManagers($alert->branch_id));
                $recipients = $recipients->merge($this->getQualityTeam());
                if ($alert->severity === 'critical') {
                    $recipients = $recipients->merge($this->getAdmins());
                }
                break;

            case 'low_stock':
                // Notify branch managers and procurement team
                $recipients = $recipients->merge($this->getBranchManagers($alert->branch_id));
                $recipients = $recipients->merge($this->getProcurementTeam());
                break;

            default:
                // Default to admins for unknown alert types
                $recipients = $recipients->merge($this->getAdmins());
                break;
        }

        return $recipients->unique('id');
    }

    /**
     * Check if email should be sent to user
     */
    protected function shouldSendEmail(User $user, StockAlert $alert): bool
    {
        // This would check user preferences, business hours, etc.
        // For now, send email for high severity alerts
        return in_array($alert->severity, ['critical', 'warning']);
    }

    /**
     * Get branch managers for a branch
     */
    protected function getBranchManagers(int $branchId): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('branch_id', $branchId)
                  ->whereHas('roles', function ($q) {
                      $q->where('name', 'branch_manager');
                  })
                  ->get();
    }

    /**
     * Get admin users
     */
    protected function getAdmins(): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'super_admin']);
        })->get();
    }

    /**
     * Get finance team users
     */
    protected function getFinanceTeam(): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereHas('roles', function ($q) {
            $q->where('name', 'finance');
        })->get();
    }

    /**
     * Get quality team users
     */
    protected function getQualityTeam(): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereHas('roles', function ($q) {
            $q->where('name', 'quality');
        })->get();
    }

    /**
     * Get procurement team users
     */
    protected function getProcurementTeam(): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereHas('roles', function ($q) {
            $q->where('name', 'procurement');
        })->get();
    }

    /**
     * Mark alert as read
     */
    public function markAlertAsRead(StockAlert $alert, User $user): bool
    {
        try {
            $result = $alert->update(['is_read' => true]);

            Log::info("Alert marked as read", [
                'alert_id' => $alert->id,
                'user_id' => $user->id,
            ]);

            return $result;

        } catch (Exception $e) {
            Log::error("Failed to mark alert as read", [
                'alert_id' => $alert->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Resolve alert
     */
    public function resolveAlert(StockAlert $alert, User $user, ?string $resolution = null): bool
    {
        try {
            $result = $alert->update([
                'is_resolved' => true,
                'resolved_at' => now(),
                'resolution' => $resolution,
            ]);

            Log::info("Alert resolved", [
                'alert_id' => $alert->id,
                'user_id' => $user->id,
                'resolution' => $resolution,
            ]);

            return $result;

        } catch (Exception $e) {
            Log::error("Failed to resolve alert", [
                'alert_id' => $alert->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Create transfer delay alert
     */
    public function createTransferDelayAlert(StockTransfer $transfer): void
    {
        $this->createAlert([
            'branch_id' => $transfer->to_branch_id,
            'stock_transfer_id' => $transfer->id,
            'alert_type' => 'transfer_delay',
            'severity' => $transfer->isOverdue() ? 'critical' : 'warning',
            'title' => 'Transfer Delivery Overdue',
            'message' => "Transfer {$transfer->transfer_number} to {$transfer->toBranch->name} is overdue for delivery.",
        ]);
    }

    /**
     * Create query escalation alert
     */
    public function createQueryEscalationAlert(StockTransferQuery $query): void
    {
        $this->createAlert([
            'branch_id' => $query->stockTransfer->to_branch_id,
            'stock_transfer_id' => $query->stock_transfer_id,
            'alert_type' => 'query_pending',
            'severity' => 'critical',
            'title' => 'Query Escalated',
            'message' => "Query {$query->query_number} has been escalated and requires immediate attention.",
        ]);
    }

    /**
     * Create financial impact alert
     */
    public function createFinancialImpactAlert(int $branchId, float $impactAmount, string $description): void
    {
        $severity = $impactAmount > 10000 ? 'critical' : ($impactAmount > 5000 ? 'warning' : 'info');

        $this->createAlert([
            'branch_id' => $branchId,
            'alert_type' => 'financial_impact',
            'severity' => $severity,
            'title' => 'Significant Financial Impact',
            'message' => "Financial impact of ₹" . number_format($impactAmount, 2) . " detected: {$description}",
        ]);
    }

    /**
     * Create quality issue alert
     */
    public function createQualityIssueAlert(StockTransfer $transfer, string $issueDescription): void
    {
        $this->createAlert([
            'branch_id' => $transfer->to_branch_id,
            'stock_transfer_id' => $transfer->id,
            'alert_type' => 'quality_issue',
            'severity' => 'warning',
            'title' => 'Quality Issue Detected',
            'message' => "Quality issue detected in transfer {$transfer->transfer_number}: {$issueDescription}",
        ]);
    }

    /**
     * Create expiry warning alert
     */
    public function createExpiryWarningAlert(int $branchId, int $productId, string $productName, int $daysToExpiry): void
    {
        $severity = $daysToExpiry <= 3 ? 'critical' : ($daysToExpiry <= 7 ? 'warning' : 'info');

        $this->createAlert([
            'branch_id' => $branchId,
            'product_id' => $productId,
            'alert_type' => 'expiry_warning',
            'severity' => $severity,
            'title' => 'Product Expiry Warning',
            'message' => "Product {$productName} will expire in {$daysToExpiry} days.",
        ]);
    }

    /**
     * Send daily summary notifications
     */
    public function sendDailySummary(): void
    {
        try {
            $branches = Branch::active()->get();

            foreach ($branches as $branch) {
                $summary = $this->generateDailySummary($branch);
                
                if ($summary['has_activity']) {
                    $managers = $this->getBranchManagers($branch->id);
                    
                    foreach ($managers as $manager) {
                        $this->sendDailySummaryNotification($manager, $branch, $summary);
                    }
                }
            }

            // Send admin summary
            $adminSummary = $this->generateAdminDailySummary();
            $admins = $this->getAdmins();
            
            foreach ($admins as $admin) {
                $this->sendAdminDailySummaryNotification($admin, $adminSummary);
            }

        } catch (Exception $e) {
            Log::error("Failed to send daily summary notifications", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Generate daily summary for branch
     */
    protected function generateDailySummary(Branch $branch): array
    {
        $today = now()->toDateString();

        return [
            'branch_name' => $branch->name,
            'transfers_received' => StockTransfer::where('to_branch_id', $branch->id)
                                               ->whereDate('delivered_date', $today)
                                               ->count(),
            'transfers_confirmed' => StockTransfer::where('to_branch_id', $branch->id)
                                                 ->whereDate('confirmed_date', $today)
                                                 ->count(),
            'queries_raised' => StockTransferQuery::whereHas('stockTransfer', function ($q) use ($branch) {
                                  $q->where('to_branch_id', $branch->id);
                              })->whereDate('created_at', $today)->count(),
            'queries_resolved' => StockTransferQuery::whereHas('stockTransfer', function ($q) use ($branch) {
                                    $q->where('to_branch_id', $branch->id);
                                })->whereDate('resolved_at', $today)->count(),
            'pending_receipts' => StockTransfer::where('to_branch_id', $branch->id)
                                             ->where('status', 'delivered')
                                             ->count(),
            'open_queries' => StockTransferQuery::whereHas('stockTransfer', function ($q) use ($branch) {
                                $q->where('to_branch_id', $branch->id);
                              })->where('status', 'open')->count(),
            'has_activity' => false, // Will be set based on above values
        ];
    }

    /**
     * Generate admin daily summary
     */
    protected function generateAdminDailySummary(): array
    {
        $today = now()->toDateString();

        return [
            'total_transfers' => StockTransfer::whereDate('created_at', $today)->count(),
            'total_queries' => StockTransferQuery::whereDate('created_at', $today)->count(),
            'critical_queries' => StockTransferQuery::where('priority', 'critical')
                                                   ->where('status', 'open')
                                                   ->count(),
            'overdue_transfers' => StockTransfer::where('status', '!=', 'confirmed')
                                               ->where('status', '!=', 'cancelled')
                                               ->whereNotNull('expected_delivery')
                                               ->where('expected_delivery', '<', now())
                                               ->count(),
            'financial_impact_today' => \App\Models\StockFinancialImpact::whereDate('impact_date', $today)
                                                                        ->sum('amount'),
        ];
    }

    /**
     * Send daily summary notification to branch manager
     */
    protected function sendDailySummaryNotification(User $manager, Branch $branch, array $summary): void
    {
        // This would send actual notification
        Log::info("Daily summary sent to branch manager", [
            'manager_id' => $manager->id,
            'branch_id' => $branch->id,
            'summary' => $summary,
        ]);
    }

    /**
     * Send admin daily summary notification
     */
    protected function sendAdminDailySummaryNotification(User $admin, array $summary): void
    {
        // This would send actual notification
        Log::info("Daily summary sent to admin", [
            'admin_id' => $admin->id,
            'summary' => $summary,
        ]);
    }

    /**
     * Check and create automated alerts
     */
    public function checkAutomatedAlerts(): void
    {
        try {
            // Check for overdue transfers
            $this->checkOverdueTransfers();

            // Check for old unresolved queries
            $this->checkOldQueries();

            // Check for high financial impacts
            $this->checkHighFinancialImpacts();

            // Check for expiring products
            $this->checkExpiringProducts();

        } catch (Exception $e) {
            Log::error("Failed to check automated alerts", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check for overdue transfers
     */
    protected function checkOverdueTransfers(): void
    {
        $overdueTransfers = StockTransfer::where('status', '!=', 'confirmed')
                                       ->where('status', '!=', 'cancelled')
                                       ->whereNotNull('expected_delivery')
                                       ->where('expected_delivery', '<', now())
                                       ->get();

        foreach ($overdueTransfers as $transfer) {
            // Check if alert already exists
            $existingAlert = StockAlert::where('stock_transfer_id', $transfer->id)
                                     ->where('alert_type', 'transfer_delay')
                                     ->where('is_resolved', false)
                                     ->first();

            if (!$existingAlert) {
                $this->createTransferDelayAlert($transfer);
            }
        }
    }

    /**
     * Check for old unresolved queries
     */
    protected function checkOldQueries(): void
    {
        $oldQueries = StockTransferQuery::where('status', 'open')
                                       ->where('created_at', '<', now()->subDays(3))
                                       ->get();

        foreach ($oldQueries as $query) {
            // Escalate if not already escalated
            if ($query->priority !== 'critical') {
                $query->update(['priority' => 'high']);
                $this->createQueryEscalationAlert($query);
            }
        }
    }

    /**
     * Check for high financial impacts
     */
    protected function checkHighFinancialImpacts(): void
    {
        $highImpacts = \App\Models\StockFinancialImpact::where('amount', '>', 5000)
                                                       ->whereDate('impact_date', today())
                                                       ->get();

        foreach ($highImpacts as $impact) {
            $this->createFinancialImpactAlert(
                $impact->branch_id,
                $impact->amount,
                $impact->description
            );
        }
    }

    /**
     * Check for expiring products
     */
    protected function checkExpiringProducts(): void
    {
        // This would check product expiry dates
        // Implementation would depend on your product/inventory structure
        Log::info("Checking for expiring products");
    }
}