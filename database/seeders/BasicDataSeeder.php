<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class BasicDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles first
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'System Administrator',
                'description' => 'Full system access',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'branch_manager',
                'display_name' => 'Branch Manager',
                'description' => 'Branch management access',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'cashier',
                'display_name' => 'Cashier',
                'description' => 'Sales and basic operations',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('roles')->insert($roles);

        // Create branches
        $branches = [
            [
                'name' => 'Main Branch',
                'code' => 'MB001',
                'address' => '123 Main Street, City Center',
                'phone' => '+91-9876543210',
                'email' => 'main@foodco.com',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'North Branch',
                'code' => 'NB001',
                'address' => '456 North Avenue, North City',
                'phone' => '+91-9876543211',
                'email' => 'north@foodco.com',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'South Branch',
                'code' => 'SB001',
                'address' => '789 South Road, South City',
                'phone' => '+91-9876543212',
                'email' => 'south@foodco.com',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('branches')->insert($branches);

        // Create users
        $users = [
            [
                'name' => 'System Administrator',
                'email' => 'admin@foodco.com',
                'phone' => '+91-9876543200',
                'password' => Hash::make('password'),
                'role_id' => 1, // admin
                'branch_id' => null,
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Branch Manager',
                'email' => 'manager@foodco.com',
                'phone' => '+91-9876543201',
                'password' => Hash::make('password'),
                'role_id' => 2, // branch_manager
                'branch_id' => 1, // Main Branch
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cashier',
                'email' => 'cashier@foodco.com',
                'phone' => '+91-9876543202',
                'password' => Hash::make('password'),
                'role_id' => 3, // cashier
                'branch_id' => 1, // Main Branch
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);

        // Create products
        $products = [
            [
                'name' => 'Fresh Apples',
                'code' => 'APP001',
                'description' => 'Sweet and juicy red apples',
                'category' => 'fruit',
                'weight_unit' => 'kg',
                'purchase_price' => 120.00,
                'mrp' => 150.00,
                'selling_price' => 150.00,
                'stock_threshold' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fresh Tomatoes',
                'code' => 'TOM001',
                'description' => 'Ripe red tomatoes',
                'category' => 'vegetable',
                'weight_unit' => 'kg',
                'purchase_price' => 40.00,
                'mrp' => 60.00,
                'selling_price' => 60.00,
                'stock_threshold' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fresh Spinach',
                'code' => 'SPI001',
                'description' => 'Organic spinach leaves',
                'category' => 'leafy',
                'weight_unit' => 'kg',
                'purchase_price' => 30.00,
                'mrp' => 50.00,
                'selling_price' => 50.00,
                'stock_threshold' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fresh Oranges',
                'code' => 'ORA001',
                'description' => 'Sweet oranges',
                'category' => 'fruit',
                'weight_unit' => 'kg',
                'purchase_price' => 80.00,
                'mrp' => 100.00,
                'selling_price' => 100.00,
                'stock_threshold' => 8,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fresh Carrots',
                'code' => 'CAR001',
                'description' => 'Fresh orange carrots',
                'category' => 'vegetable',
                'weight_unit' => 'kg',
                'purchase_price' => 35.00,
                'mrp' => 55.00,
                'selling_price' => 55.00,
                'stock_threshold' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('products')->insert($products);

        // Create product branches (inventory)
        $productBranches = [
            [
                'product_id' => 1,
                'branch_id' => 1,
                'selling_price' => 150.00,
                'current_stock' => 50.0,
                'is_available_online' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => 1,
                'branch_id' => 2,
                'selling_price' => 150.00,
                'current_stock' => 30.0,
                'is_available_online' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => 2,
                'branch_id' => 1,
                'selling_price' => 60.00,
                'current_stock' => 25.0,
                'is_available_online' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => 3,
                'branch_id' => 1,
                'selling_price' => 50.00,
                'current_stock' => 15.0,
                'is_available_online' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => 4,
                'branch_id' => 2,
                'selling_price' => 100.00,
                'current_stock' => 40.0,
                'is_available_online' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => 5,
                'branch_id' => 3,
                'selling_price' => 55.00,
                'current_stock' => 20.0,
                'is_available_online' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('product_branches')->insert($productBranches);

        // Create customers
        $customers = [
            [
                'name' => 'John Doe',
                'phone' => '+91-9876543201',
                'email' => 'john@example.com',
                'address' => '123 Customer Street, City',
                'type' => 'retail',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jane Smith',
                'phone' => '+91-9876543202',
                'email' => 'jane@example.com',
                'address' => '456 Customer Avenue, City',
                'type' => 'retail',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bob Johnson',
                'phone' => '+91-9876543203',
                'email' => 'bob@example.com',
                'address' => '789 Customer Road, City',
                'type' => 'wholesale',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('customers')->insert($customers);

        // Create vendors
        $vendors = [
            [
                'name' => 'Fresh Farms Ltd',
                'code' => 'V001',
                'email' => 'contact@freshfarms.com',
                'phone' => '+91-9876543301',
                'address' => '123 Vendor Street, City',
                'gst_number' => 'GST123456789',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Green Produce Co',
                'code' => 'V002',
                'email' => 'info@greenproduce.com',
                'phone' => '+91-9876543302',
                'address' => '456 Vendor Avenue, City',
                'gst_number' => 'GST987654321',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('vendors')->insert($vendors);

        // Map products to vendors with supply prices (for PO product selection)
        $productVendors = [
            // Apples
            [
                'product_id' => 1,
                'vendor_id' => 1,
                'supply_price' => 120.00,
                'is_primary_supplier' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Tomatoes
            [
                'product_id' => 2,
                'vendor_id' => 2,
                'supply_price' => 40.00,
                'is_primary_supplier' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Spinach
            [
                'product_id' => 3,
                'vendor_id' => 1,
                'supply_price' => 30.00,
                'is_primary_supplier' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Oranges
            [
                'product_id' => 4,
                'vendor_id' => 2,
                'supply_price' => 80.00,
                'is_primary_supplier' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Carrots
            [
                'product_id' => 5,
                'vendor_id' => 1,
                'supply_price' => 35.00,
                'is_primary_supplier' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('product_vendors')->insert($productVendors);

        // Create orders
        $orders = [
            [
                'order_number' => 'ORD001',
                'customer_id' => 1,
                'branch_id' => 1,
                'user_id' => 3, // cashier
                'order_type' => 'on_shop',
                'status' => 'delivered',
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'subtotal' => 250.00,
                'tax_amount' => 0.00,
                'discount_amount' => 0.00,
                'total_amount' => 250.00,
                'notes' => 'Fresh produce order',
                'order_date' => now(),
                'delivery_date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_number' => 'ORD002',
                'customer_id' => 2,
                'branch_id' => 2,
                'user_id' => 2, // branch manager
                'order_type' => 'on_shop',
                'status' => 'delivered',
                'payment_method' => 'card',
                'payment_status' => 'paid',
                'subtotal' => 180.00,
                'tax_amount' => 0.00,
                'discount_amount' => 0.00,
                'total_amount' => 180.00,
                'notes' => 'Mixed vegetables order',
                'order_date' => now()->subDays(1),
                'delivery_date' => now()->subDays(1),
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'order_number' => 'ORD003',
                'customer_id' => 3,
                'branch_id' => 1,
                'user_id' => 3, // cashier
                'order_type' => 'on_shop',
                'status' => 'delivered',
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'subtotal' => 320.00,
                'tax_amount' => 0.00,
                'discount_amount' => 0.00,
                'total_amount' => 320.00,
                'notes' => 'Large fruit order',
                'order_date' => now()->subDays(2),
                'delivery_date' => now()->subDays(2),
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
        ];

        DB::table('orders')->insert($orders);

        // Create order items
        $orderItems = [
            [
                'order_id' => 1,
                'product_id' => 1,
                'batch_id' => null,
                'quantity' => 1.5,
                'unit_price' => 150.00,
                'total_price' => 225.00,
                'actual_weight' => 1.5,
                'billed_weight' => 1.5,
                'adjustment_weight' => 0.0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => 1,
                'product_id' => 2,
                'batch_id' => null,
                'quantity' => 0.5,
                'unit_price' => 60.00,
                'total_price' => 30.00,
                'actual_weight' => 0.5,
                'billed_weight' => 0.5,
                'adjustment_weight' => 0.0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => 2,
                'product_id' => 3,
                'batch_id' => null,
                'quantity' => 1.0,
                'unit_price' => 50.00,
                'total_price' => 50.00,
                'actual_weight' => 1.0,
                'billed_weight' => 1.0,
                'adjustment_weight' => 0.0,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'order_id' => 2,
                'product_id' => 4,
                'batch_id' => null,
                'quantity' => 1.3,
                'unit_price' => 100.00,
                'total_price' => 130.00,
                'actual_weight' => 1.3,
                'billed_weight' => 1.3,
                'adjustment_weight' => 0.0,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'order_id' => 3,
                'product_id' => 1,
                'batch_id' => null,
                'quantity' => 2.0,
                'unit_price' => 150.00,
                'total_price' => 300.00,
                'actual_weight' => 2.0,
                'billed_weight' => 2.0,
                'adjustment_weight' => 0.0,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'order_id' => 3,
                'product_id' => 5,
                'batch_id' => null,
                'quantity' => 0.4,
                'unit_price' => 55.00,
                'total_price' => 22.00,
                'actual_weight' => 0.4,
                'billed_weight' => 0.4,
                'adjustment_weight' => 0.0,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
        ];

        DB::table('order_items')->insert($orderItems);

        // Create purchase orders
        $purchaseOrders = [
            [
                'po_number' => 'PO001',
                'vendor_id' => 1,
                'branch_id' => 1,
                'user_id' => 2, // branch manager
                'status' => 'sent',
                'payment_terms' => '15_days',
                'subtotal' => 5000.00,
                'tax_amount' => 0.00,
                'transport_cost' => 0.00,
                'total_amount' => 5000.00,
                'notes' => 'Fresh produce order',
                'expected_delivery_date' => now()->addDays(2),
                'actual_delivery_date' => null,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'po_number' => 'PO002',
                'vendor_id' => 2,
                'branch_id' => 2,
                'user_id' => 2, // branch manager
                'status' => 'confirmed',
                'payment_terms' => '7_days',
                'subtotal' => 3500.00,
                'tax_amount' => 0.00,
                'transport_cost' => 0.00,
                'total_amount' => 3500.00,
                'notes' => 'Mixed vegetables order',
                'expected_delivery_date' => now()->addDays(1),
                'actual_delivery_date' => null,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
        ];

        DB::table('purchase_orders')->insert($purchaseOrders);

        // Create expense categories
        $expenseCategories = [
            [
                'name' => 'Utilities',
                'code' => 'UTIL',
                'description' => 'Utility expenses like electricity, water, etc.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Maintenance',
                'code' => 'MAINT',
                'description' => 'Maintenance and repair expenses',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('expense_categories')->insert($expenseCategories);

        // Create expenses
        $expenses = [
            [
                'expense_category_id' => 1, // Utilities
                'branch_id' => 1,
                'user_id' => 1, // admin
                'title' => 'Electricity Bill',
                'description' => 'Monthly electricity bill for Main Branch',
                'amount' => 1500.00,
                'expense_date' => now()->subDays(10),
                'payment_method' => 'bank',
                'reference_number' => 'ELEC001',
                'status' => 'paid',
                'notes' => 'Regular monthly expense',
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ],
            [
                'expense_category_id' => 2, // Maintenance
                'branch_id' => 1,
                'user_id' => 1, // admin
                'title' => 'Equipment Repair',
                'description' => 'Repair of refrigeration equipment',
                'amount' => 800.00,
                'expense_date' => now()->subDays(15),
                'payment_method' => 'cash',
                'reference_number' => 'REPAIR001',
                'status' => 'paid',
                'notes' => 'Emergency repair work',
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(15),
            ],
        ];

        DB::table('expenses')->insert($expenses);
    }
}
