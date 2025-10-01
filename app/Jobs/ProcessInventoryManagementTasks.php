<?php

namespace App\Jobs;

use App\Models\AutoPurchaseOrder;
use App\Models\BatchPriceAdjustment;
use App\Models\ExpiryAlert;
use App\Models\ReorderAlert;
use App\Services\SmartReorderingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessInventoryManagementTasks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting Inventory Management Tasks');

        try {
            // 1. Generate expiry alerts
            $expiryAlerts = ExpiryAlert::generateAlerts();
            Log::info("Generated {$expiryAlerts} expiry alerts");

            // 2. Apply automatic near-expiry discounts
            $discounts = BatchPriceAdjustment::applyAutomaticDiscounts();
            Log::info("Applied {$discounts} near-expiry discounts");

            // 3. Deactivate expired price adjustments
            $deactivated = BatchPriceAdjustment::deactivateExpired();
            Log::info("Deactivated {$deactivated} expired price adjustments");

            // 4. Run smart reordering workflow
            $reorderingService = new SmartReorderingService();
            $results = $reorderingService->runReorderingWorkflow();
            Log::info('Smart reordering workflow completed', $results);

            // 5. Generate reorder alerts
            $reorderAlerts = ReorderAlert::generateAlerts();
            Log::info("Generated {$reorderAlerts} reorder alerts");

            // 6. Generate auto purchase orders
            $autoPOs = AutoPurchaseOrder::generateAutoPurchaseOrders();
            Log::info("Generated {$autoPOs} auto purchase orders");

            Log::info('Inventory Management Tasks completed successfully');
        } catch (\Exception $e) {
            Log::error('Error in Inventory Management Tasks: ' . $e->getMessage());
            throw $e;
        }
    }
}
