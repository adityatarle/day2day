<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\WebAuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\InventoryController;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\VendorController;
use App\Http\Controllers\Web\PurchaseOrderController;
use App\Http\Controllers\Web\ReportController;

// Home page - redirects to login if not authenticated
Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return redirect('/login');
});

// Authentication routes
Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Product management
    Route::middleware('role:admin,branch_manager')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::get('/products/category/{category}', [ProductController::class, 'byCategory'])->name('products.byCategory');
    });
    
    // Inventory management
    Route::middleware('role:admin,branch_manager')->group(function () {
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/add-stock', [InventoryController::class, 'addStockForm'])->name('inventory.addStockForm');
        Route::get('/inventory/record-loss', [InventoryController::class, 'recordLossForm'])->name('inventory.recordLossForm');
        Route::get('/inventory/batches', [InventoryController::class, 'batches'])->name('inventory.batches');
        Route::get('/inventory/stock-movements', [InventoryController::class, 'stockMovements'])->name('inventory.stockMovements');
        Route::get('/inventory/loss-tracking', [InventoryController::class, 'lossTracking'])->name('inventory.lossTracking');
        Route::get('/inventory/valuation', [InventoryController::class, 'valuation'])->name('inventory.valuation');
        Route::get('/inventory/low-stock-alerts', [InventoryController::class, 'lowStockAlerts'])->name('inventory.lowStockAlerts');
    });
    
    // Order management
    Route::middleware('role:admin,branch_manager,cashier')->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
        Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
        Route::get('/billing/quick-sale', [OrderController::class, 'quickSaleForm'])->name('billing.quickSale');
        Route::get('/billing/wholesale', [OrderController::class, 'wholesaleForm'])->name('billing.wholesale');
    });
    
    // Customer management
    Route::middleware('role:admin,branch_manager')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::get('/customers/{customer}/purchase-history', [CustomerController::class, 'purchaseHistory'])->name('customers.purchaseHistory');
    });
    
    // Vendor management
    Route::middleware('role:admin,branch_manager')->group(function () {
        Route::get('/vendors', [VendorController::class, 'index'])->name('vendors.index');
        Route::get('/vendors/create', [VendorController::class, 'create'])->name('vendors.create');
        Route::post('/vendors', [VendorController::class, 'store'])->name('vendors.store');
        Route::get('/vendors/{vendor}', [VendorController::class, 'show'])->name('vendors.show');
        Route::get('/vendors/{vendor}/edit', [VendorController::class, 'edit'])->name('vendors.edit');
        Route::put('/vendors/{vendor}', [VendorController::class, 'update'])->name('vendors.update');
        Route::delete('/vendors/{vendor}', [VendorController::class, 'destroy'])->name('vendors.destroy');
        Route::get('/vendors/{vendor}/analytics', [VendorController::class, 'analytics'])->name('vendors.analytics');
        Route::get('/vendors/{vendor}/credit-management', [VendorController::class, 'creditManagement'])->name('vendors.credit-management');
        Route::post('/vendors/{vendor}/credit-transaction', [VendorController::class, 'addCreditTransaction'])->name('vendors.addCreditTransaction');
    });
    
    // Purchase Order management
    Route::middleware('role:admin,branch_manager')->group(function () {
        Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::get('/purchase-orders/dashboard', [PurchaseOrderController::class, 'dashboard'])->name('purchase-orders.dashboard');
        Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
        Route::get('/purchase-orders/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit');
        Route::put('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('purchase-orders.update');
        Route::post('/purchase-orders/{purchaseOrder}/send', [PurchaseOrderController::class, 'send'])->name('purchase-orders.send');
        Route::post('/purchase-orders/{purchaseOrder}/confirm', [PurchaseOrderController::class, 'confirm'])->name('purchase-orders.confirm');
        Route::get('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'showReceiveForm'])->name('purchase-orders.receive-form');
        Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
        Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
        Route::get('/purchase-orders/{purchaseOrder}/pdf', [PurchaseOrderController::class, 'generatePdf'])->name('purchase-orders.pdf');
        Route::get('/api/vendors/{vendor}/products', [PurchaseOrderController::class, 'getVendorProducts'])->name('api.vendor-products');
    });
    
    // Reports
    Route::middleware('role:admin,branch_manager')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports/customers', [ReportController::class, 'customers'])->name('reports.customers');
        Route::get('/reports/vendors', [ReportController::class, 'vendors'])->name('reports.vendors');
        Route::get('/reports/expenses', [ReportController::class, 'expenses'])->name('reports.expenses');
        Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profitLoss');
        Route::get('/reports/analytics', [ReportController::class, 'analytics'])->name('reports.analytics');
    });
    
    // User profile and settings
    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');
    
    Route::get('/settings', function () {
        return view('settings');
    })->name('settings');
});

// Fallback route
Route::fallback(function () {
    return redirect('/');
});
