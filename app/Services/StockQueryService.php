<?php

namespace App\Services;

use App\Models\StockTransferQuery;
use App\Models\StockQueryResponse;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\User;
use App\Models\StockAlert;
use App\Models\StockFinancialImpact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class StockQueryService
{
    /**
     * Create a new stock transfer query
     */
    public function createQuery(array $queryData, User $branchManager): StockTransferQuery
    {
        DB::beginTransaction();

        try {
            $query = StockTransferQuery::create([
                'stock_transfer_id' => $queryData['stock_transfer_id'],
                'stock_transfer_item_id' => $queryData['stock_transfer_item_id'] ?? null,
                'raised_by' => $branchManager->id,
                'query_type' => $queryData['query_type'],
                'priority' => $queryData['priority'] ?? 'medium',
                'title' => $queryData['title'],
                'description' => $queryData['description'],
                'expected_quantity' => $queryData['expected_quantity'] ?? null,
                'actual_quantity' => $queryData['actual_quantity'] ?? null,
                'evidence_photos' => $queryData['evidence_photos'] ?? null,
                'documents' => $queryData['documents'] ?? null,
            ]);

            // Calculate and set financial impact
            $query->updateFinancialImpact();

            // Create alert for admin
            $this->createQueryAlert($query);

            // Auto-assign to admin if specified
            if (isset($queryData['assigned_to'])) {
                $query->assignTo(User::find($queryData['assigned_to']));
            }

            DB::commit();

            Log::info("Stock query created", [
                'query_id' => $query->id,
                'query_number' => $query->query_number,
                'branch_manager_id' => $branchManager->id,
                'transfer_id' => $queryData['stock_transfer_id'],
            ]);

            return $query;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to create stock query", [
                'error' => $e->getMessage(),
                'branch_manager_id' => $branchManager->id,
                'query_data' => $queryData,
            ]);
            throw $e;
        }
    }

    /**
     * Assign query to admin user
     */
    public function assignQuery(StockTransferQuery $query, User $admin): bool
    {
        try {
            $result = $query->assignTo($admin);

            if ($result) {
                // Add assignment response
                $query->addResponse(
                    "Query has been assigned to {$admin->name}",
                    'status_update',
                    null,
                    false
                );

                Log::info("Stock query assigned", [
                    'query_id' => $query->id,
                    'query_number' => $query->query_number,
                    'assigned_to' => $admin->id,
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Log::error("Failed to assign stock query", [
                'query_id' => $query->id,
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Add response to query
     */
    public function addResponse(
        StockTransferQuery $query, 
        User $user, 
        string $message, 
        string $type = 'comment',
        ?array $attachments = null,
        bool $isInternal = false
    ): StockQueryResponse {
        try {
            $response = $query->addResponse($message, $type, $attachments, $isInternal);

            Log::info("Response added to stock query", [
                'query_id' => $query->id,
                'response_id' => $response->id,
                'user_id' => $user->id,
                'type' => $type,
            ]);

            return $response;

        } catch (Exception $e) {
            Log::error("Failed to add response to stock query", [
                'query_id' => $query->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Resolve query with resolution
     */
    public function resolveQuery(
        StockTransferQuery $query, 
        string $resolution, 
        User $resolver,
        ?float $recoveryAmount = null
    ): bool {
        DB::beginTransaction();

        try {
            $result = $query->markResolved($resolution, $resolver);

            if ($result) {
                // Add resolution response
                $query->addResponse(
                    $resolution,
                    'resolution',
                    null,
                    false
                );

                // Record financial recovery if applicable
                if ($recoveryAmount && $recoveryAmount > 0) {
                    $this->recordFinancialRecovery($query, $recoveryAmount, $resolution);
                }

                Log::info("Stock query resolved", [
                    'query_id' => $query->id,
                    'query_number' => $query->query_number,
                    'resolver_id' => $resolver->id,
                    'recovery_amount' => $recoveryAmount,
                ]);
            }

            DB::commit();
            return $result;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to resolve stock query", [
                'query_id' => $query->id,
                'resolver_id' => $resolver->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Escalate query
     */
    public function escalateQuery(StockTransferQuery $query, User $escalator, ?string $reason = null): bool
    {
        try {
            $result = $query->escalate($reason);

            if ($result) {
                // Create critical alert
                StockAlert::create([
                    'branch_id' => $query->stockTransfer->to_branch_id,
                    'stock_transfer_id' => $query->stock_transfer_id,
                    'alert_type' => 'query_pending',
                    'severity' => 'critical',
                    'title' => 'Query Escalated',
                    'message' => "Query {$query->query_number} has been escalated: " . ($reason ?? 'No reason provided'),
                ]);

                Log::info("Stock query escalated", [
                    'query_id' => $query->id,
                    'query_number' => $query->query_number,
                    'escalator_id' => $escalator->id,
                    'reason' => $reason,
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Log::error("Failed to escalate stock query", [
                'query_id' => $query->id,
                'escalator_id' => $escalator->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Close query
     */
    public function closeQuery(StockTransferQuery $query, User $closer, ?string $notes = null): bool
    {
        try {
            $result = $query->markClosed($notes);

            if ($result) {
                if ($notes) {
                    $query->addResponse(
                        "Query closed: " . $notes,
                        'status_update',
                        null,
                        false
                    );
                }

                Log::info("Stock query closed", [
                    'query_id' => $query->id,
                    'query_number' => $query->query_number,
                    'closer_id' => $closer->id,
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Log::error("Failed to close stock query", [
                'query_id' => $query->id,
                'closer_id' => $closer->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Upload evidence files for query
     */
    public function uploadEvidence(StockTransferQuery $query, array $files, string $type = 'photos'): array
    {
        $uploadedFiles = [];

        try {
            foreach ($files as $file) {
                $path = $file->store("stock-queries/{$query->id}/{$type}", 'public');
                $uploadedFiles[] = [
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_at' => now()->toISOString(),
                ];
            }

            // Update query with new files
            $currentFiles = $type === 'photos' ? $query->evidence_photos ?? [] : $query->documents ?? [];
            $allFiles = array_merge($currentFiles, $uploadedFiles);

            $query->update([
                $type === 'photos' ? 'evidence_photos' : 'documents' => $allFiles
            ]);

            Log::info("Evidence uploaded for stock query", [
                'query_id' => $query->id,
                'type' => $type,
                'file_count' => count($uploadedFiles),
            ]);

            return $uploadedFiles;

        } catch (Exception $e) {
            // Clean up uploaded files on error
            foreach ($uploadedFiles as $file) {
                Storage::disk('public')->delete($file['path']);
            }

            Log::error("Failed to upload evidence for stock query", [
                'query_id' => $query->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get query statistics for branch or overall
     */
    public function getQueryStatistics(?int $branchId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = StockTransferQuery::query();

        if ($branchId) {
            $query->whereHas('stockTransfer', function ($q) use ($branchId) {
                $q->where('to_branch_id', $branchId);
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $queries = $query->get();

        return [
            'total_queries' => $queries->count(),
            'open_queries' => $queries->where('status', 'open')->count(),
            'in_progress_queries' => $queries->where('status', 'in_progress')->count(),
            'resolved_queries' => $queries->where('status', 'resolved')->count(),
            'closed_queries' => $queries->where('status', 'closed')->count(),
            'escalated_queries' => $queries->where('status', 'escalated')->count(),
            'total_financial_impact' => $queries->sum('financial_impact'),
            'average_resolution_time' => $this->calculateAverageResolutionTime($queries),
            'queries_by_type' => $this->getQueriesByType($queries),
            'queries_by_priority' => $this->getQueriesByPriority($queries),
            'overdue_queries' => $queries->filter(fn($q) => $q->isOverdue())->count(),
        ];
    }

    /**
     * Get overdue queries
     */
    public function getOverdueQueries(?int $branchId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = StockTransferQuery::where('status', 'open')
                                  ->where('created_at', '<', now()->subDay());

        if ($branchId) {
            $query->whereHas('stockTransfer', function ($q) use ($branchId) {
                $q->where('to_branch_id', $branchId);
            });
        }

        return $query->with(['stockTransfer.toBranch', 'raisedBy'])->get();
    }

    /**
     * Create alert for new query
     */
    protected function createQueryAlert(StockTransferQuery $query): void
    {
        StockAlert::create([
            'branch_id' => $query->stockTransfer->to_branch_id,
            'stock_transfer_id' => $query->stock_transfer_id,
            'alert_type' => 'query_pending',
            'severity' => $query->priority === 'critical' ? 'critical' : 'warning',
            'title' => 'New Stock Query Raised',
            'message' => "Query {$query->query_number} has been raised: {$query->title}",
        ]);
    }

    /**
     * Record financial recovery for query
     */
    protected function recordFinancialRecovery(StockTransferQuery $query, float $amount, string $notes): void
    {
        $financialImpacts = $query->financialImpacts()->get();

        foreach ($financialImpacts as $impact) {
            $remainingRecovery = $amount - $impact->recovered_amount;
            if ($remainingRecovery > 0) {
                $recoveryAmount = min($remainingRecovery, $impact->getNetImpactAmount());
                $impact->recordRecovery($recoveryAmount, $notes);
                $amount -= $recoveryAmount;

                if ($amount <= 0) break;
            }
        }
    }

    /**
     * Calculate average resolution time in hours
     */
    protected function calculateAverageResolutionTime($queries): ?float
    {
        $resolvedQueries = $queries->filter(function ($query) {
            return $query->resolved_at;
        });

        if ($resolvedQueries->isEmpty()) {
            return null;
        }

        $totalHours = $resolvedQueries->sum(function ($query) {
            return $query->created_at->diffInHours($query->resolved_at);
        });

        return round($totalHours / $resolvedQueries->count(), 2);
    }

    /**
     * Get queries grouped by type
     */
    protected function getQueriesByType($queries): array
    {
        return $queries->groupBy('query_type')->map(function ($group) {
            return $group->count();
        })->toArray();
    }

    /**
     * Get queries grouped by priority
     */
    protected function getQueriesByPriority($queries): array
    {
        return $queries->groupBy('priority')->map(function ($group) {
            return $group->count();
        })->toArray();
    }

    /**
     * Generate query trends report
     */
    public function generateTrendsReport(?int $branchId = null, int $days = 30): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        $query = StockTransferQuery::whereBetween('created_at', [$startDate, $endDate]);

        if ($branchId) {
            $query->whereHas('stockTransfer', function ($q) use ($branchId) {
                $q->where('to_branch_id', $branchId);
            });
        }

        $queries = $query->get();

        // Group by day
        $dailyStats = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dayQueries = $queries->filter(function ($query) use ($date) {
                return $query->created_at->isSameDay($date);
            });

            $dailyStats[$date->format('Y-m-d')] = [
                'date' => $date->format('Y-m-d'),
                'total_queries' => $dayQueries->count(),
                'critical_queries' => $dayQueries->where('priority', 'critical')->count(),
                'financial_impact' => $dayQueries->sum('financial_impact'),
            ];
        }

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'days' => $days,
            ],
            'daily_stats' => array_values($dailyStats),
            'summary' => [
                'total_queries' => $queries->count(),
                'average_per_day' => round($queries->count() / $days, 2),
                'total_financial_impact' => $queries->sum('financial_impact'),
                'most_common_type' => $queries->groupBy('query_type')->sortByDesc(function ($group) {
                    return $group->count();
                })->keys()->first(),
            ],
        ];
    }
}