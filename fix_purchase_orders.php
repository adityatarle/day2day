<?php
/**
 * Fix Purchase Orders Script
 * 
 * This script fixes the purchase orders data to show up on the Create Purchase Entry page.
 * It updates existing purchase orders and creates test data with the correct status and order_type.
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;

echo "Starting purchase orders fix...\n";

try {
    // Start transaction
    DB::beginTransaction();

    // Update existing purchase orders
    echo "Updating existing purchase orders...\n";
    
    $existingOrders = PurchaseOrder::whereIn('id', [1, 2])->get();
    
    foreach ($existingOrders as $order) {
        $order->update([
            'order_type' => 'branch_request',
            'status' => $order->id == 1 ? 'approved' : 'fulfilled',
            'received_at' => null,
        ]);
        echo "Updated PO {$order->po_number} to status: {$order->status}\n";
    }

    // Create additional test purchase order
    echo "Creating additional test purchase order...\n";
    
    $newOrder = PurchaseOrder::create([
        'po_number' => 'PO003',
        'vendor_id' => 1,
        'branch_id' => 1,
        'user_id' => 2,
        'status' => 'approved',
        'order_type' => 'branch_request',
        'payment_terms' => '10_days',
        'subtotal' => 2500.00,
        'tax_amount' => 0.00,
        'transport_cost' => 100.00,
        'total_amount' => 2600.00,
        'notes' => 'Organic vegetables order',
        'expected_delivery_date' => now()->addDays(3),
        'actual_delivery_date' => null,
        'received_at' => null,
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDays(2),
    ]);

    echo "Created PO {$newOrder->po_number}\n";

    // Create purchase order items for all orders
    echo "Creating purchase order items...\n";
    
    $orderItems = [
        // PO001 items
        ['purchase_order_id' => 1, 'product_id' => 1, 'quantity' => 20.0, 'unit_price' => 120.00, 'total_price' => 2400.00, 'fulfilled_quantity' => 20.0],
        ['purchase_order_id' => 1, 'product_id' => 3, 'quantity' => 15.0, 'unit_price' => 30.00, 'total_price' => 450.00, 'fulfilled_quantity' => 15.0],
        ['purchase_order_id' => 1, 'product_id' => 5, 'quantity' => 25.0, 'unit_price' => 35.00, 'total_price' => 875.00, 'fulfilled_quantity' => 25.0],
        
        // PO002 items
        ['purchase_order_id' => 2, 'product_id' => 2, 'quantity' => 30.0, 'unit_price' => 40.00, 'total_price' => 1200.00, 'fulfilled_quantity' => 30.0],
        ['purchase_order_id' => 2, 'product_id' => 4, 'quantity' => 20.0, 'unit_price' => 80.00, 'total_price' => 1600.00, 'fulfilled_quantity' => 20.0],
        ['purchase_order_id' => 2, 'product_id' => 1, 'quantity' => 10.0, 'unit_price' => 120.00, 'total_price' => 1200.00, 'fulfilled_quantity' => 10.0],
        
        // PO003 items
        ['purchase_order_id' => 3, 'product_id' => 3, 'quantity' => 20.0, 'unit_price' => 30.00, 'total_price' => 600.00, 'fulfilled_quantity' => 20.0],
        ['purchase_order_id' => 3, 'product_id' => 5, 'quantity' => 15.0, 'unit_price' => 35.00, 'total_price' => 525.00, 'fulfilled_quantity' => 15.0],
        ['purchase_order_id' => 3, 'product_id' => 2, 'quantity' => 25.0, 'unit_price' => 40.00, 'total_price' => 1000.00, 'fulfilled_quantity' => 25.0],
    ];

    foreach ($orderItems as $item) {
        $item['created_at'] = now()->subDays($item['purchase_order_id'] == 1 ? 5 : ($item['purchase_order_id'] == 2 ? 3 : 2));
        $item['updated_at'] = $item['created_at'];
        
        PurchaseOrderItem::create($item);
    }

    echo "Created " . count($orderItems) . " purchase order items\n";

    // Commit transaction
    DB::commit();

    // Verify the results
    echo "\nVerifying results...\n";
    
    $availableOrders = PurchaseOrder::with(['vendor', 'purchaseOrderItems.product'])
        ->where('branch_id', 1)
        ->where('order_type', 'branch_request')
        ->whereIn('status', ['approved', 'fulfilled'])
        ->whereNull('received_at')
        ->orderBy('created_at', 'desc')
        ->get();

    echo "Found " . $availableOrders->count() . " available purchase orders:\n";
    
    foreach ($availableOrders as $order) {
        echo "- PO {$order->po_number} ({$order->status}) - {$order->vendor->name} - {$order->purchaseOrderItems->count()} items - ₹{$order->total_amount}\n";
    }

    echo "\nPurchase orders fix completed successfully!\n";
    echo "The Create Purchase Entry page should now show the available purchase orders.\n";

} catch (Exception $e) {
    DB::rollback();
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}