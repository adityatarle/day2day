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

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    
    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
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
    });
    
    // Payment management
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{payment}', [PaymentController::class, 'show']);
    
    // Credit transactions
    Route::get('/credit-transactions', [CreditTransactionController::class, 'index']);
    Route::post('/credit-transactions', [CreditTransactionController::class, 'store']);
    Route::get('/credit-transactions/{transaction}', [CreditTransactionController::class, 'show']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});