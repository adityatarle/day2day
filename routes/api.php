<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\Auth\OutletAuthController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Api\SystemMonitoringController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\WholesaleController;
use App\Http\Controllers\LossTrackingController;
use App\Http\Controllers\DeliveryAdjustmentController;
use App\Http\Controllers\InventoryDashboardController;
use App\Http\Controllers\Admin\StockTransferController;
use App\Http\Controllers\Branch\StockReceiptController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\BranchApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\CustomerOrderApiController;
use App\Http\Controllers\Api\StoreLocationApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::post('/v1/login', [AuthController::class, 'login']);

// Customer-facing public APIs
Route::get('/stores', [StoreLocationApiController::class, 'index']);
Route::get('/stores/nearest', [StoreLocationApiController::class, 'nearest']);
Route::get('/stores/{branch}', [StoreLocationApiController::class, 'show']);
Route::post('/customer/orders', [CustomerOrderApiController::class, 'store']);
Route::get('/customer/orders', [CustomerOrderApiController::class, 'index']);
Route::get('/customer/orders/{order}', [CustomerOrderApiController::class, 'show']);

// Outlet-specific authentication
Route::post('/outlet/login', [OutletAuthController::class, 'outletLogin']);
Route::get('/outlet/{outletCode}/info', [OutletAuthController::class, 'getOutletInfo']);

