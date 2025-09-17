<?php
/**
 * Debug Purchase Entries Script
 * 
 * This script helps debug why purchase orders are not showing on the Create Purchase Entry page.
 * It checks the user, roles, and purchase order data to identify the issue.
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Role;

echo "=== DEBUGGING PURCHASE ENTRIES ISSUE ===\n\n";

try {
    // Check users and their roles
    echo "1. Checking users and roles...\n";
    $users = User::with('roles')->get();
    foreach ($users as $user) {
        echo "User: {$user->name} (ID: {$user->id}, Branch ID: {$user->branch_id})\n";
        foreach ($user->roles as $role) {
            echo "  - Role: {$role->name}\n";
        }
    }
    echo "\n";

    // Check branch managers specifically
    echo "2. Checking branch managers...\n";
    $branchManagers = User::whereHas('roles', function($query) {
        $query->where('name', 'branch_manager');
    })->get();
    
    foreach ($branchManagers as $manager) {
        echo "Branch Manager: {$manager->name} (ID: {$manager->id}, Branch ID: {$manager->branch_id})\n";
    }
    echo "\n";

    // Check all purchase orders
    echo "3. Checking all purchase orders...\n";
    $allOrders = PurchaseOrder::with(['vendor', 'purchaseOrderItems'])->get();
    echo "Total purchase orders: " . $allOrders->count() . "\n";
    
    foreach ($allOrders as $order) {
        echo "PO: {$order->po_number} (ID: {$order->id}, Branch: {$order->branch_id}, Status: {$order->status}, Order Type: {$order->order_type}, Received: " . ($order->received_at ? 'Yes' : 'No') . ")\n";
    }
    echo "\n";

    // Check purchase orders for branch 1
    echo "4. Checking purchase orders for branch 1...\n";
    $branch1Orders = PurchaseOrder::where('branch_id', 1)->get();
    echo "Branch 1 orders: " . $branch1Orders->count() . "\n";
    
    foreach ($branch1Orders as $order) {
        echo "PO: {$order->po_number} (Status: {$order->status}, Order Type: {$order->order_type}, Received: " . ($order->received_at ? 'Yes' : 'No') . ")\n";
    }
    echo "\n";

    // Check the exact query from the controller
    echo "5. Testing the exact controller query...\n";
    $user = User::where('branch_id', 1)->whereHas('roles', function($query) {
        $query->where('name', 'branch_manager');
    })->first();
    
    if ($user) {
        echo "Found branch manager user: {$user->name} (Branch ID: {$user->branch_id})\n";
        
        $availablePurchaseOrders = PurchaseOrder::with(['vendor', 'purchaseOrderItems.product'])
            ->where('branch_id', $user->branch_id)
            ->where('order_type', 'branch_request')
            ->whereIn('status', ['approved', 'fulfilled'])
            ->whereNull('received_at')
            ->orderBy('created_at', 'desc')
            ->get();
            
        echo "Available purchase orders for this user: " . $availablePurchaseOrders->count() . "\n";
        
        foreach ($availablePurchaseOrders as $order) {
            echo "  - PO: {$order->po_number} (Status: {$order->status}, Vendor: {$order->vendor->name}, Items: {$order->purchaseOrderItems->count()})\n";
        }
    } else {
        echo "No branch manager found for branch 1!\n";
    }
    echo "\n";

    // Check what's missing
    echo "6. Analyzing what's missing...\n";
    
    $branch1Orders = PurchaseOrder::where('branch_id', 1)->get();
    $hasBranchRequest = $branch1Orders->where('order_type', 'branch_request')->count();
    $hasApprovedOrFulfilled = $branch1Orders->whereIn('status', ['approved', 'fulfilled'])->count();
    $hasNotReceived = $branch1Orders->whereNull('received_at')->count();
    
    echo "Branch 1 orders with order_type='branch_request': {$hasBranchRequest}\n";
    echo "Branch 1 orders with status in ['approved', 'fulfilled']: {$hasApprovedOrFulfilled}\n";
    echo "Branch 1 orders with received_at IS NULL: {$hasNotReceived}\n";
    
    if ($hasBranchRequest == 0) {
        echo "ISSUE: No orders have order_type='branch_request'\n";
    }
    if ($hasApprovedOrFulfilled == 0) {
        echo "ISSUE: No orders have status 'approved' or 'fulfilled'\n";
    }
    if ($hasNotReceived == 0) {
        echo "ISSUE: All orders have received_at set\n";
    }
    
    echo "\n=== DEBUG COMPLETE ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}