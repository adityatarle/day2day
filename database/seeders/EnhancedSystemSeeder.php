<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Customer;
use App\Models\WholesalePricing;
use App\Models\ExpenseCategory;

class EnhancedSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedEnhancedExpenseCategories();
        $this->seedEnhancedProducts();
        $this->seedWholesaleCustomers();
        $this->seedWholesalePricing();
        $this->seedSampleExpenseAllocations();
    }

    /**
     * Seed enhanced expense categories.
     */
    private function seedEnhancedExpenseCategories(): void
    {
        $categories = [
            [
                'name' => 'CNG Expenses',
                'code' => 'CNG',
                'description' => 'CNG fuel expenses for delivery vehicles',
                'is_active' => true,
            ],
            [
                'name' => 'Diesel Expenses',
                'code' => 'DIESEL',
                'description' => 'Diesel fuel expenses for transport vehicles',
                'is_active' => true,
            ],
            [
                'name' => 'Delivery Vehicle Maintenance',
                'code' => 'VEHICLE_MAINT',
                'description' => 'Vehicle maintenance and repair costs',
                'is_active' => true,
            ],
            [
                'name' => 'Loading Labour',
                'code' => 'LOADING_LABOUR',
                'description' => 'Labour costs for loading goods',
                'is_active' => true,
            ],
            [
                'name' => 'Unloading Labour',
                'code' => 'UNLOADING_LABOUR',
                'description' => 'Labour costs for unloading goods',
                'is_active' => true,
            ],
            [
                'name' => 'Storage Operational',
                'code' => 'STORAGE_OPS',
                'description' => 'Storage and warehouse operational costs',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::firstOrCreate(
                ['code' => $category['code']],
                $category
            );
        }
    }

    /**
     * Seed enhanced products with categories and subcategories.
     */
    private function seedEnhancedProducts(): void
    {
        $enhancedProducts = [
            // Fruits
            [
                'name' => 'Premium Alphonso Mango',
                'code' => 'MANGO-ALPHONSO',
                'category' => 'fruit',
                'subcategory' => 'tropical',
                'shelf_life_days' => 7,
                'storage_temperature' => '8-12°C',
                'is_perishable' => true,
                'hsn_code' => '08045010',
            ],
            [
                'name' => 'Imported Kiwi',
                'code' => 'KIWI-IMP',
                'category' => 'exotic',
                'subcategory' => 'imported',
                'shelf_life_days' => 14,
                'storage_temperature' => '2-4°C',
                'is_perishable' => true,
                'hsn_code' => '08105000',
            ],
            
            // Vegetables
            [
                'name' => 'Organic Broccoli',
                'code' => 'BROCCOLI-ORG',
                'category' => 'vegetable',
                'subcategory' => 'organic',
                'shelf_life_days' => 5,
                'storage_temperature' => '0-2°C',
                'is_perishable' => true,
                'hsn_code' => '07041000',
            ],
            [
                'name' => 'Baby Corn',
                'code' => 'CORN-BABY',
                'category' => 'vegetable',
                'subcategory' => 'specialty',
                'shelf_life_days' => 3,
                'storage_temperature' => '0-2°C',
                'is_perishable' => true,
                'hsn_code' => '07099990',
            ],
            
            // Leafy Vegetables
            [
                'name' => 'Organic Spinach',
                'code' => 'SPINACH-ORG',
                'category' => 'leafy',
                'subcategory' => 'organic',
                'shelf_life_days' => 2,
                'storage_temperature' => '0-1°C',
                'is_perishable' => true,
                'hsn_code' => '07039000',
            ],
            [
                'name' => 'Fresh Mint Leaves',
                'code' => 'MINT-FRESH',
                'category' => 'herbs',
                'subcategory' => 'fresh',
                'shelf_life_days' => 3,
                'storage_temperature' => '0-2°C',
                'is_perishable' => true,
                'hsn_code' => '07099990',
            ],
        ];

        foreach ($enhancedProducts as $productData) {
            Product::firstOrCreate(
                ['code' => $productData['code']],
                array_merge($productData, [
                    'description' => "Premium quality {$productData['name']}",
                    'weight_unit' => 'kg',
                    'purchase_price' => rand(50, 200),
                    'mrp' => rand(80, 300),
                    'selling_price' => rand(70, 250),
                    'stock_threshold' => 5,
                    'is_active' => true,
                ])
            );
        }
    }

    /**
     * Seed wholesale customers.
     */
    private function seedWholesaleCustomers(): void
    {
        $wholesaleCustomers = [
            [
                'name' => 'Fresh Mart Wholesale',
                'phone' => '9876543210',
                'email' => 'orders@freshmart.com',
                'address' => 'Wholesale Market, Sector 15',
                'type' => 'business',
                'customer_type' => 'regular_wholesale',
                'credit_limit' => 50000.00,
                'credit_days' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Green Valley Distributors',
                'phone' => '9876543211',
                'email' => 'purchase@greenvalley.com',
                'address' => 'Industrial Area, Phase 2',
                'type' => 'business',
                'customer_type' => 'distributor',
                'credit_limit' => 100000.00,
                'credit_days' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'City Retailers Network',
                'phone' => '9876543212',
                'email' => 'buying@cityretailers.com',
                'address' => 'Commercial Complex, Main Road',
                'type' => 'business',
                'customer_type' => 'retailer',
                'credit_limit' => 25000.00,
                'credit_days' => 7,
                'is_active' => true,
            ],
        ];

        foreach ($wholesaleCustomers as $customerData) {
            Customer::firstOrCreate(
                ['phone' => $customerData['phone']],
                $customerData
            );
        }
    }

    /**
     * Seed wholesale pricing tiers.
     */
    private function seedWholesalePricing(): void
    {
        $products = Product::take(5)->get();
        $customerTypes = ['regular_wholesale', 'premium_wholesale', 'distributor', 'retailer'];

        foreach ($products as $product) {
            foreach ($customerTypes as $customerType) {
                // Create different pricing tiers based on quantity
                $pricingTiers = [
                    [
                        'min_quantity' => 10,
                        'max_quantity' => 49,
                        'discount' => 5,
                    ],
                    [
                        'min_quantity' => 50,
                        'max_quantity' => 99,
                        'discount' => 10,
                    ],
                    [
                        'min_quantity' => 100,
                        'max_quantity' => null,
                        'discount' => 15,
                    ],
                ];

                foreach ($pricingTiers as $tier) {
                    $discountMultiplier = match($customerType) {
                        'distributor' => 1.5,
                        'premium_wholesale' => 1.2,
                        'retailer' => 0.8,
                        default => 1.0,
                    };

                    $discountPercentage = $tier['discount'] * $discountMultiplier;
                    $wholesalePrice = $product->selling_price * (1 - ($discountPercentage / 100));

                    WholesalePricing::firstOrCreate([
                        'product_id' => $product->id,
                        'customer_type' => $customerType,
                        'min_quantity' => $tier['min_quantity'],
                        'max_quantity' => $tier['max_quantity'],
                    ], [
                        'wholesale_price' => $wholesalePrice,
                        'discount_percentage' => $discountPercentage,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }

    /**
     * Seed sample expense allocations.
     */
    private function seedSampleExpenseAllocations(): void
    {
        // This would typically be done through the ExpenseController
        // Adding sample data for demonstration
        
        $transportCategory = ExpenseCategory::where('code', 'TRANSPORT')->first();
        $labourCategory = ExpenseCategory::where('code', 'LABOUR')->first();
        
        if ($transportCategory && $labourCategory) {
            DB::table('expenses')->insert([
                [
                    'expense_category_id' => $transportCategory->id,
                    'branch_id' => 1,
                    'user_id' => 1,
                    'title' => 'Daily CNG Refill',
                    'description' => 'CNG refill for delivery vehicle',
                    'amount' => 500.00,
                    'expense_date' => now()->subDays(1),
                    'payment_method' => 'cash',
                    'status' => 'paid',
                    'expense_type' => 'transport',
                    'allocation_method' => 'equal',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'expense_category_id' => $labourCategory->id,
                    'branch_id' => 1,
                    'user_id' => 1,
                    'title' => 'Loading/Unloading Labour',
                    'description' => 'Daily labour charges for goods handling',
                    'amount' => 800.00,
                    'expense_date' => now()->subDays(1),
                    'payment_method' => 'cash',
                    'status' => 'paid',
                    'expense_type' => 'labour',
                    'allocation_method' => 'weighted',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}