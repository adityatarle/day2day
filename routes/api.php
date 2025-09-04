<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\Auth\OutletAuthController;

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
Route::post('/login', [AuthController::class, 'login']);

// Outlet-specific authentication
Route::post('/outlet/login', [OutletAuthController::class, 'outletLogin']);
Route::get('/outlet/{outletCode}/info', [OutletAuthController::class, 'getOutletInfo']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    
    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // Outlet authentication routes
    Route::post('/outlet/logout', [OutletAuthController::class, 'outletLogout']);
    Route::post('/outlet/change-password', [OutletAuthController::class, 'changePassword']);
    
    // User management routes (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::get('/roles', [UserController::class, 'getRoles']);
        Route::get('/branches', [UserController::class, 'getBranches']);
        
        // Branch management
        Route::apiResource('branches', BranchController::class);
        
        // Vendor management
        Route::apiResource('vendors', VendorController::class);
        
        // Expense categories
        Route::get('/expense-categories', [ExpenseController::class, 'getCategories']);
        Route::post('/expense-categories', [ExpenseController::class, 'storeCategory']);
        Route::put('/expense-categories/{category}', [ExpenseController::class, 'updateCategory']);
        Route::delete('/expense-categories/{category}', [ExpenseController::class, 'destroyCategory']);
        
        // GST rates
        Route::get('/gst-rates', [ProductController::class, 'getGstRates']);
        Route::post('/gst-rates', [ProductController::class, 'storeGstRate']);
        Route::put('/gst-rates/{gstRate}', [ProductController::class, 'updateGstRate']);
        Route::delete('/gst-rates/{gstRate}', [ProductController::class, 'destroyGstRate']);
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
        Route::apiResource('customers', CustomerController::class);
        Route::get('/customers/{customer}/purchase-history', [CustomerController::class, 'getPurchaseHistory']);
        Route::get('/customers/{customer}/credit-balance', [CustomerController::class, 'getCreditBalance']);
        
        // Purchase orders
        Route::apiResource('purchase-orders', PurchaseOrderController::class);
        Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive']);
        
        // Reports
        Route::get('/reports/sales', [ReportController::class, 'getSalesReport']);
        Route::get('/reports/inventory', [ReportController::class, 'getInventoryReport']);
        Route::get('/reports/customers', [ReportController::class, 'getCustomerReport']);
        Route::get('/reports/vendors', [ReportController::class, 'getVendorReport']);
        Route::get('/reports/expenses', [ReportController::class, 'getExpenseReport']);
        Route::get('/reports/profit-loss', [ReportController::class, 'getProfitLossReport']);
    });
    
    // Cashier routes
    Route::middleware('role:admin,branch_manager,cashier')->group(function () {
        // Order management
        Route::apiResource('orders', OrderController::class);
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
        Route::get('/orders/{order}/invoice', [OrderController::class, 'generateInvoice']);
        Route::get('/orders/statistics', [OrderController::class, 'getStatistics']);
        
        // Quick billing
        Route::post('/billing/quick-sale', [OrderController::class, 'quickSale']);
        Route::post('/billing/wholesale', [OrderController::class, 'wholesaleSale']);
    });
    
    // Delivery Boy routes
    Route::middleware('role:admin,branch_manager,delivery_boy')->group(function () {
        // Delivery management
        Route::get('/deliveries/assigned', [DeliveryController::class, 'getAssignedDeliveries']);
        Route::put('/deliveries/{delivery}/pickup', [DeliveryController::class, 'markAsPickedUp']);
        Route::put('/deliveries/{delivery}/in-transit', [DeliveryController::class, 'markAsInTransit']);
        Route::put('/deliveries/{delivery}/delivered', [DeliveryController::class, 'markAsDelivered']);
        Route::put('/deliveries/{delivery}/returned', [DeliveryController::class, 'markAsReturned']);
        
        // Returns management
        Route::post('/returns', [ReturnController::class, 'store']);
        Route::put('/returns/{return}/approve', [ReturnController::class, 'approve']);
        Route::put('/returns/{return}/reject', [ReturnController::class, 'reject']);
        Route::put('/returns/{return}/process', [ReturnController::class, 'process']);
        
        // Customer adjustments
        Route::post('/adjustments', [OrderController::class, 'createAdjustment']);
    });
    
    // Common routes for all authenticated users
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
    Route::get('/dashboard/recent-orders', [DashboardController::class, 'getRecentOrders']);
    Route::get('/dashboard/low-stock', [DashboardController::class, 'getLowStock']);
    Route::get('/dashboard/today-sales', [DashboardController::class, 'getTodaySales']);
    
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
    Route::put('/products/{product}/vendor-pricing', [ProductController::class, 'updateVendorPricing']);
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
    
    // Payment management
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{payment}', [PaymentController::class, 'show']);
    
    // Credit transactions
    Route::get('/credit-transactions', [CreditTransactionController::class, 'index']);
    Route::post('/credit-transactions', [CreditTransactionController::class, 'store']);
    Route::get('/credit-transactions/{transaction}', [CreditTransactionController::class, 'show']);

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
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});