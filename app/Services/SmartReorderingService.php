<?php

namespace App\Services;

use App\Models\AutoPurchaseOrder;
use App\Models\DemandForecast;
use App\Models\Product;
use App\Models\Branch;
use App\Models\ReorderAlert;
use App\Models\ReorderPointConfig;
use App\Models\SeasonalAdjustment;
use App\Models\VendorLeadTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SmartReorderingService
{
    /**
     * Initialize reorder point configs for all products and branches.
     */
    public function initializeReorderConfigs(): int
    {
        $count = 0;
        $products = Product::active()->get();
        $branches = Branch::all();

        foreach ($products as $product) {
            foreach ($branches as $branch) {
                // Check if config already exists
                $existing = ReorderPointConfig::where('product_id', $product->id)
                                             ->where('branch_id', $branch->id)
                                             ->exists();

                if ($existing) {
                    continue;
                }

                // Get vendor lead time
                $vendors = $product->vendors()->wherePivot('is_primary_supplier', true)->get();
                $leadTime = 2; // Default

                if ($vendors->isNotEmpty()) {
                    $leadTime = VendorLeadTime::getAverageLeadTime($vendors->first()->id, $product->id);
                }

                // Create config
                $config = ReorderPointConfig::create([
                    'product_id' => $product->id,
                    'branch_id' => $branch->id,
                    'average_daily_sales' => 0,
                    'lead_time_days' => $leadTime,
                    'safety_stock_days' => 2,
                    'reorder_point' => 0,
                    'seasonal_factor' => 1.0,
                    'calculation_period_days' => 30,
                    'auto_reorder_enabled' => $product->enable_auto_reorder ?? true,
                ]);

                // Calculate initial values
                $config->recalculate();
                $count++;
            }
        }

        return $count;
    }

    /**
     * Recalculate all reorder points.
     */
    public function recalculateAllReorderPoints(): int
    {
        $count = 0;
        $configs = ReorderPointConfig::all();

        foreach ($configs as $config) {
            try {
                // Update seasonal factor
                $seasonalFactor = SeasonalAdjustment::getSeasonalFactor(
                    $config->product_id,
                    $config->product->category
                );
                $config->seasonal_factor = $seasonalFactor;

                // Update lead time from vendor data
                $vendors = $config->product->vendors()->wherePivot('is_primary_supplier', true)->get();
                if ($vendors->isNotEmpty()) {
                    $leadTime = VendorLeadTime::getAverageLeadTime($vendors->first()->id, $config->product_id);
                    $config->lead_time_days = $leadTime;
                }

                // Recalculate
                $config->recalculate();
                $count++;
            } catch (\Exception $e) {
                Log::error("Error recalculating reorder point for product {$config->product_id}: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Generate demand forecasts for the next N days.
     */
    public function generateDemandForecasts(int $days = 7): int
    {
        $count = 0;
        $products = Product::active()->get();
        $branches = Branch::all();

        foreach ($products as $product) {
            foreach ($branches as $branch) {
                for ($i = 0; $i < $days; $i++) {
                    $forecastDate = now()->addDays($i)->toDateString();
                    
                    try {
                        DemandForecast::generateForecast(
                            $product->id,
                            $branch->id,
                            $forecastDate,
                            'weighted_average'
                        );
                        $count++;
                    } catch (\Exception $e) {
                        Log::error("Error generating forecast for product {$product->id}: " . $e->getMessage());
                    }
                }
            }
        }

        return $count;
    }

    /**
     * Update actual demand for yesterday's forecasts and calculate accuracy.
     */
    public function updateForecastAccuracy(): int
    {
        $count = 0;
        $yesterday = now()->subDay()->toDateString();

        $forecasts = DemandForecast::where('forecast_date', $yesterday)
                                   ->whereNull('actual_demand')
                                   ->get();

        foreach ($forecasts as $forecast) {
            try {
                // Get actual sales for yesterday
                $actualDemand = \App\Models\OrderItem::whereHas('order', function ($query) use ($forecast, $yesterday) {
                    $query->where('branch_id', $forecast->branch_id)
                          ->where('status', 'completed')
                          ->whereDate('created_at', $yesterday);
                })
                ->where('product_id', $forecast->product_id)
                ->sum('quantity');

                $forecast->updateActualDemand($actualDemand);
                $count++;
            } catch (\Exception $e) {
                Log::error("Error updating forecast accuracy: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Generate reorder alerts for products below reorder point.
     */
    public function generateReorderAlerts(): int
    {
        return ReorderAlert::generateAlerts();
    }

    /**
     * Generate auto purchase orders for products below reorder point.
     */
    public function generateAutoPurchaseOrders(): int
    {
        return AutoPurchaseOrder::generateAutoPurchaseOrders();
    }

    /**
     * Run full reordering workflow.
     */
    public function runReorderingWorkflow(): array
    {
        $results = [
            'configs_recalculated' => 0,
            'forecasts_generated' => 0,
            'forecasts_updated' => 0,
            'alerts_generated' => 0,
            'auto_pos_generated' => 0,
        ];

        try {
            DB::beginTransaction();

            // Step 1: Recalculate reorder points
            $results['configs_recalculated'] = $this->recalculateAllReorderPoints();
            Log::info("Recalculated {$results['configs_recalculated']} reorder point configs");

            // Step 2: Update forecast accuracy from yesterday
            $results['forecasts_updated'] = $this->updateForecastAccuracy();
            Log::info("Updated {$results['forecasts_updated']} forecast accuracies");

            // Step 3: Generate new forecasts
            $results['forecasts_generated'] = $this->generateDemandForecasts(7);
            Log::info("Generated {$results['forecasts_generated']} demand forecasts");

            // Step 4: Generate alerts
            $results['alerts_generated'] = $this->generateReorderAlerts();
            Log::info("Generated {$results['alerts_generated']} reorder alerts");

            // Step 5: Generate auto purchase orders
            $results['auto_pos_generated'] = $this->generateAutoPurchaseOrders();
            Log::info("Generated {$results['auto_pos_generated']} auto purchase orders");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error in reordering workflow: " . $e->getMessage());
            throw $e;
        }

        return $results;
    }

    /**
     * Get reordering dashboard statistics.
     */
    public function getDashboardStats(): array
    {
        return [
            'total_configs' => ReorderPointConfig::count(),
            'active_configs' => ReorderPointConfig::where('auto_reorder_enabled', true)->count(),
            'products_below_reorder_point' => $this->getProductsBelowReorderPoint()->count(),
            'pending_auto_pos' => AutoPurchaseOrder::pending()->count(),
            'unresolved_alerts' => ReorderAlert::unresolved()->count(),
            'critical_alerts' => ReorderAlert::unresolved()->bySeverity('critical')->count(),
            'high_alerts' => ReorderAlert::unresolved()->bySeverity('high')->count(),
            'forecast_accuracy' => $this->getAverageForecastAccuracy(),
            'total_forecasts' => DemandForecast::whereNotNull('actual_demand')->count(),
        ];
    }

    /**
     * Get products below reorder point.
     */
    public function getProductsBelowReorderPoint()
    {
        return ReorderPointConfig::where('auto_reorder_enabled', true)
            ->with(['product', 'branch'])
            ->get()
            ->filter(function ($config) {
                return $config->shouldReorder();
            });
    }

    /**
     * Get average forecast accuracy across all products.
     */
    private function getAverageForecastAccuracy(): float
    {
        return DemandForecast::whereNotNull('forecast_accuracy')
                            ->avg('forecast_accuracy') ?? 0;
    }

    /**
     * Get reorder recommendations for a specific product.
     */
    public function getReorderRecommendations(int $productId, int $branchId): array
    {
        $config = ReorderPointConfig::where('product_id', $productId)
                                    ->where('branch_id', $branchId)
                                    ->first();

        if (!$config) {
            return [
                'should_reorder' => false,
                'message' => 'Reorder point not configured',
            ];
        }

        $currentStock = $config->product->getCurrentStock($branchId);
        $shouldReorder = $config->shouldReorder();

        $recommendations = [
            'should_reorder' => $shouldReorder,
            'current_stock' => $currentStock,
            'reorder_point' => $config->reorder_point,
            'recommended_quantity' => $shouldReorder ? $config->getRecommendedOrderQuantity() : 0,
            'average_daily_sales' => $config->average_daily_sales,
            'lead_time_days' => $config->lead_time_days,
            'safety_stock_days' => $config->safety_stock_days,
            'seasonal_factor' => $config->seasonal_factor,
            'days_until_stockout' => $config->average_daily_sales > 0 
                ? floor($currentStock / $config->average_daily_sales) 
                : null,
        ];

        // Get demand forecast for next 7 days
        $forecasts = DemandForecast::where('product_id', $productId)
                                  ->where('branch_id', $branchId)
                                  ->where('forecast_date', '>=', now()->toDateString())
                                  ->orderBy('forecast_date')
                                  ->limit(7)
                                  ->get();

        $recommendations['demand_forecasts'] = $forecasts;
        $recommendations['next_7_days_demand'] = $forecasts->sum('forecasted_demand');

        return $recommendations;
    }
}
