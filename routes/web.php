<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\WebAuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\SuperAdminDashboardController;
use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\BranchManagerDashboardController;
use App\Http\Controllers\Web\CashierDashboardController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\InventoryController;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\VendorController;
use App\Http\Controllers\Web\PurchaseOrderController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\AdminUserManagementController;
use App\Http\Controllers\Web\AdminBranchManagementController;
use App\Http\Controllers\Web\BranchStaffController;
use App\Http\Controllers\Web\CashierOrdersController;
use App\Http\Controllers\Web\OutletWebController;
use App\Http\Controllers\Web\PosWebController;
use App\Http\Controllers\Web\UserManagementController;
use App\Http\Controllers\Web\BranchManagementController;
use App\Http\Controllers\Web\PosSessionController;
use App\Http\Controllers\Auth\OutletAuthController;
use App\Http\Controllers\Day2Day\AdminDashboardController as Day2DayAdminController;
use App\Http\Controllers\Day2Day\BranchDashboardController as Day2DayBranchController;
use App\Http\Controllers\Web\AdminBranchOrderController;
use App\Http\Controllers\Web\BranchProductOrderController;
use App\Http\Controllers\Web\BranchPurchaseEntryController;
use App\Http\Controllers\Web\EnhancedPurchaseEntryController;
use App\Http\Controllers\Web\OrderWorkflowController;

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

// Outlet-specific authentication
Route::get('/outlet/{outletCode}/login', [OutletAuthController::class, 'showOutletLogin'])->name('outlet.login');
Route::post('/outlet/{outletCode}/login', [OutletAuthController::class, 'processOutletLogin'])->name('outlet.login.process');

