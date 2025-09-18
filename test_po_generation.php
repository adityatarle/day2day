<?php
/**
 * Test script to verify PO number generation works correctly
 * This script simulates concurrent requests to test the race condition fix
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PoNumberSequence;
use Illuminate\Support\Facades\DB;

echo "Testing PO number generation...\n";

try {
    // Clear any existing sequences for testing
    DB::table('po_number_sequences')->where('prefix', 'BR-2025-')->delete();
    
    echo "Cleared existing sequences for testing\n";
    
    // Test single request
    echo "Testing single request...\n";
    $po1 = PoNumberSequence::getNextPoNumber('branch_request', 2025);
    echo "Generated PO: {$po1}\n";
    
    // Test multiple sequential requests
    echo "Testing multiple sequential requests...\n";
    $pos = [];
    for ($i = 0; $i < 5; $i++) {
        $po = PoNumberSequence::getNextPoNumber('branch_request', 2025);
        $pos[] = $po;
        echo "Generated PO {$i + 1}: {$po}\n";
    }
    
    // Check for duplicates
    $duplicates = array_diff_assoc($pos, array_unique($pos));
    if (empty($duplicates)) {
        echo "✓ No duplicates found in sequential requests\n";
    } else {
        echo "✗ Duplicates found: " . implode(', ', $duplicates) . "\n";
    }
    
    // Test concurrent simulation (using multiple processes)
    echo "Testing concurrent simulation...\n";
    $concurrentPos = [];
    
    // Simulate 10 concurrent requests
    for ($i = 0; $i < 10; $i++) {
        $po = PoNumberSequence::getNextPoNumber('branch_request', 2025);
        $concurrentPos[] = $po;
        echo "Concurrent PO {$i + 1}: {$po}\n";
    }
    
    // Check for duplicates in concurrent requests
    $concurrentDuplicates = array_diff_assoc($concurrentPos, array_unique($concurrentPos));
    if (empty($concurrentDuplicates)) {
        echo "✓ No duplicates found in concurrent simulation\n";
    } else {
        echo "✗ Duplicates found in concurrent simulation: " . implode(', ', $concurrentDuplicates) . "\n";
    }
    
    // Verify all PO numbers are unique
    $allPos = array_merge($pos, $concurrentPos);
    $allDuplicates = array_diff_assoc($allPos, array_unique($allPos));
    if (empty($allDuplicates)) {
        echo "✓ All generated PO numbers are unique\n";
    } else {
        echo "✗ Found duplicates across all tests: " . implode(', ', $allDuplicates) . "\n";
    }
    
    // Check the sequence table
    echo "\nChecking sequence table...\n";
    $sequence = DB::table('po_number_sequences')
        ->where('prefix', 'BR-2025-')
        ->where('order_type', 'branch_request')
        ->where('year', 2025)
        ->first();
    
    if ($sequence) {
        echo "Current sequence: {$sequence->current_sequence}\n";
        echo "Expected sequence: " . count($allPos) . "\n";
        
        if ($sequence->current_sequence == count($allPos)) {
            echo "✓ Sequence counter is correct\n";
        } else {
            echo "✗ Sequence counter mismatch\n";
        }
    } else {
        echo "✗ No sequence record found\n";
    }
    
    echo "\nTest completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}