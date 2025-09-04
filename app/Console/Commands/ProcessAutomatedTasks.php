<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InventoryService;
use App\Models\Product;
use App\Models\Branch;

class ProcessAutomatedTasks extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'system:process-automated-tasks 
                           {--expired-batches : Process expired batches}
                           {--stock-alerts : Check and update stock alerts}
                           {--online-availability : Update online availability based on thresholds}';

    /**
     * The console command description.
     */
    protected $description = 'Process automated tasks for inventory management';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $inventoryService = new InventoryService();

        $this->info('Starting automated tasks processing...');

        // Process expired batches
        if ($this->option('expired-batches') || !$this->hasOptions()) {
            $this->info('Processing expired batches...');
            $processedCount = $inventoryService->processExpiredBatches();
            $this->info("Processed {$processedCount} expired batches");
        }

        // Update online availability based on stock thresholds
        if ($this->option('online-availability') || !$this->hasOptions()) {
            $this->info('Updating online availability based on stock thresholds...');
            $this->updateOnlineAvailability($inventoryService);
        }

        // Generate stock alerts
        if ($this->option('stock-alerts') || !$this->hasOptions()) {
            $this->info('Checking stock alerts...');
            $alerts = $inventoryService->getStockAlerts();
            $this->displayStockAlerts($alerts);
        }

        $this->info('Automated tasks processing completed!');
    }

    /**
     * Check if any options are provided.
     */
    private function hasOptions(): bool
    {
        return $this->option('expired-batches') || 
               $this->option('stock-alerts') || 
               $this->option('online-availability');
    }

    /**
     * Update online availability for all products.
     */
    private function updateOnlineAvailability(InventoryService $inventoryService): void
    {
        $products = Product::with('branches')->active()->get();
        $updatedCount = 0;

        foreach ($products as $product) {
            foreach ($product->branches as $branch) {
                $currentStock = $branch->pivot->current_stock;
                $wasOnline = $branch->pivot->is_available_online;
                
                $inventoryService->checkAndUpdateOnlineAvailability(
                    $product, 
                    $branch->id, 
                    $currentStock
                );

                // Check if status changed
                $branch->refresh();
                $isNowOnline = $branch->pivot->is_available_online;
                
                if ($wasOnline !== $isNowOnline) {
                    $status = $isNowOnline ? 'ONLINE' : 'OFFLINE';
                    $this->line("  - {$product->name} at {$branch->name}: {$status} (Stock: {$currentStock})");
                    $updatedCount++;
                }
            }
        }

        $this->info("Updated online availability for {$updatedCount} product-branch combinations");
    }

    /**
     * Display stock alerts in a formatted way.
     */
    private function displayStockAlerts(array $alerts): void
    {
        // Low stock alerts
        if (!empty($alerts['low_stock'])) {
            $this->warn('LOW STOCK ALERTS:');
            foreach ($alerts['low_stock'] as $alert) {
                $this->line("  - {$alert['product_name']} at {$alert['branch_name']}: {$alert['current_stock']} (Threshold: {$alert['threshold']})");
            }
        }

        // Out of stock alerts
        if (!empty($alerts['out_of_stock'])) {
            $this->error('OUT OF STOCK ALERTS:');
            foreach ($alerts['out_of_stock'] as $alert) {
                $this->line("  - {$alert['product_name']} at {$alert['branch_name']}: OUT OF STOCK");
            }
        }

        // Expiring soon alerts
        if (!empty($alerts['expiring_soon'])) {
            $this->warn('EXPIRING SOON ALERTS:');
            foreach ($alerts['expiring_soon'] as $alert) {
                $this->line("  - Batch {$alert['batch_number']} ({$alert['product_name']}): Expires in {$alert['days_to_expire']} days");
            }
        }

        if (empty($alerts['low_stock']) && empty($alerts['out_of_stock']) && empty($alerts['expiring_soon'])) {
            $this->info('No stock alerts found - all products are adequately stocked!');
        }
    }
}