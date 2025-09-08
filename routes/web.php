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
        Route::get('/billing/quick-sale', [OrderController::class, 'quickSaleForm'])->name('billing.quickSale');
        Route::get('/billing/wholesale', [OrderController::class, 'wholesaleForm'])->name('billing.wholesale');
    });
    
    // Customer management
    Route::middleware('role:super_admin,admin,branch_manager')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::get('/customers/{customer}/purchase-history', [CustomerController::class, 'purchaseHistory'])->name('customers.purchaseHistory');
    });
    
    // Vendor management
    Route::middleware('role:super_admin,admin,branch_manager')->group(function () {
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
    Route::middleware('role:super_admin,admin,branch_manager')->group(function () {
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

        // Branch performance analytics
        Route::get('/admin/branches/performance', [AdminController::class, 'branchPerformance'])
            ->name('admin.branches.performance');
        
        // Role Management
        Route::get('/admin/roles', [AdminController::class, 'roles'])->name('admin.roles');
        
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
});

// Fallback route
Route::fallback(function () {
    return redirect('/');
});