// Customer/Dealer authentication (mobile number login)
Route::post('/customer/login', [CustomerAuthController::class, 'login']);
Route::post('/customer/register', [CustomerAuthController::class, 'register']);
Route::post('/customer/password-reset/request', [CustomerAuthController::class, 'requestPasswordReset']);
Route::post('/customer/password-reset/verify', [CustomerAuthController::class, 'resetPassword']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    
    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // Outlet authentication routes
    Route::post('/outlet/logout', [OutletAuthController::class, 'outletLogout']);
    Route::post('/outlet/change-password', [OutletAuthController::class, 'changePassword']);
    
    // Customer/Dealer authentication routes
    Route::post('/customer/logout', [CustomerAuthController::class, 'logout']);
    Route::get('/customer/profile', [CustomerAuthController::class, 'profile']);
    Route::post('/customer/change-password', [CustomerAuthController::class, 'changePassword']);
    
    // User management routes (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::get('/roles', [UserController::class, 'getRoles']);
        Route::get('/branches', [UserController::class, 'getBranches']);
        Route::get('/branches/all', [BranchApiController::class, 'index']);
        
        // Expense categories
        Route::get('/expense-categories', [ExpenseController::class, 'getCategories']);
        Route::post('/expense-categories', [ExpenseController::class, 'storeCategory']);
        Route::put('/expense-categories/{category}', [ExpenseController::class, 'updateCategory']);
        Route::delete('/expense-categories/{category}', [ExpenseController::class, 'destroyCategory']);
    });
    
    // Branch Manager routes
    Route::middleware('role:admin,branch_manager')->group(function () {
        // Product management
        Route::apiResource('products', ProductController::class);
        Route::post('/products/{product}/branch-pricing', [ProductController::class, 'updateBranchPricing']);
        Route::get('/products/{product}/stock-info', [ProductController::class, 'getStockInfo']);
        Route::get('/products/category/{category}', [ProductController::class, 'getByCategory']);
        Route::get('/products/search', [ProductController::class, 'search']);
        
        // Inventory management
        Route::get('/inventory', [InventoryController::class, 'index']);
        Route::post('/inventory/add-stock', [InventoryController::class, 'addStock']);
        Route::post('/inventory/record-loss', [InventoryController::class, 'recordLoss']);
        Route::get('/inventory/{product}/batches', [InventoryController::class, 'getBatches']);
        Route::put('/inventory/batches/{batch}/status', [InventoryController::class, 'updateBatchStatus']);
        Route::get('/inventory/{product}/stock-movements', [InventoryController::class, 'getStockMovements']);
        Route::get('/inventory/loss-summary', [InventoryController::class, 'getLossSummary']);
        Route::get('/inventory/low-stock-alerts', [InventoryController::class, 'getLowStockAlerts']);
        Route::get('/inventory/valuation', [InventoryController::class, 'getInventoryValuation']);
        
        // Customer management
    });
    
    // Cashier routes
    Route::middleware('role:admin,branch_manager,cashier')->group(function () {
        // Order management
        Route::apiResource('orders', OrderController::class);
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
        Route::get('/orders/{order}/invoice', [OrderController::class, 'generateInvoice']);
        Route::get('/orders/statistics', [OrderController::class, 'getStatistics']);
        
        // Customer management
        Route::get('/customers', [CustomerApiController::class, 'index']);
        Route::get('/customers/search', [CustomerApiController::class, 'search']);
        Route::get('/customers/{customer}', [CustomerApiController::class, 'show']);
        Route::post('/customers', [CustomerApiController::class, 'store']);
        Route::put('/customers/{customer}', [CustomerApiController::class, 'update']);
        Route::get('/customers/{customer}/purchase-history', [CustomerApiController::class, 'purchaseHistory']);
        
        // Dashboard
        Route::get('/dashboard', [DashboardApiController::class, 'index']);
        Route::get('/dashboard/sales-chart', [DashboardApiController::class, 'salesChart']);
        
        // Branch information
        Route::get('/branch/current', [BranchApiController::class, 'current']);
        Route::get('/branch/statistics', [BranchApiController::class, 'statistics']);
    });
    
    // Common routes for all authenticated users
    // (Dashboard endpoints removed; use monitoring endpoints instead)
    
    // Expense management (for branch managers and admins)
    Route::middleware('role:admin,branch_manager')->group(function () {
        Route::apiResource('expenses', ExpenseController::class);
        Route::put('/expenses/{expense}/approve', [ExpenseController::class, 'approve']);
        Route::put('/expenses/{expense}/reject', [ExpenseController::class, 'reject']);
        Route::put('/expenses/{expense}/mark-paid', [ExpenseController::class, 'markAsPaid']);
        Route::get('/expenses/allocation/report', [ExpenseController::class, 'getAllocationReport']);
        Route::get('/expenses/cost/analysis', [ExpenseController::class, 'getCostAnalysis']);
        Route::get('/expenses/summary', [ExpenseController::class, 'getExpenseSummary']);
    });

    // Enhanced Inventory Management
    Route::get('/inventory/alerts', [InventoryController::class, 'getStockAlerts']);
    Route::post('/inventory/weight-loss', [InventoryController::class, 'recordWeightLoss']);
    Route::post('/inventory/water-loss', [InventoryController::class, 'recordWaterLoss']);
    Route::post('/inventory/wastage-loss', [InventoryController::class, 'recordWastageLoss']);
    Route::post('/inventory/transfer', [InventoryController::class, 'transferStock']);
    Route::put('/inventory/thresholds/bulk', [InventoryController::class, 'bulkUpdateThresholds']);
    Route::get('/inventory/valuation-with-costs', [InventoryController::class, 'getValuationWithCosts']);
    Route::post('/inventory/process-expired-batches', [InventoryController::class, 'processExpiredBatches']);

    // Inventory Dashboard & Analytics
    Route::get('/inventory/dashboard', [InventoryDashboardController::class, 'getDashboardData']);
    Route::get('/inventory/profit-analysis', [InventoryDashboardController::class, 'getProfitAnalysis']);
    Route::get('/inventory/forecast', [InventoryDashboardController::class, 'getInventoryForecast']);

    // Loss Tracking Management
    Route::apiResource('loss-tracking', LossTrackingController::class);
    Route::get('/loss-tracking/analytics', [LossTrackingController::class, 'getLossAnalytics']);
    Route::get('/loss-tracking/trends', [LossTrackingController::class, 'getLossTrends']);
    Route::get('/loss-tracking/critical-alerts', [LossTrackingController::class, 'getCriticalLossAlerts']);
    Route::post('/loss-tracking/bulk', [LossTrackingController::class, 'bulkRecordLosses']);
    Route::get('/loss-tracking/prevention-recommendations', [LossTrackingController::class, 'getLossPreventionRecommendations']);
    Route::get('/loss-tracking/export', [LossTrackingController::class, 'exportLossData']);

    // Enhanced Product Management
    Route::get('/products/categories', [ProductController::class, 'getCategories']);
    Route::put('/products/{product}/branch-pricing', [ProductController::class, 'updateBranchPricing']);
    // Vendor pricing - ONLY for main branch (admin role)
    Route::middleware('role:admin')->group(function () {
        Route::put('/products/{product}/vendor-pricing', [ProductController::class, 'updateVendorPricing']);
    });
    Route::put('/products/categories/bulk', [ProductController::class, 'bulkUpdateCategories']);
    Route::get('/products/category/{category}', [ProductController::class, 'getByCategory']);

    // Wholesale Management
    Route::get('/wholesale/pricing-tiers', [WholesaleController::class, 'getPricingTiers']);
    Route::post('/wholesale/pricing-tiers', [WholesaleController::class, 'createPricingTier']);
    Route::put('/wholesale/pricing-tiers/{pricingTier}', [WholesaleController::class, 'updatePricingTier']);
    Route::delete('/wholesale/pricing-tiers/{pricingTier}', [WholesaleController::class, 'deletePricingTier']);
    Route::post('/wholesale/calculate-pricing', [WholesaleController::class, 'calculateWholesalePricing']);
    Route::post('/wholesale/orders', [WholesaleController::class, 'createWholesaleOrder']);
    Route::get('/wholesale/orders', [WholesaleController::class, 'getWholesaleOrders']);
    Route::get('/wholesale/orders/{order}/invoice', [WholesaleController::class, 'generateInvoice']);
    Route::get('/wholesale/customer-analysis', [WholesaleController::class, 'getCustomerAnalysis']);
    Route::get('/wholesale/performance-metrics', [WholesaleController::class, 'getPerformanceMetrics']);

    // Billing Management
    Route::get('/billing/invoice/{order}', [BillingController::class, 'generateInvoice']);
    Route::post('/billing/quick-billing', [BillingController::class, 'quickBilling']);
    Route::post('/billing/online-payment/{order}', [BillingController::class, 'processOnlinePayment']);
    Route::post('/billing/bulk-invoice', [BillingController::class, 'generateBulkInvoice']);
    Route::post('/billing/partial-payment/{order}', [BillingController::class, 'processPartialPayment']);
    Route::get('/billing/summary', [BillingController::class, 'getBillingSummary']);
    Route::get('/billing/pending-payments', [BillingController::class, 'getPendingPayments']);

    // Delivery Boy Adjustment (for delivery boys)
    Route::middleware('role:delivery_boy')->group(function () {
        Route::get('/delivery/orders', [DeliveryAdjustmentController::class, 'getDeliveryOrders']);
        Route::put('/delivery/orders/{order}/start', [DeliveryAdjustmentController::class, 'startDelivery']);
        Route::put('/delivery/orders/{order}/process', [DeliveryAdjustmentController::class, 'processDelivery']);
        Route::put('/delivery/orders/{order}/location', [DeliveryAdjustmentController::class, 'updateLocation']);
        Route::post('/delivery/orders/{order}/quick-return', [DeliveryAdjustmentController::class, 'quickReturn']);
        Route::get('/delivery/history', [DeliveryAdjustmentController::class, 'getDeliveryHistory']);
        Route::get('/delivery/stats', [DeliveryAdjustmentController::class, 'getDeliveryStats']);
        Route::get('/delivery/optimized-route', [DeliveryAdjustmentController::class, 'getOptimizedRoute']);
    });

    // City Management (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('cities', CityController::class);
        Route::post('/cities/{city}/product-pricing', [CityController::class, 'setProductPricing']);
        Route::get('/cities/{city}/product-pricing', [CityController::class, 'getProductPricing']);
    });

    // Outlet Management
    Route::middleware('role:admin,branch_manager')->group(function () {
        Route::apiResource('outlets', OutletController::class);
        Route::get('/cities/{city}/outlets', [OutletController::class, 'getByCity']);
        Route::post('/outlets/{outlet}/staff', [OutletController::class, 'createStaff']);
        Route::get('/outlets/{outlet}/performance', [OutletController::class, 'getPerformanceMetrics']);
    });

    // POS System
    Route::middleware('role:admin,branch_manager,cashier')->group(function () {
        Route::post('/pos/start-session', [PosController::class, 'startSession']);
        Route::get('/pos/current-session', [PosController::class, 'getCurrentSession']);
        Route::post('/pos/process-sale', [PosController::class, 'processSale']);
        Route::post('/pos/close-session', [PosController::class, 'closeSession']);
        Route::get('/pos/products', [PosController::class, 'getProducts']);
        Route::get('/pos/session-history', [PosController::class, 'getSessionHistory']);
        Route::get('/pos/session-summary', [PosController::class, 'getSessionSummary']);
        Route::post('/pos/generate-upi-qr', [PosController::class, 'generateUpiQr']);
    });

    // Advanced Stock Management System
    
    // Admin Stock Transfer Management
    Route::middleware('role:admin,super_admin')->prefix('admin/stock-transfers')->group(function () {
        Route::get('/', [StockTransferController::class, 'index']);
        Route::post('/', [StockTransferController::class, 'store']);
        Route::get('/dashboard', [StockTransferController::class, 'dashboard']);
        Route::get('/reports', [StockTransferController::class, 'report']);
        Route::get('/{stockTransfer}', [StockTransferController::class, 'show']);
        Route::post('/{stockTransfer}/dispatch', [StockTransferController::class, 'dispatch']);
        Route::post('/{stockTransfer}/cancel', [StockTransferController::class, 'cancel']);
        
        // Query Management
        Route::get('/queries/all', [StockTransferController::class, 'queries']);
        Route::get('/queries/{query}', [StockTransferController::class, 'showQuery']);
        Route::post('/queries/{query}/assign', [StockTransferController::class, 'assignQuery']);
        Route::post('/queries/{query}/respond', [StockTransferController::class, 'addQueryResponse']);
        Route::post('/queries/{query}/resolve', [StockTransferController::class, 'resolveQuery']);
    });
    
    // Branch Stock Receipt Management
    Route::middleware('role:branch_manager,admin,super_admin')->prefix('branch/stock-receipts')->group(function () {
        Route::get('/', [StockReceiptController::class, 'index']);
        Route::get('/dashboard', [StockReceiptController::class, 'dashboard']);
        Route::get('/{stockTransfer}', [StockReceiptController::class, 'show']);
        Route::post('/{stockTransfer}/confirm-receipt', [StockReceiptController::class, 'confirmReceipt']);
        
        // Query Management for Branch
        Route::get('/queries/all', [StockReceiptController::class, 'queries']);
        Route::get('/queries/{query}', [StockReceiptController::class, 'showQuery']);
        Route::post('/{stockTransfer}/queries', [StockReceiptController::class, 'storeQuery']);
        Route::post('/queries/{query}/respond', [StockReceiptController::class, 'addQueryResponse']);
        Route::post('/queries/{query}/escalate', [StockReceiptController::class, 'escalateQuery']);
        
        // Stock Reconciliation
        Route::post('/{stockTransfer}/reconciliation', [StockReceiptController::class, 'storeReconciliation']);
    });
    
    // Stock Transfer APIs for Mobile/External Integration
    Route::prefix('stock-transfers')->group(function () {
        // For delivery personnel to mark as delivered
        Route::post('/{stockTransfer}/mark-delivered', function(\App\Models\StockTransfer $stockTransfer, Request $request) {
            $service = app(\App\Services\StockTransferService::class);
            $result = $service->markAsDelivered($stockTransfer, $request->all());
            return response()->json(['success' => $result]);
        });
        
        // Get transfer status for tracking
        Route::get('/{stockTransfer}/status', function(\App\Models\StockTransfer $stockTransfer) {
            return response()->json([
                'transfer_number' => $stockTransfer->transfer_number,
                'status' => $stockTransfer->status,
                'status_display' => $stockTransfer->getStatusDisplayName(),
                'dispatch_date' => $stockTransfer->dispatch_date,
                'expected_delivery' => $stockTransfer->expected_delivery,
                'delivered_date' => $stockTransfer->delivered_date,
                'confirmed_date' => $stockTransfer->confirmed_date,
                'is_overdue' => $stockTransfer->isOverdue(),
                'days_until_delivery' => $stockTransfer->getDaysUntilDelivery(),
            ]);
        });
        
        // Get transfer items for verification
        Route::get('/{stockTransfer}/items', function(\App\Models\StockTransfer $stockTransfer) {
            $items = $stockTransfer->items()->with('product:id,name,code')->get();
            return response()->json($items);
        });
    });

    // Mobile App API Routes for Branch Managers
    Route::prefix('mobile')->middleware('role:branch_manager,admin,super_admin')->group(function () {
        // Dashboard data
        Route::get('/dashboard', [\App\Http\Controllers\Api\StockTransferApiController::class, 'getDashboardData']);
        
        // Stock transfers for mobile
        Route::get('/transfers', [\App\Http\Controllers\Api\StockTransferApiController::class, 'getBranchTransfers']);
        Route::get('/transfers/{transferId}', [\App\Http\Controllers\Api\StockTransferApiController::class, 'getTransferDetails']);
        Route::post('/transfers/{transferId}/confirm-receipt', [\App\Http\Controllers\Api\StockTransferApiController::class, 'confirmReceipt']);
        
        // Queries management for mobile
        Route::get('/queries', [\App\Http\Controllers\Api\StockTransferApiController::class, 'getBranchQueries']);
        Route::get('/queries/{queryId}', [\App\Http\Controllers\Api\StockTransferApiController::class, 'getQueryDetails']);
        Route::post('/queries', [\App\Http\Controllers\Api\StockTransferApiController::class, 'createQuery']);
        Route::post('/queries/{queryId}/respond', [\App\Http\Controllers\Api\StockTransferApiController::class, 'addQueryResponse']);
        
        // Statistics for mobile dashboard
        Route::get('/statistics', [\App\Http\Controllers\Api\StockTransferApiController::class, 'getBranchStatistics']);
    });
    
    // Financial Impact and Transport Expense APIs
    Route::middleware('role:admin,super_admin,branch_manager')->prefix('stock-management')->group(function () {
        
        // Financial Impact Reports
        Route::get('/financial-impacts', function(Request $request) {
            $query = \App\Models\StockFinancialImpact::with(['branch:id,name', 'impactable']);
            
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
            
            if ($request->filled('impact_type')) {
                $query->where('impact_type', $request->impact_type);
            }
            
            if ($request->filled('date_from')) {
                $query->whereDate('impact_date', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('impact_date', '<=', $request->date_to);
            }
            
            $impacts = $query->orderBy('impact_date', 'desc')->paginate(20);
            
            return response()->json([
                'impacts' => $impacts,
                'summary' => [
                    'total_impact' => $query->sum('amount'),
                    'total_recovered' => $query->sum('recovered_amount'),
                    'net_impact' => $query->sum('amount') - $query->sum('recovered_amount'),
                ]
            ]);
        });
        
        // Transport Expenses
        Route::get('/transport-expenses', function(Request $request) {
            $query = \App\Models\TransportExpense::with(['stockTransfer:id,transfer_number,to_branch_id']);
            
            if ($request->filled('transfer_id')) {
                $query->where('stock_transfer_id', $request->transfer_id);
            }
            
            if ($request->filled('expense_type')) {
                $query->where('expense_type', $request->expense_type);
            }
            
            if ($request->filled('date_from')) {
                $query->whereDate('expense_date', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('expense_date', '<=', $request->date_to);
            }
            
            $expenses = $query->orderBy('expense_date', 'desc')->paginate(20);
            
            return response()->json([
                'expenses' => $expenses,
                'summary' => [
                    'total_amount' => $query->sum('amount'),
                    'expense_breakdown' => $query->groupBy('expense_type')
                        ->selectRaw('expense_type, SUM(amount) as total')
                        ->pluck('total', 'expense_type'),
                ]
            ]);
        });
        
        // Stock Alerts
        Route::get('/alerts', function(Request $request) {
            $query = \App\Models\StockAlert::with(['branch:id,name', 'product:id,name', 'stockTransfer:id,transfer_number']);
            
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
            
            if ($request->filled('alert_type')) {
                $query->where('alert_type', $request->alert_type);
            }
            
            if ($request->filled('severity')) {
                $query->where('severity', $request->severity);
            }
            
            if ($request->boolean('unresolved_only')) {
                $query->where('is_resolved', false);
            }
            
            $alerts = $query->orderBy('created_at', 'desc')->paginate(20);
            
            return response()->json($alerts);
        });
        
        // Mark alert as resolved
        Route::post('/alerts/{alert}/resolve', function(\App\Models\StockAlert $alert) {
            $alert->markAsResolved();
            return response()->json(['success' => true]);
        });
        
        // Stock Management Statistics
        Route::get('/statistics', function(Request $request) {
            $branchId = $request->get('branch_id');
            $startDate = $request->get('date_from');
            $endDate = $request->get('date_to');
            
            $transferService = app(\App\Services\StockTransferService::class);
            $queryService = app(\App\Services\StockQueryService::class);
            
            return response()->json([
                'transfers' => $transferService->getTransferStatistics($branchId, $startDate, $endDate),
                'queries' => $queryService->getQueryStatistics($branchId, $startDate, $endDate),
                'financial_summary' => [
                    'total_losses' => \App\Models\StockFinancialImpact::getTotalLosses($branchId, $startDate, $endDate),
                    'recoverable_amount' => \App\Models\StockFinancialImpact::getTotalRecoverableAmount($branchId, $startDate, $endDate),
                ],
                'recent_activities' => [
                    'overdue_transfers' => $transferService->getOverdueTransfers($branchId)->count(),
                    'pending_queries' => $queryService->getOverdueQueries($branchId)->count(),
                    'critical_alerts' => \App\Models\StockAlert::where('severity', 'critical')
                        ->where('is_resolved', false)
                        ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                        ->count(),
                ]
            ]);
        });
    });

    // System Monitoring and Real-time Data
    Route::prefix('monitoring')->group(function () {
        
        // Super Admin monitoring
        Route::middleware('role:super_admin')->group(function () {
            Route::get('/system-status', [SystemMonitoringController::class, 'getSystemStatus']);
            Route::get('/branch-performance', [SystemMonitoringController::class, 'getBranchPerformance']);
        });
        
        // Branch Manager monitoring
        Route::middleware('role:super_admin,branch_manager')->group(function () {
            Route::get('/branch-status', [SystemMonitoringController::class, 'getBranchStatus']);
            Route::get('/user-activity', [SystemMonitoringController::class, 'getUserActivity']);
        });
        
        // General monitoring (all authenticated users)
        Route::middleware('role:super_admin,branch_manager,cashier')->group(function () {
            Route::get('/pos-status', [SystemMonitoringController::class, 'getPosStatus']);
            Route::get('/sales-data', [SystemMonitoringController::class, 'getSalesData']);
            Route::get('/inventory-alerts', [SystemMonitoringController::class, 'getInventoryAlerts']);
        });
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});