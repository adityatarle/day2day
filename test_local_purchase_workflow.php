<?php

require_once 'vendor/autoload.php';

use App\Models\LocalPurchase;
use App\Models\LocalPurchaseItem;
use App\Models\Branch;
use App\Models\User;
use App\Models\Product;
use App\Models\Vendor;

// This is a simple test script to verify the local purchase workflow
echo "Testing Local Purchase Workflow...\n\n";

// Test 1: Check if LocalPurchase model exists and has required methods
echo "1. Testing LocalPurchase model...\n";
try {
    $reflection = new ReflectionClass(LocalPurchase::class);
    $methods = ['isPending', 'isApproved', 'isRejected', 'isCompleted', 'approve', 'reject', 'markAsCompleted'];
    
    foreach ($methods as $method) {
        if ($reflection->hasMethod($method)) {
            echo "   ✓ Method '$method' exists\n";
        } else {
            echo "   ✗ Method '$method' missing\n";
        }
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Test 2: Check if LocalPurchaseItem model exists and has required methods
echo "\n2. Testing LocalPurchaseItem model...\n";
try {
    $reflection = new ReflectionClass(LocalPurchaseItem::class);
    $methods = ['updateStock', 'calculateAmounts'];
    
    foreach ($methods as $method) {
        if ($reflection->hasMethod($method)) {
            echo "   ✓ Method '$method' exists\n";
        } else {
            echo "   ✗ Method '$method' missing\n";
        }
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Test 3: Check if LocalPurchaseNotification model exists
echo "\n3. Testing LocalPurchaseNotification model...\n";
try {
    $reflection = new ReflectionClass(\App\Models\LocalPurchaseNotification::class);
    $methods = ['markAsRead', 'markEmailAsSent', 'getMessage', 'getTitle'];
    
    foreach ($methods as $method) {
        if ($reflection->hasMethod($method)) {
            echo "   ✓ Method '$method' exists\n";
        } else {
            echo "   ✗ Method '$method' missing\n";
        }
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Test 4: Check if required models exist
echo "\n4. Testing required models...\n";
$models = [
    'Branch' => Branch::class,
    'User' => User::class,
    'Product' => Product::class,
    'Vendor' => Vendor::class,
    'Expense' => \App\Models\Expense::class,
    'ExpenseCategory' => \App\Models\ExpenseCategory::class,
    'StockMovement' => \App\Models\StockMovement::class,
];

foreach ($models as $name => $class) {
    try {
        $reflection = new ReflectionClass($class);
        echo "   ✓ $name model exists\n";
    } catch (Exception $e) {
        echo "   ✗ $name model missing: " . $e->getMessage() . "\n";
    }
}

// Test 5: Check if Mail class exists
echo "\n5. Testing Mail classes...\n";
try {
    $reflection = new ReflectionClass(\App\Mail\LocalPurchaseNotification::class);
    echo "   ✓ LocalPurchaseNotification mail class exists\n";
} catch (Exception $e) {
    echo "   ✗ LocalPurchaseNotification mail class missing: " . $e->getMessage() . "\n";
}

// Test 6: Check if Job class exists
echo "\n6. Testing Job classes...\n";
try {
    $reflection = new ReflectionClass(\App\Jobs\SendLocalPurchaseNotificationEmail::class);
    echo "   ✓ SendLocalPurchaseNotificationEmail job class exists\n";
} catch (Exception $e) {
    echo "   ✗ SendLocalPurchaseNotificationEmail job class missing: " . $e->getMessage() . "\n";
}

echo "\nWorkflow test completed!\n";