<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Branch;
use App\Models\Batch;
use App\Models\LossTracking;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryDashboardController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Get comprehensive inventory dashboard data.
     */
    public function getDashboardData(Request $request): JsonResponse
    {
        $branchId = $request->query('branch_id');
        $dateRange = $request->query('date_range', '7'); // days

        $dashboardData = [
            'overview' => $this->getInventoryOverview($branchId),
            'stock_alerts' => $this->inventoryService->getStockAlerts($branchId),
            'loss_summary' => $this->getLossSummary($branchId, $dateRange),
            'recent_movements' => $this->getRecentStockMovements($branchId, 10),
            'expiring_batches' => $this->getExpiringBatches($branchId, 7),
            'top_selling_products' => $this->getTopSellingProducts($branchId, $dateRange),
            'category_performance' => $this->getCategoryPerformance($branchId, $dateRange),
            'inventory_trends' => $this->getInventoryTrends($branchId, $dateRange),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $dashboardData
        ]);
    }

    /**
     * Get inventory overview statistics.
     */
    public function getInventoryOverview($branchId = null): array
    {
        $query = Product::with(['branches' => function ($q) use ($branchId) {
            $q->withPivot(['current_stock', 'selling_price', 'is_available_online']);
            if ($branchId) {
                $q->where('branches.id', $branchId);
            }
        }])->active();

        $products = $query->get();

        $totalProducts = $products->count();
        $totalStockValue = 0;
        $lowStockCount = 0;
        $outOfStockCount = 0;
        $availableOnlineCount = 0;

        foreach ($products as $product) {
            foreach ($product->branches as $branch) {
                $currentStock = $branch->pivot->current_stock;
                $sellingPrice = $branch->pivot->selling_price;
                $isAvailableOnline = $branch->pivot->is_available_online;

                $totalStockValue += $currentStock * $sellingPrice;

                if ($currentStock <= $product->stock_threshold) {
                    if ($currentStock == 0) {
                        $outOfStockCount++;
                    } else {
                        $lowStockCount++;
                    }
                }

                if ($isAvailableOnline && $currentStock > $product->stock_threshold) {
                    $availableOnlineCount++;
                }
            }
        }

        return [
            'total_products' => $totalProducts,
            'total_stock_value' => round($totalStockValue, 2),
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'available_online_count' => $availableOnlineCount,
            'total_branches' => $branchId ? 1 : Branch::active()->count(),
        ];
    }

    /**
     * Get loss summary for dashboard.
     */
    public function getLossSummary($branchId = null, $days = 7): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $query = LossTracking::with(['product', 'branch'])
                            ->where('created_at', '>=', $startDate);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $losses = $query->get();

        $summary = [
            'total_losses' => $losses->count(),
            'total_financial_loss' => $losses->sum('financial_loss'),
            'by_type' => $losses->groupBy('loss_type')->map(function ($typeLosses, $type) {
                return [
                    'type' => $type,
                    'count' => $typeLosses->count(),
                    'quantity_lost' => $typeLosses->sum('quantity_lost'),
                    'financial_loss' => $typeLosses->sum('financial_loss'),
                ];
            })->values(),
            'top_loss_products' => $losses->groupBy('product_id')->map(function ($productLosses) {
                $product = $productLosses->first()->product;
                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'total_quantity_lost' => $productLosses->sum('quantity_lost'),
                    'total_financial_loss' => $productLosses->sum('financial_loss'),
                    'loss_count' => $productLosses->count(),
                ];
            })->sortByDesc('total_financial_loss')->take(5)->values(),
        ];

        return $summary;
    }

    /**
     * Get recent stock movements.
     */
    public function getRecentStockMovements($branchId = null, $limit = 10): array
    {
        $query = StockMovement::with(['product', 'branch', 'user'])
                             ->orderBy('created_at', 'desc')
                             ->limit($limit);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $movements = $query->get();

        return $movements->map(function ($movement) {
            return [
                'id' => $movement->id,
                'product_name' => $movement->product->name,
                'branch_name' => $movement->branch->name,
                'type' => $movement->type,
                'quantity' => $movement->quantity,
                'unit_price' => $movement->unit_price,
                'total_value' => abs($movement->quantity * $movement->unit_price),
                'user_name' => $movement->user->name ?? 'System',
                'created_at' => $movement->created_at,
                'notes' => $movement->notes,
            ];
        })->toArray();
    }

    /**
     * Get batches expiring soon.
     */
    public function getExpiringBatches($branchId = null, $days = 7): array
    {
        $query = Batch::with(['product', 'branch'])
                     ->where('expiry_date', '<=', Carbon::now()->addDays($days))
                     ->where('expiry_date', '>', Carbon::now())
                     ->where('current_quantity', '>', 0)
                     ->where('status', 'active')
                     ->orderBy('expiry_date', 'asc');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $batches = $query->get();

        return $batches->map(function ($batch) {
            return [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'product_name' => $batch->product->name,
                'branch_name' => $batch->branch->name,
                'current_quantity' => $batch->current_quantity,
                'expiry_date' => $batch->expiry_date,
                'days_to_expire' => Carbon::now()->diffInDays($batch->expiry_date),
                'financial_impact' => $batch->current_quantity * $batch->product->selling_price,
                'urgency' => $this->getExpiryUrgency($batch->expiry_date),
            ];
        })->toArray();
    }

    /**
     * Get top selling products.
     */
    public function getTopSellingProducts($branchId = null, $days = 7): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $query = DB::table('order_items')
                  ->join('orders', 'order_items.order_id', '=', 'orders.id')
                  ->join('products', 'order_items.product_id', '=', 'products.id')
                  ->where('orders.created_at', '>=', $startDate)
                  ->where('orders.status', 'completed')
                  ->select([
                      'products.id as product_id',
                      'products.name as product_name',
                      'products.category',
                      DB::raw('SUM(order_items.quantity) as total_quantity_sold'),
                      DB::raw('SUM(order_items.total_price) as total_revenue'),
                      DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                  ])
                  ->groupBy('products.id', 'products.name', 'products.category');

        if ($branchId) {
            $query->where('orders.branch_id', $branchId);
        }

        return $query->orderByDesc('total_revenue')
                    ->limit(10)
                    ->get()
                    ->toArray();
    }

    /**
     * Get category performance.
     */
    public function getCategoryPerformance($branchId = null, $days = 7): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $query = DB::table('order_items')
                  ->join('orders', 'order_items.order_id', '=', 'orders.id')
                  ->join('products', 'order_items.product_id', '=', 'products.id')
                  ->where('orders.created_at', '>=', $startDate)
                  ->where('orders.status', 'completed')
                  ->select([
                      'products.category',
                      DB::raw('SUM(order_items.quantity) as total_quantity_sold'),
                      DB::raw('SUM(order_items.total_price) as total_revenue'),
                      DB::raw('COUNT(DISTINCT order_items.product_id) as unique_products'),
                      DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                  ])
                  ->groupBy('products.category');

        if ($branchId) {
            $query->where('orders.branch_id', $branchId);
        }

        return $query->orderByDesc('total_revenue')
                    ->get()
                    ->toArray();
    }

    /**
     * Get inventory trends over time.
     */
    public function getInventoryTrends($branchId = null, $days = 7): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        // Daily stock movements
        $movementQuery = StockMovement::selectRaw('
            DATE(created_at) as date,
            SUM(CASE WHEN quantity > 0 THEN quantity ELSE 0 END) as stock_in,
            SUM(CASE WHEN quantity < 0 THEN ABS(quantity) ELSE 0 END) as stock_out,
            SUM(quantity * unit_price) as value_change
        ')
        ->where('created_at', '>=', $startDate)
        ->groupBy('date')
        ->orderBy('date');

        if ($branchId) {
            $movementQuery->where('branch_id', $branchId);
        }

        $movements = $movementQuery->get();

        // Daily losses
        $lossQuery = LossTracking::selectRaw('
            DATE(created_at) as date,
            SUM(quantity_lost) as total_quantity_lost,
            SUM(financial_loss) as total_financial_loss
        ')
        ->where('created_at', '>=', $startDate)
        ->groupBy('date')
        ->orderBy('date');

        if ($branchId) {
            $lossQuery->where('branch_id', $branchId);
        }

        $losses = $lossQuery->get()->keyBy('date');

        return $movements->map(function ($movement) use ($losses) {
            $date = $movement->date;
            $loss = $losses->get($date);
            
            return [
                'date' => $date,
                'stock_in' => $movement->stock_in,
                'stock_out' => $movement->stock_out,
                'net_movement' => $movement->stock_in - $movement->stock_out,
                'value_change' => $movement->value_change,
                'quantity_lost' => $loss ? $loss->total_quantity_lost : 0,
                'financial_loss' => $loss ? $loss->total_financial_loss : 0,
            ];
        })->toArray();
    }

    /**
     * Get product-wise profit analysis.
     */
    public function getProfitAnalysis(Request $request): JsonResponse
    {
        $branchId = $request->query('branch_id');
        $category = $request->query('category');
        
        $query = Product::with(['branches' => function ($q) use ($branchId) {
            $q->withPivot(['current_stock', 'selling_price']);
            if ($branchId) {
                $q->where('branches.id', $branchId);
            }
        }, 'expenseAllocations'])->active();

        if ($category) {
            $query->where('category', $category);
        }

        $products = $query->get();

        $analysis = $products->map(function ($product) {
            $profitData = [];
            
            foreach ($product->branches as $branch) {
                $currentStock = $branch->pivot->current_stock;
                $sellingPrice = $branch->pivot->selling_price;
                $costPerUnit = $product->getCostPerUnit($branch->id);
                $profitMargin = $product->getProfitMargin($branch->id);
                $profitPercentage = $product->getProfitPercentage($branch->id);

                $profitData[] = [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'current_stock' => $currentStock,
                    'purchase_price' => $product->purchase_price,
                    'cost_per_unit' => $costPerUnit,
                    'selling_price' => $sellingPrice,
                    'profit_margin' => $profitMargin,
                    'profit_percentage' => $profitPercentage,
                    'potential_profit' => $currentStock * $profitMargin,
                ];
            }

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'category' => $product->category,
                'branches' => $profitData,
                'average_profit_percentage' => collect($profitData)->avg('profit_percentage'),
                'total_potential_profit' => collect($profitData)->sum('potential_profit'),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'products' => $analysis,
                'summary' => [
                    'total_products' => $analysis->count(),
                    'total_potential_profit' => $analysis->sum('total_potential_profit'),
                    'average_profit_percentage' => $analysis->avg('average_profit_percentage'),
                ]
            ]
        ]);
    }

    /**
     * Get inventory forecasting data.
     */
    public function getInventoryForecast(Request $request): JsonResponse
    {
        $branchId = $request->query('branch_id');
        $productId = $request->query('product_id');
        $days = $request->query('days', 30);

        $forecast = $this->calculateInventoryForecast($productId, $branchId, $days);

        return response()->json([
            'status' => 'success',
            'data' => $forecast
        ]);
    }

    /**
     * Calculate inventory forecast based on historical data.
     */
    private function calculateInventoryForecast($productId, $branchId, $days): array
    {
        $product = Product::find($productId);
        if (!$product) {
            return ['error' => 'Product not found'];
        }

        // Get historical sales data (last 30 days)
        $historicalSales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.product_id', $productId)
            ->where('orders.status', 'completed')
            ->where('orders.created_at', '>=', Carbon::now()->subDays(30))
            ->when($branchId, function ($query) use ($branchId) {
                return $query->where('orders.branch_id', $branchId);
            })
            ->selectRaw('DATE(orders.created_at) as date, SUM(order_items.quantity) as daily_sales')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $averageDailySales = $historicalSales->avg('daily_sales') ?: 0;
        $currentStock = $product->getCurrentStock($branchId);

        // Simple linear forecast
        $forecast = [];
        $projectedStock = $currentStock;
        
        for ($i = 1; $i <= $days; $i++) {
            $projectedStock -= $averageDailySales;
            $date = Carbon::now()->addDays($i)->format('Y-m-d');
            
            $forecast[] = [
                'date' => $date,
                'projected_stock' => max(0, $projectedStock),
                'days_until_stockout' => $projectedStock <= 0 ? $i : null,
                'reorder_needed' => $projectedStock <= $product->stock_threshold,
            ];
        }

        return [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'current_stock' => $currentStock,
                'threshold' => $product->stock_threshold,
            ],
            'historical_data' => [
                'average_daily_sales' => round($averageDailySales, 2),
                'total_historical_days' => $historicalSales->count(),
            ],
            'forecast' => $forecast,
            'recommendations' => $this->getReorderRecommendations($product, $averageDailySales, $currentStock),
        ];
    }

    /**
     * Get reorder recommendations.
     */
    private function getReorderRecommendations($product, $averageDailySales, $currentStock): array
    {
        $daysUntilStockout = $averageDailySales > 0 ? $currentStock / $averageDailySales : 999;
        $recommendedReorderQuantity = $averageDailySales * 15; // 15 days buffer
        
        return [
            'days_until_stockout' => round($daysUntilStockout, 1),
            'recommended_reorder_quantity' => round($recommendedReorderQuantity, 2),
            'urgency_level' => $this->getUrgencyLevel($daysUntilStockout),
            'suggested_action' => $this->getSuggestedAction($daysUntilStockout),
        ];
    }

    /**
     * Get urgency level based on days until stockout.
     */
    private function getUrgencyLevel($daysUntilStockout): string
    {
        if ($daysUntilStockout <= 3) return 'critical';
        if ($daysUntilStockout <= 7) return 'high';
        if ($daysUntilStockout <= 14) return 'medium';
        return 'low';
    }

    /**
     * Get suggested action based on days until stockout.
     */
    private function getSuggestedAction($daysUntilStockout): string
    {
        if ($daysUntilStockout <= 3) return 'Place emergency order immediately';
        if ($daysUntilStockout <= 7) return 'Place order within 24 hours';
        if ($daysUntilStockout <= 14) return 'Schedule order for this week';
        return 'Monitor and plan for future order';
    }

    /**
     * Get expiry urgency level.
     */
    private function getExpiryUrgency($expiryDate): string
    {
        $daysToExpire = Carbon::now()->diffInDays($expiryDate);
        
        if ($daysToExpire <= 1) return 'critical';
        if ($daysToExpire <= 3) return 'high';
        if ($daysToExpire <= 7) return 'medium';
        return 'low';
    }
}