<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Product;
use App\Models\User;
use App\Models\Role;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\StockTransferQuery;
use App\Models\TransportExpense;
use App\Models\StockFinancialImpact;
use App\Models\StockAlert;
use App\Services\StockTransferService;
use App\Services\StockQueryService;
use Illuminate\Support\Facades\Hash;

class StockManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Stock Management System...');

        // Create roles if they don't exist
        $this->createRoles();

        // Create branches
        $branches = $this->createBranches();

        // Create users
        $users = $this->createUsers($branches);

        // Create products
        $products = $this->createProducts();

        // Create stock transfers
        $transfers = $this->createStockTransfers($branches, $users, $products);

        // Create queries and financial impacts
        $this->createQueriesAndImpacts($transfers, $users);

        // Create alerts
        $this->createAlerts($branches, $products, $transfers);

        $this->command->info('Stock Management System seeded successfully!');
    }

    private function createRoles(): void
    {
        $roles = [
            ['name' => 'super_admin', 'display_name' => 'Super Administrator'],
            ['name' => 'admin', 'display_name' => 'Administrator'],
            ['name' => 'branch_manager', 'display_name' => 'Branch Manager'],
            ['name' => 'cashier', 'display_name' => 'Cashier'],
            ['name' => 'delivery_boy', 'display_name' => 'Delivery Boy'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }

    private function createBranches(): array
    {
        $branches = [
            [
                'name' => 'Main Warehouse',
                'code' => 'WH001',
                'address' => 'Industrial Area, Sector 5, Mumbai',
                'phone' => '022-12345678',
                'email' => 'warehouse@company.com',
                'is_active' => true,
                'outlet_type' => 'warehouse',
                'pos_enabled' => false,
            ],
            [
                'name' => 'Branch A - Andheri',
                'code' => 'BR001',
                'address' => 'Shop No. 15, Andheri West, Mumbai',
                'phone' => '022-87654321',
                'email' => 'andheri@company.com',
                'is_active' => true,
                'outlet_type' => 'retail',
                'pos_enabled' => true,
            ],
            [
                'name' => 'Branch B - Bandra',
                'code' => 'BR002',
                'address' => 'Plot 25, Bandra East, Mumbai',
                'phone' => '022-11223344',
                'email' => 'bandra@company.com',
                'is_active' => true,
                'outlet_type' => 'retail',
                'pos_enabled' => true,
            ],
            [
                'name' => 'Branch C - Pune',
                'code' => 'BR003',
                'address' => 'FC Road, Pune, Maharashtra',
                'phone' => '020-55667788',
                'email' => 'pune@company.com',
                'is_active' => true,
                'outlet_type' => 'retail',
                'pos_enabled' => true,
            ],
        ];

        $createdBranches = [];
        foreach ($branches as $branch) {
            $createdBranches[] = Branch::firstOrCreate(['code' => $branch['code']], $branch);
        }

        return $createdBranches;
    }

    private function createUsers(array $branches): array
    {
        $users = [
            [
                'name' => 'System Admin',
                'email' => 'admin@company.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'branch_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Warehouse Manager',
                'email' => 'warehouse@company.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'branch_id' => $branches[0]->id, // Main Warehouse
                'is_active' => true,
            ],
            [
                'name' => 'Andheri Branch Manager',
                'email' => 'manager.andheri@company.com',
                'password' => Hash::make('password123'),
                'role' => 'branch_manager',
                'branch_id' => $branches[1]->id,
                'is_active' => true,
            ],
            [
                'name' => 'Bandra Branch Manager',
                'email' => 'manager.bandra@company.com',
                'password' => Hash::make('password123'),
                'role' => 'branch_manager',
                'branch_id' => $branches[2]->id,
                'is_active' => true,
            ],
            [
                'name' => 'Pune Branch Manager',
                'email' => 'manager.pune@company.com',
                'password' => Hash::make('password123'),
                'role' => 'branch_manager',
                'branch_id' => $branches[3]->id,
                'is_active' => true,
            ],
        ];

        $createdUsers = [];
        foreach ($users as $userData) {
            $role = Role::where('name', $userData['role'])->first();
            unset($userData['role']);
            $userData['role_id'] = $role->id;
            
            $createdUsers[] = User::firstOrCreate(['email' => $userData['email']], $userData);
        }

        return $createdUsers;
    }

    private function createProducts(): array
    {
        $products = [
            [
                'name' => 'Fresh Apples',
                'code' => 'FRUIT001',
                'description' => 'Premium quality red apples',
                'category' => 'fruit',
                'subcategory' => 'seasonal',
                'weight_unit' => 'kg',
                'purchase_price' => 80.00,
                'mrp' => 120.00,
                'selling_price' => 100.00,
                'stock_threshold' => 50,
                'shelf_life_days' => 15,
                'is_perishable' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Fresh Bananas',
                'code' => 'FRUIT002',
                'description' => 'Ripe yellow bananas',
                'category' => 'fruit',
                'subcategory' => 'tropical',
                'weight_unit' => 'kg',
                'purchase_price' => 40.00,
                'mrp' => 70.00,
                'selling_price' => 60.00,
                'stock_threshold' => 30,
                'shelf_life_days' => 7,
                'is_perishable' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Onions',
                'code' => 'VEG001',
                'description' => 'Fresh red onions',
                'category' => 'vegetable',
                'subcategory' => 'bulb',
                'weight_unit' => 'kg',
                'purchase_price' => 30.00,
                'mrp' => 50.00,
                'selling_price' => 45.00,
                'stock_threshold' => 100,
                'shelf_life_days' => 30,
                'is_perishable' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Potatoes',
                'code' => 'VEG002',
                'description' => 'Fresh potatoes',
                'category' => 'vegetable',
                'subcategory' => 'root',
                'weight_unit' => 'kg',
                'purchase_price' => 25.00,
                'mrp' => 40.00,
                'selling_price' => 35.00,
                'stock_threshold' => 150,
                'shelf_life_days' => 45,
                'is_perishable' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Spinach',
                'code' => 'LEAFY001',
                'description' => 'Fresh green spinach',
                'category' => 'leafy',
                'subcategory' => 'greens',
                'weight_unit' => 'kg',
                'purchase_price' => 60.00,
                'mrp' => 90.00,
                'selling_price' => 80.00,
                'stock_threshold' => 20,
                'shelf_life_days' => 3,
                'is_perishable' => true,
                'is_active' => true,
            ],
        ];

        $createdProducts = [];
        foreach ($products as $product) {
            $createdProducts[] = Product::firstOrCreate(['code' => $product['code']], $product);
        }

        return $createdProducts;
    }

    private function createStockTransfers(array $branches, array $users, array $products): array
    {
        $transfers = [];
        $admin = $users[0]; // System Admin
        $warehouseBranch = $branches[0]; // Main Warehouse

        // Create transfers for each branch
        for ($i = 1; $i <= 3; $i++) {
            $toBranch = $branches[$i];
            
            // Create confirmed transfer
            $transfer1 = StockTransfer::create([
                'from_branch_id' => $warehouseBranch->id,
                'to_branch_id' => $toBranch->id,
                'initiated_by' => $admin->id,
                'status' => 'confirmed',
                'total_value' => 15000.00,
                'transport_cost' => 500.00,
                'transport_vendor' => 'ABC Transport Ltd',
                'vehicle_number' => 'MH12AB' . (1000 + $i),
                'driver_name' => 'Driver ' . $i,
                'driver_phone' => '98765432' . $i . '0',
                'dispatch_date' => now()->subDays(5),
                'expected_delivery' => now()->subDays(4),
                'delivered_date' => now()->subDays(3),
                'confirmed_date' => now()->subDays(2),
                'dispatch_notes' => 'Regular weekly stock replenishment',
            ]);

            // Add items to the transfer
            $this->addTransferItems($transfer1, $products);
            $transfers[] = $transfer1;

            // Create in-transit transfer
            $transfer2 = StockTransfer::create([
                'from_branch_id' => $warehouseBranch->id,
                'to_branch_id' => $toBranch->id,
                'initiated_by' => $admin->id,
                'status' => 'in_transit',
                'total_value' => 12000.00,
                'transport_cost' => 450.00,
                'transport_vendor' => 'XYZ Logistics',
                'vehicle_number' => 'MH14CD' . (2000 + $i),
                'driver_name' => 'Driver ' . ($i + 10),
                'driver_phone' => '87654321' . $i . '0',
                'dispatch_date' => now()->subDays(2),
                'expected_delivery' => now()->addDay(),
                'dispatch_notes' => 'Express delivery requested',
            ]);

            // Add items to the transfer
            $this->addTransferItems($transfer2, $products, 0.8); // 80% of normal quantities
            $transfers[] = $transfer2;
        }

        // Create an overdue transfer
        $overdueTransfer = StockTransfer::create([
            'from_branch_id' => $warehouseBranch->id,
            'to_branch_id' => $branches[1]->id,
            'initiated_by' => $admin->id,
            'status' => 'in_transit',
            'total_value' => 8000.00,
            'transport_cost' => 400.00,
            'transport_vendor' => 'Delayed Transport Co',
            'vehicle_number' => 'MH16EF3001',
            'driver_name' => 'Late Driver',
            'driver_phone' => '9876543210',
            'dispatch_date' => now()->subDays(7),
            'expected_delivery' => now()->subDays(3), // Overdue by 3 days
            'dispatch_notes' => 'Priority delivery - handle with care',
        ]);

        $this->addTransferItems($overdueTransfer, $products, 0.6);
        $transfers[] = $overdueTransfer;

        return $transfers;
    }

    private function addTransferItems(StockTransfer $transfer, array $products, float $multiplier = 1.0): void
    {
        foreach ($products as $product) {
            $baseQuantity = match($product->category) {
                'fruit' => 50,
                'vegetable' => 100,
                'leafy' => 20,
                default => 50,
            };

            $quantity = $baseQuantity * $multiplier;
            $unitPrice = $product->purchase_price;

            StockTransferItem::create([
                'stock_transfer_id' => $transfer->id,
                'product_id' => $product->id,
                'quantity_sent' => $quantity,
                'quantity_received' => $transfer->status === 'confirmed' ? 
                    $quantity * (0.95 + (rand(0, 10) / 100)) : null, // 95-105% variance
                'unit_price' => $unitPrice,
                'total_value' => $quantity * $unitPrice,
                'unit_of_measurement' => $product->weight_unit,
                'expiry_date' => $product->is_perishable ? 
                    now()->addDays($product->shelf_life_days) : null,
            ]);
        }
    }

    private function createQueriesAndImpacts(array $transfers, array $users): void
    {
        $branchManagers = array_slice($users, 2); // Skip admin users

        foreach ($transfers as $transfer) {
            if ($transfer->status === 'confirmed' && rand(1, 100) <= 30) { // 30% chance of having a query
                $branchManager = collect($branchManagers)->firstWhere('branch_id', $transfer->to_branch_id);
                if (!$branchManager) continue;

                $queryTypes = ['weight_difference', 'quantity_shortage', 'quality_issue', 'damaged_goods'];
                $queryType = $queryTypes[array_rand($queryTypes)];
                
                $item = $transfer->items->random();
                $expectedQty = $item->quantity_sent;
                $actualQty = $item->quantity_received;

                $query = StockTransferQuery::create([
                    'stock_transfer_id' => $transfer->id,
                    'stock_transfer_item_id' => $item->id,
                    'raised_by' => $branchManager->id,
                    'assigned_to' => $users[0]->id, // Assign to admin
                    'query_type' => $queryType,
                    'priority' => rand(1, 100) <= 20 ? 'high' : 'medium',
                    'status' => rand(1, 100) <= 70 ? 'resolved' : 'open',
                    'title' => ucfirst(str_replace('_', ' ', $queryType)) . ' in ' . $item->product->name,
                    'description' => "Discrepancy found in {$item->product->name}. Expected: {$expectedQty}kg, Received: {$actualQty}kg",
                    'expected_quantity' => $expectedQty,
                    'actual_quantity' => $actualQty,
                    'difference_quantity' => $actualQty - $expectedQty,
                    'financial_impact' => abs($actualQty - $expectedQty) * $item->unit_price,
                    'resolved_at' => rand(1, 100) <= 70 ? now()->subDays(rand(1, 5)) : null,
                ]);

                // Create financial impact record
                if ($query->financial_impact > 0) {
                    StockFinancialImpact::create([
                        'impactable_type' => StockTransferQuery::class,
                        'impactable_id' => $query->id,
                        'branch_id' => $transfer->to_branch_id,
                        'impact_type' => match($queryType) {
                            'weight_difference', 'quantity_shortage' => 'loss_shortage',
                            'quality_issue' => 'loss_quality',
                            'damaged_goods' => 'loss_damaged',
                            default => 'other',
                        },
                        'amount' => $query->financial_impact,
                        'impact_category' => 'direct_loss',
                        'description' => $query->description,
                        'impact_date' => $query->created_at->toDateString(),
                        'is_recoverable' => in_array($queryType, ['weight_difference', 'quantity_shortage']),
                        'recovered_amount' => rand(1, 100) <= 50 ? $query->financial_impact * 0.3 : 0,
                    ]);
                }
            }
        }
    }

    private function createAlerts(array $branches, array $products, array $transfers): void
    {
        // Create various types of alerts
        foreach ($branches as $branch) {
            if ($branch->code === 'WH001') continue; // Skip warehouse

            // Low stock alert
            if (rand(1, 100) <= 40) {
                StockAlert::create([
                    'branch_id' => $branch->id,
                    'product_id' => $products[array_rand($products)]->id,
                    'alert_type' => 'low_stock',
                    'severity' => 'warning',
                    'title' => 'Low Stock Alert',
                    'message' => 'Stock is running low and needs replenishment',
                    'is_read' => false,
                    'is_resolved' => false,
                ]);
            }

            // Expiry warning alert
            if (rand(1, 100) <= 30) {
                $perishableProduct = collect($products)->where('is_perishable', true)->random();
                StockAlert::create([
                    'branch_id' => $branch->id,
                    'product_id' => $perishableProduct->id,
                    'alert_type' => 'expiry_warning',
                    'severity' => 'critical',
                    'title' => 'Product Expiry Warning',
                    'message' => "Product {$perishableProduct->name} is expiring soon",
                    'is_read' => false,
                    'is_resolved' => false,
                ]);
            }
        }

        // Transfer delay alerts for overdue transfers
        foreach ($transfers as $transfer) {
            if ($transfer->isOverdue()) {
                StockAlert::create([
                    'branch_id' => $transfer->to_branch_id,
                    'stock_transfer_id' => $transfer->id,
                    'alert_type' => 'transfer_delay',
                    'severity' => 'critical',
                    'title' => 'Transfer Overdue',
                    'message' => "Transfer {$transfer->transfer_number} is overdue by " . abs($transfer->getDaysUntilDelivery()) . " days",
                    'is_read' => false,
                    'is_resolved' => false,
                ]);
            }
        }
    }
}