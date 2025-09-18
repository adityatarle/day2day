<?php
/**
 * Cleanup script to remove duplicate PO numbers and reset sequences
 * This script helps clean up any existing data that might be causing conflicts
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PurchaseOrder;
use App\Models\PoNumberSequence;
use Illuminate\Support\Facades\DB;

echo "Starting cleanup of duplicate PO numbers...\n";

try {
    DB::beginTransaction();
    
    // Find duplicate PO numbers
    echo "Checking for duplicate PO numbers...\n";
    $duplicates = DB::select("
        SELECT po_number, COUNT(*) as count 
        FROM purchase_orders 
        GROUP BY po_number 
        HAVING COUNT(*) > 1
    ");
    
    if (empty($duplicates)) {
        echo "No duplicate PO numbers found.\n";
    } else {
        echo "Found " . count($duplicates) . " duplicate PO numbers:\n";
        foreach ($duplicates as $dup) {
            echo "- {$dup->po_number} (appears {$dup->count} times)\n";
        }
        
        // For each duplicate, keep the oldest one and delete the rest
        foreach ($duplicates as $dup) {
            $orders = PurchaseOrder::where('po_number', $dup->po_number)
                ->orderBy('created_at', 'asc')
                ->get();
            
            if ($orders->count() > 1) {
                $keepOrder = $orders->first();
                $deleteOrders = $orders->skip(1);
                
                echo "Keeping order ID {$keepOrder->id} (created: {$keepOrder->created_at})\n";
                
                foreach ($deleteOrders as $deleteOrder) {
                    echo "Deleting order ID {$deleteOrder->id} (created: {$deleteOrder->created_at})\n";
                    $deleteOrder->delete();
                }
            }
        }
    }
    
    // Reset PO number sequences to avoid conflicts
    echo "Resetting PO number sequences...\n";
    
    // Get the highest sequence number for each prefix/order_type/year combination
    $maxSequences = DB::select("
        SELECT 
            prefix, 
            order_type, 
            year, 
            MAX(CAST(SUBSTRING(po_number, LENGTH(prefix) + 1) AS UNSIGNED)) as max_sequence
        FROM purchase_orders 
        WHERE po_number REGEXP '^[A-Z]+-[0-9]+-[0-9]+$'
        GROUP BY prefix, order_type, year
    ");
    
    foreach ($maxSequences as $maxSeq) {
        $prefix = $maxSeq->prefix;
        $orderType = $maxSeq->order_type;
        $year = $maxSeq->year;
        $maxSequence = $maxSeq->max_sequence;
        
        echo "Updating sequence for {$prefix} {$orderType} {$year} to {$maxSequence}\n";
        
        DB::table('po_number_sequences')
            ->updateOrInsert(
                [
                    'prefix' => $prefix,
                    'order_type' => $orderType,
                    'year' => $year
                ],
                [
                    'current_sequence' => $maxSequence,
                    'updated_at' => now()
                ]
            );
    }
    
    // Verify the cleanup
    echo "Verifying cleanup...\n";
    
    $remainingDuplicates = DB::select("
        SELECT po_number, COUNT(*) as count 
        FROM purchase_orders 
        GROUP BY po_number 
        HAVING COUNT(*) > 1
    ");
    
    if (empty($remainingDuplicates)) {
        echo "✓ No duplicate PO numbers remaining\n";
    } else {
        echo "✗ Still found " . count($remainingDuplicates) . " duplicate PO numbers\n";
    }
    
    // Show current sequences
    echo "Current sequences:\n";
    $sequences = DB::table('po_number_sequences')->get();
    foreach ($sequences as $seq) {
        echo "- {$seq->prefix} {$seq->order_type} {$seq->year}: {$seq->current_sequence}\n";
    }
    
    DB::commit();
    echo "Cleanup completed successfully!\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "Error during cleanup: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}