// Protected routes
Route::middleware('auth')->group(function () {
    
    // Dashboard - Main redirect
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Role-specific dashboards
    Route::get('/dashboard/super-admin', [SuperAdminDashboardController::class, 'index'])
        ->name('dashboard.super_admin')
        ->middleware('role:super_admin');
    
    Route::get('/dashboard/admin', [AdminDashboardController::class, 'index'])
        ->name('dashboard.admin')
        ->middleware('role:admin');
    
    Route::get('/dashboard/branch-manager', [BranchManagerDashboardController::class, 'index'])
        ->name('dashboard.branch_manager')
        ->middleware('role:branch_manager');
    
    Route::get('/dashboard/cashier', [CashierDashboardController::class, 'index'])
        ->name('dashboard.cashier')
        ->middleware('role:cashier');
    
    // Cashier POS data endpoint
    Route::get('/api/cashier/pos-data', [CashierDashboardController::class, 'getPosData'])
        ->name('api.cashier.pos_data')
        ->middleware('role:cashier');
    
    // Product management
    Route::middleware('role:super_admin,admin,branch_manager')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::get('/products/category/{category}', [ProductController::class, 'byCategory'])->name('products.byCategory');
    });
    
    // Inventory management
    Route::middleware('role:super_admin,admin,branch_manager')->group(function () {
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/add-stock', [InventoryController::class, 'addStockForm'])->name('inventory.addStockForm');
        Route::post('/inventory/add-stock', [InventoryController::class, 'addStock'])->name('inventory.addStock');
        Route::get('/inventory/record-loss', [InventoryController::class, 'recordLossForm'])->name('inventory.recordLossForm');
        Route::post('/inventory/record-loss', [InventoryController::class, 'recordLoss'])->name('inventory.recordLoss');
        Route::get('/inventory/batches', [InventoryController::class, 'batches'])->name('inventory.batches');
        Route::get('/inventory/stock-movements', [InventoryController::class, 'stockMovements'])->name('inventory.stockMovements');
        Route::get('/inventory/loss-tracking', [InventoryController::class, 'lossTracking'])->name('inventory.lossTracking');
        Route::get('/inventory/valuation', [InventoryController::class, 'valuation'])->name('inventory.valuation');
        Route::get('/inventory/low-stock-alerts', [InventoryController::class, 'lowStockAlerts'])->name('inventory.lowStockAlerts');
    });
    
    // Order management
    Route::middleware('role:super_admin,admin,branch_manager,cashier')->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
        Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
        
        // Order Workflow Routes
        Route::get('/orders/workflow/dashboard', [OrderWorkflowController::class, 'dashboard'])->name('orders.workflow.dashboard');
        Route::get('/orders/workflow/{order}', [OrderWorkflowController::class, 'show'])->name('orders.workflow.show');
        Route::post('/orders/{order}/workflow/transition', [OrderWorkflowController::class, 'transition'])->name('orders.workflow.transition');
        Route::post('/orders/workflow/bulk-transition', [OrderWorkflowController::class, 'bulkTransition'])->name('orders.workflow.bulk-transition');
        Route::post('/orders/{order}/workflow/quality-check', [OrderWorkflowController::class, 'qualityCheck'])->name('orders.workflow.quality-check');
        Route::get('/orders/workflow/analytics', [OrderWorkflowController::class, 'analytics'])->name('orders.workflow.analytics');
        Route::get('/orders/workflow/status/{status}', [OrderWorkflowController::class, 'byStatus'])->name('orders.workflow.by-status');
        Route::put('/orders/{order}/workflow/priority', [OrderWorkflowController::class, 'updatePriority'])->name('orders.workflow.update-priority');
        Route::get('/billing/quick-sale', [OrderController::class, 'quickSaleForm'])->name('billing.quickSale');
        Route::get('/billing/wholesale', [OrderController::class, 'wholesaleForm'])->name('billing.wholesale');
    });
    
    // Customer management
    Route::middleware('role:super_admin,admin,branch_manager')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::get('/customers/{customer}/purchase-history', [CustomerController::class, 'purchaseHistory'])->name('customers.purchaseHistory');
    });
    
    // Vendor management - ONLY for main branch (super_admin, admin)
    // Sub-branches should NOT have access to vendors as vendors are confidential to main branch
    Route::middleware('role:super_admin,admin')->group(function () {
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
        Route::get('/vendors/purchase-orders', [VendorController::class, 'purchaseOrders'])->name('vendors.purchaseOrders');
    });
    
    // Purchase Order management
    // Main branch (admin) can create orders to vendors and manage all orders
    // Sub-branches (branch_manager) can only create purchase REQUESTS to main branch and view their own orders
    Route::middleware('role:super_admin,admin,branch_manager')->group(function () {
        Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::get('/purchase-orders/dashboard', [PurchaseOrderController::class, 'dashboard'])->name('purchase-orders.dashboard');
        Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->where('purchaseOrder', '[0-9]+')->name('purchase-orders.show');
        Route::get('/purchase-orders/{purchaseOrder}/pdf', [PurchaseOrderController::class, 'generatePdf'])->where('purchaseOrder', '[0-9]+')->name('purchase-orders.pdf');
    });
    
    // Purchase Order creation and vendor operations - ONLY for main branch
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        Route::get('/purchase-orders/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit');
        Route::put('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('purchase-orders.update');
        Route::post('/purchase-orders/{purchaseOrder}/send', [PurchaseOrderController::class, 'send'])->name('purchase-orders.send');
        Route::post('/purchase-orders/{purchaseOrder}/confirm', [PurchaseOrderController::class, 'confirm'])->name('purchase-orders.confirm');
        Route::get('/purchase-orders/receive/{purchaseOrder?}', [PurchaseOrderController::class, 'showReceiveForm'])->name('purchase-orders.receive-form');
        Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
        Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
        Route::get('/api/vendors/{vendor}/products', [PurchaseOrderController::class, 'getVendorProducts'])->name('api.vendor-products');
    });
    
    // Branch Purchase Requests - Sub-branches can only send requests to main branch
    Route::middleware('role:branch_manager')->group(function () {
        Route::get('/purchase-requests', [PurchaseOrderController::class, 'branchRequests'])->name('purchase-requests.index');
        Route::get('/purchase-requests/create', [PurchaseOrderController::class, 'createBranchRequest'])->name('purchase-requests.create');
        Route::post('/purchase-requests', [PurchaseOrderController::class, 'storeBranchRequest'])->name('purchase-requests.store');
        Route::get('/purchase-requests/{purchaseOrder}', [PurchaseOrderController::class, 'showBranchRequest'])->name('purchase-requests.show');
    });
    
    // Reports
    Route::middleware('role:super_admin,admin,branch_manager')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports/customers', [ReportController::class, 'customers'])->name('reports.customers');
        Route::get('/vendors', [VendorController::class, 'index'])->name('vendors.index');
        Route::get('/reports/vendors', [ReportController::class, 'vendors'])->name('reports.vendors');
        Route::get('/reports/expenses', [ReportController::class, 'expenses'])->name('reports.expenses');
        Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profitLoss');
        Route::get('/reports/analytics', [ReportController::class, 'analytics'])->name('reports.analytics');
    });
    
    // Super Admin and Admin routes
    Route::middleware('role:super_admin,admin')->group(function () {
        // Admin Dashboard
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        // Branch Orders Management - Admin can see all branch orders and assign vendors
        Route::get('/admin/branch-orders', [AdminBranchOrderController::class, 'index'])->name('admin.branch-orders.index');
        Route::get('/admin/branch-orders/{branchOrder}', [AdminBranchOrderController::class, 'show'])->name('admin.branch-orders.show');
        Route::post('/admin/branch-orders/{branchOrder}/approve', [AdminBranchOrderController::class, 'approve'])->name('admin.branch-orders.approve');
        Route::post('/admin/branch-orders/{branchOrder}/create-vendor-po', [AdminBranchOrderController::class, 'createVendorPurchaseOrder'])->name('admin.branch-orders.create-vendor-po');
        Route::get('/admin/branch-orders/{branchOrder}/fulfill', [AdminBranchOrderController::class, 'showFulfillForm'])->name('admin.branch-orders.fulfill-form');
        Route::post('/admin/branch-orders/{branchOrder}/fulfill', [AdminBranchOrderController::class, 'fulfill'])->name('admin.branch-orders.fulfill');
        Route::post('/admin/branch-orders/{branchOrder}/cancel', [AdminBranchOrderController::class, 'cancel'])->name('admin.branch-orders.cancel');
        
        // User Management
        Route::resource('admin/users', AdminUserManagementController::class)->names([
            'index' => 'admin.users.index',
            'create' => 'admin.users.create',
            'store' => 'admin.users.store',
            'show' => 'admin.users.show',
            'edit' => 'admin.users.edit',
            'update' => 'admin.users.update',
            'destroy' => 'admin.users.destroy',
        ]);
        Route::patch('/admin/users/{user}/toggle-status', [AdminUserManagementController::class, 'toggleStatus'])
            ->name('admin.users.toggle-status');
        
        // Branch Management
        Route::resource('admin/branches', AdminBranchManagementController::class)->names([
            'index' => 'admin.branches.index',
            'create' => 'admin.branches.create',
            'store' => 'admin.branches.store',
            'show' => 'admin.branches.show',
            'edit' => 'admin.branches.edit',
            'update' => 'admin.branches.update',
            'destroy' => 'admin.branches.destroy',
        ]);
        Route::patch('/admin/branches/{branch}/toggle-status', [AdminBranchManagementController::class, 'toggleStatus'])
            ->name('admin.branches.toggle-status');
        Route::patch('/admin/branches/{branch}/assign-manager', [AdminBranchManagementController::class, 'assignManager'])
            ->name('admin.branches.assign-manager');
        
        // Additional branch management routes
        Route::post('/admin/branches/{branch}/add-staff', [AdminBranchManagementController::class, 'addStaff'])
            ->name('admin.branches.add-staff');
        Route::post('/admin/users/{user}/reset-password', [AdminBranchManagementController::class, 'resetStaffPassword'])
            ->name('admin.users.reset-password');
        Route::patch('/admin/users/{user}/toggle-status', [AdminBranchManagementController::class, 'toggleStaffStatus'])
            ->name('admin.users.toggle-status');
        Route::get('/admin/branches/{branch}/pos-details', [AdminBranchManagementController::class, 'getPosDetails'])
            ->name('admin.branches.pos-details');
        Route::get('/admin/branches/{branch}/inventory', [AdminBranchManagementController::class, 'getInventory'])
            ->name('admin.branches.inventory');
        Route::get('/admin/branches/{branch}/reports', [AdminBranchManagementController::class, 'getReports'])
            ->name('admin.branches.reports');

        // Branch performance analytics
        Route::get('/admin/branches/performance', [AdminController::class, 'branchPerformance'])
            ->name('admin.branches.performance');
        
        // Role Management
        Route::get('/admin/roles', [AdminController::class, 'roles'])->name('admin.roles');
        
        // Purchase Entries Management
        Route::get('/admin/purchase-entries', [AdminController::class, 'purchaseEntries'])->name('admin.purchase-entries.index');
        
        // System Settings
        Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
        Route::get('/admin/security', [AdminController::class, 'security'])->name('admin.security');
        Route::get('/admin/analytics', [AdminController::class, 'analytics'])->name('admin.analytics');
        Route::get('/admin/roles', [AdminController::class, 'roles'])->name('admin.roles.index');
    });

    // Branch Manager specific routes
    Route::middleware('role:branch_manager')->group(function () {
        // Branch Staff Management
        Route::resource('branch/staff', BranchStaffController::class)->names([
            'index' => 'branch.staff.index',
            'create' => 'branch.staff.create',
            'store' => 'branch.staff.store',
            'show' => 'branch.staff.show',
            'edit' => 'branch.staff.edit',
            'update' => 'branch.staff.update',
            'destroy' => 'branch.staff.destroy',
        ]);
        Route::patch('/branch/staff/{staff}/toggle-status', [BranchStaffController::class, 'toggleStatus'])
            ->name('branch.staff.toggle-status');

        // Branch Product Orders - Branch managers order products from admin
        Route::get('/branch/product-orders', [BranchProductOrderController::class, 'index'])->name('branch.product-orders.index');
        Route::get('/branch/product-orders/create', [BranchProductOrderController::class, 'create'])->name('branch.product-orders.create');
        Route::post('/branch/product-orders', [BranchProductOrderController::class, 'store'])->name('branch.product-orders.store');
        Route::get('/branch/product-orders/{productOrder}', [BranchProductOrderController::class, 'show'])->name('branch.product-orders.show');
        Route::get('/branch/product-orders/{productOrder}/edit', [BranchProductOrderController::class, 'edit'])->name('branch.product-orders.edit');
        Route::put('/branch/product-orders/{productOrder}', [BranchProductOrderController::class, 'update'])->name('branch.product-orders.update');
        Route::delete('/branch/product-orders/{productOrder}', [BranchProductOrderController::class, 'destroy'])->name('branch.product-orders.destroy');

        // Branch Purchase Entries - Track deliveries from admin with discrepancies
        Route::get('/branch/purchase-entries', [BranchPurchaseEntryController::class, 'index'])->name('branch.purchase-entries.index');
        Route::get('/branch/purchase-entries/create', [BranchPurchaseEntryController::class, 'create'])->name('branch.purchase-entries.create');
        Route::get('/branch/purchase-entries/{purchaseEntry}', [BranchPurchaseEntryController::class, 'show'])->name('branch.purchase-entries.show');
        Route::get('/branch/purchase-entries/{purchaseEntry}/create-receipt', [BranchPurchaseEntryController::class, 'createReceipt'])->name('branch.purchase-entries.create-receipt');
        Route::post('/branch/purchase-entries/{purchaseEntry}/store-receipt', [BranchPurchaseEntryController::class, 'storeReceipt'])->name('branch.purchase-entries.store-receipt');
        Route::get('/branch/purchase-entries/{purchaseEntry}/receipt', [BranchPurchaseEntryController::class, 'showReceipt'])->name('branch.purchase-entries.receipt');
        Route::get('/branch/discrepancy-report', [BranchPurchaseEntryController::class, 'discrepancyReport'])->name('branch.purchase-entries.discrepancy-report');
        Route::get('/branch/purchase-entries-debug', [BranchPurchaseEntryController::class, 'debug'])->name('branch.purchase-entries.debug');

        // Enhanced Purchase Entries - Comprehensive tracking with detailed quantities
        Route::get('/enhanced-purchase-entries', [EnhancedPurchaseEntryController::class, 'index'])->name('enhanced-purchase-entries.index');
        Route::get('/enhanced-purchase-entries/create', [EnhancedPurchaseEntryController::class, 'create'])->name('enhanced-purchase-entries.create');
        Route::post('/enhanced-purchase-entries', [EnhancedPurchaseEntryController::class, 'store'])->name('enhanced-purchase-entries.store');
        Route::get('/enhanced-purchase-entries/{purchaseOrder}', [EnhancedPurchaseEntryController::class, 'show'])->name('enhanced-purchase-entries.show');
        Route::get('/enhanced-purchase-entries/entry/{purchaseEntry}', [EnhancedPurchaseEntryController::class, 'showEntry'])->name('enhanced-purchase-entries.entry');
        Route::get('/enhanced-purchase-entries/report', [EnhancedPurchaseEntryController::class, 'report'])->name('enhanced-purchase-entries.report');
        
        // API routes for enhanced purchase entries
        Route::get('/api/purchase-orders/{purchaseOrder}/items', function($purchaseOrderId) {
            $purchaseOrder = \App\Models\PurchaseOrder::with('purchaseOrderItems.product')->findOrFail($purchaseOrderId);
            return response()->json([
                'success' => true,
                'items' => $purchaseOrder->purchaseOrderItems
            ]);
        })->name('api.purchase-orders.items');

        // Branch-specific routes
        Route::get('/branch/inventory', [InventoryController::class, 'branchIndex'])->name('branch.inventory.index');
        Route::get('/branch/orders', [OrderController::class, 'branchIndex'])->name('branch.orders.index');
        Route::get('/branch/customers', [CustomerController::class, 'branchIndex'])->name('branch.customers.index');
        Route::get('/branch/expenses', [ExpenseController::class, 'branchIndex'])->name('branch.expenses.index');
        Route::get('/branch/reports', [ReportController::class, 'branchIndex'])->name('branch.reports.index');
    });

    // Cashier specific routes
    Route::middleware('role:cashier')->group(function () {
        // Cashier Orders and Sales
        Route::get('/cashier/orders', [CashierOrdersController::class, 'index'])->name('cashier.orders.index');
        Route::get('/cashier/orders/{order}', [CashierOrdersController::class, 'show'])->name('cashier.orders.show');
        
        // Returns and Refunds
        Route::get('/cashier/returns', [CashierOrdersController::class, 'returns'])->name('cashier.returns.index');
        Route::get('/cashier/orders/{order}/return', [CashierOrdersController::class, 'createReturn'])->name('cashier.returns.create');
        Route::post('/cashier/orders/{order}/return', [CashierOrdersController::class, 'storeReturn'])->name('cashier.returns.store');
        
        // Cashier specific views
        Route::get('/cashier/inventory/view', [InventoryController::class, 'cashierView'])->name('cashier.inventory.view');
        Route::get('/cashier/customers/search', [CustomerController::class, 'cashierSearch'])->name('cashier.customers.search');
        Route::get('/cashier/help', function() { 
            return view('cashier.help'); 
        })->name('cashier.help');
        
        // POS specific routes
        Route::get('/pos/session/current', [PosSessionController::class, 'current'])->name('pos.session.current');
        Route::get('/pos/session/history', [PosSessionController::class, 'history'])->name('pos.session.history');
        Route::post('/api/pos/session/start', [PosSessionController::class, 'start'])->name('api.pos.session.start');
    });

    // Outlet Management (Super Admin, Admin and Branch Manager)
    Route::middleware('role:super_admin,admin,branch_manager')->group(function () {
        Route::get('/outlets', [OutletWebController::class, 'index'])->name('outlets.index');
        Route::get('/outlets/create', [OutletWebController::class, 'create'])->name('outlets.create');
        Route::post('/outlets', [OutletWebController::class, 'store'])->name('outlets.store');
        Route::get('/outlets/{outlet}', [OutletWebController::class, 'show'])->name('outlets.show');
        Route::get('/outlets/{outlet}/edit', [OutletWebController::class, 'edit'])->name('outlets.edit');
        Route::put('/outlets/{outlet}', [OutletWebController::class, 'update'])->name('outlets.update');
        Route::delete('/outlets/{outlet}', [OutletWebController::class, 'destroy'])->name('outlets.destroy');
        Route::get('/outlets/{outlet}/staff', [OutletWebController::class, 'manageStaff'])->name('outlets.staff');
    });

    // POS System (Cashier, Branch Manager, Admin, Super Admin)
    Route::middleware('role:super_admin,admin,branch_manager,cashier')->group(function () {
        Route::get('/pos', [PosWebController::class, 'index'])->name('pos.index');
        Route::get('/pos/start-session', [PosWebController::class, 'startSession'])->name('pos.start-session');
        Route::post('/pos/start-session', [PosWebController::class, 'processStartSession'])->name('pos.process-start-session');
        Route::get('/pos/close-session', [PosWebController::class, 'closeSession'])->name('pos.close-session');
        Route::post('/pos/close-session', [PosWebController::class, 'processCloseSession'])->name('pos.process-close-session');
        Route::get('/pos/sale', [PosWebController::class, 'sale'])->name('pos.sale');
        Route::get('/pos/sales', [PosWebController::class, 'sales'])->name('pos.sales');
        Route::get('/pos/history', [PosWebController::class, 'sessionHistory'])->name('pos.history');
        
        // POS API routes
        Route::get('/api/pos/products', [PosWebController::class, 'getProducts'])->name('api.pos.products');
        Route::post('/api/pos/process-sale', [PosWebController::class, 'processSale'])->name('api.pos.process-sale');
    });
    
    // User Management (Super Admin and Branch Manager)
    Route::middleware('role:super_admin,branch_manager')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
        Route::get('/api/users/stats', [UserManagementController::class, 'getUserStats'])->name('api.users.stats');
    });

    // Branch Management (Super Admin only)
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/branches', [BranchManagementController::class, 'index'])->name('branches.index');
        Route::get('/branches/create', [BranchManagementController::class, 'create'])->name('branches.create');
        Route::post('/branches', [BranchManagementController::class, 'store'])->name('branches.store');
        Route::get('/branches/{branch}', [BranchManagementController::class, 'show'])->name('branches.show');
        Route::get('/branches/{branch}/edit', [BranchManagementController::class, 'edit'])->name('branches.edit');
        Route::put('/branches/{branch}', [BranchManagementController::class, 'update'])->name('branches.update');
        Route::post('/branches/{branch}/toggle-status', [BranchManagementController::class, 'toggleStatus'])->name('branches.toggle-status');
        Route::post('/branches/{branch}/assign-manager', [BranchManagementController::class, 'assignManager'])->name('branches.assign-manager');
        Route::get('/api/branches/{branch}/performance', [BranchManagementController::class, 'getPerformanceData'])->name('api.branches.performance');
    });

    // Branch Inventory Management (Super Admin and Branch Manager)
    Route::middleware('role:super_admin,branch_manager')->group(function () {
        Route::get('/branches/{branch}/inventory', [BranchManagementController::class, 'inventory'])->name('branches.inventory');
        Route::post('/branches/{branch}/inventory/{product}', [BranchManagementController::class, 'updateInventoryItem'])->name('branches.inventory.update');
    });

    // Enhanced POS Session Management
    Route::middleware('role:super_admin,branch_manager,cashier')->group(function () {
        Route::get('/pos/sessions', [PosSessionController::class, 'index'])->name('pos.sessions.index');
        Route::get('/pos/sessions/create', [PosSessionController::class, 'create'])->name('pos.sessions.create');
        Route::post('/pos/sessions', [PosSessionController::class, 'store'])->name('pos.sessions.store');
        Route::get('/pos/sessions/{posSession}', [PosSessionController::class, 'show'])->name('pos.sessions.show');
        Route::post('/pos/sessions/{posSession}/close', [PosSessionController::class, 'close'])->name('pos.sessions.close');
        Route::get('/api/pos/sessions/{posSession}/performance', [PosSessionController::class, 'getPerformanceData'])->name('api.pos.sessions.performance');
        Route::get('/api/pos/active-sessions', [PosSessionController::class, 'getActiveSessions'])->name('api.pos.active-sessions');
    });

    // User profile and settings
    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');
    
    Route::get('/settings', function () {
        return view('settings');
    })->name('settings');
    
    // Day2Day specific routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/day2day/admin/dashboard', [Day2DayAdminController::class, 'index'])->name('day2day.admin.dashboard');
        Route::get('/day2day/admin/branches', [Day2DayAdminController::class, 'getBranches'])->name('day2day.admin.branches');
        Route::get('/day2day/admin/products', [Day2DayAdminController::class, 'getProducts'])->name('day2day.admin.products');
        Route::get('/day2day/admin/cities', [Day2DayAdminController::class, 'getCities'])->name('day2day.admin.cities');
        Route::post('/day2day/admin/supply-materials', [Day2DayAdminController::class, 'supplyMaterials'])->name('day2day.admin.supply-materials');
        Route::get('/day2day/admin/branch-reports/{branch}', [Day2DayAdminController::class, 'getBranchReports'])->name('day2day.admin.branch-reports');
        Route::post('/day2day/admin/update-city-pricing', [Day2DayAdminController::class, 'updateCityPricing'])->name('day2day.admin.update-city-pricing');
    });
    
    Route::middleware('role:branch_manager,cashier')->group(function () {
        Route::get('/day2day/branch/dashboard', [Day2DayBranchController::class, 'index'])->name('day2day.branch.dashboard');
        // Removed vendor access - sub-branches should NOT interact with vendors directly
        Route::get('/day2day/branch/products', [Day2DayBranchController::class, 'getBranchProducts'])->name('day2day.branch.products');
        Route::post('/day2day/branch/material-receipt', [Day2DayBranchController::class, 'recordMaterialReceipt'])->name('day2day.branch.material-receipt');
        Route::post('/day2day/branch/record-damage', [Day2DayBranchController::class, 'recordDamage'])->name('day2day.branch.record-damage');
        Route::get('/day2day/branch/sales-report', [Day2DayBranchController::class, 'getSalesReport'])->name('day2day.branch.sales-report');
        Route::get('/day2day/branch/purchase-report', [Day2DayBranchController::class, 'getPurchaseReport'])->name('day2day.branch.purchase-report');
        Route::post('/day2day/branch/purchase-request', [Day2DayBranchController::class, 'createPurchaseRequest'])->name('day2day.branch.purchase-request');
    });
});

// Fallback route
Route::fallback(function () {
    return redirect('/');
});
