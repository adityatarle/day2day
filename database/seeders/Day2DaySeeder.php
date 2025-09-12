<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Branch;
use App\Models\City;
use App\Models\Product;
use App\Models\CityProductPricing;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class Day2DaySeeder extends Seeder
{
    /**
     * Run the database seeds for Day2Day company.
     */
    public function run(): void
    {
        $this->command->info('Setting up Day2Day multi-branch company system...');

        // Create Day2Day specific roles if needed
        $this->setupDay2DayRoles();
        
        // Create cities where Day2Day operates
        $this->setupDay2DayCities();
        
        // Create Day2Day branches
        $this->setupDay2DayBranches();
        
        // Create Day2Day users
        $this->setupDay2DayUsers();
        
        // Create Day2Day products with city-specific pricing
        $this->setupDay2DayProducts();
        
        $this->command->info('Day2Day system setup completed successfully!');
        $this->displayDay2DayCredentials();
    }

    private function setupDay2DayRoles()
    {
        $this->command->info('Setting up Day2Day roles...');

        // Add admin role for main branch admin
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Main Branch Admin',
                'description' => 'Main branch administrator who manages all branches and supplies materials',
                'is_active' => true
            ]
        );

        // Get all permissions for admin role
        $permissions = Permission::all();
        $adminRole->permissions()->sync($permissions);
    }

    private function setupDay2DayCities()
    {
        $this->command->info('Setting up Day2Day cities...');

        $cities = [
            ['name' => 'Mumbai', 'state' => 'Maharashtra', 'code' => 'MUM', 'is_active' => true],
            ['name' => 'Delhi', 'state' => 'Delhi', 'code' => 'DEL', 'is_active' => true],
            ['name' => 'Bangalore', 'state' => 'Karnataka', 'code' => 'BLR', 'is_active' => true],
            ['name' => 'Pune', 'state' => 'Maharashtra', 'code' => 'PUN', 'is_active' => true],
            ['name' => 'Chennai', 'state' => 'Tamil Nadu', 'code' => 'CHN', 'is_active' => true],
            ['name' => 'Hyderabad', 'state' => 'Telangana', 'code' => 'HYD', 'is_active' => true],
            ['name' => 'Kolkata', 'state' => 'West Bengal', 'code' => 'KOL', 'is_active' => true],
            ['name' => 'Ahmedabad', 'state' => 'Gujarat', 'code' => 'AMD', 'is_active' => true],
        ];

        foreach ($cities as $city) {
            City::firstOrCreate(['name' => $city['name']], $city);
        }
    }

    private function setupDay2DayBranches()
    {
        $this->command->info('Setting up Day2Day branches...');

        $mumbai = City::where('name', 'Mumbai')->first();
        $delhi = City::where('name', 'Delhi')->first();
        $bangalore = City::where('name', 'Bangalore')->first();
        $pune = City::where('name', 'Pune')->first();
        $chennai = City::where('name', 'Chennai')->first();
        $hyderabad = City::where('name', 'Hyderabad')->first();
        $kolkata = City::where('name', 'Kolkata')->first();
        $ahmedabad = City::where('name', 'Ahmedabad')->first();

        $branches = [
            // Main Branch (Head Office)
            [
                'name' => 'Day2Day Main Branch (Head Office)',
                'code' => 'D2D-MAIN',
                'address' => 'Corporate Office, Business District, Mumbai',
                'phone' => '+91-9999000001',
                'email' => 'main@day2day.com',
                'city_id' => $mumbai->id,
                'outlet_type' => 'warehouse',
                'pos_enabled' => true,
                'pos_terminal_id' => 'POS-D2D-MAIN',
                'is_active' => true,
            ],
            
            // Branch Locations
            [
                'name' => 'Day2Day Mumbai Branch',
                'code' => 'D2D-MUM-001',
                'address' => 'Shop 15, Market Plaza, Andheri, Mumbai',
                'phone' => '+91-9999000002',
                'email' => 'mumbai@day2day.com',
                'city_id' => $mumbai->id,
                'outlet_type' => 'retail',
                'pos_enabled' => true,
                'pos_terminal_id' => 'POS-D2D-MUM-001',
                'is_active' => true,
            ],
            [
                'name' => 'Day2Day Delhi Branch',
                'code' => 'D2D-DEL-001',
                'address' => 'Unit 12, Central Market, Karol Bagh, Delhi',
                'phone' => '+91-9999000003',
                'email' => 'delhi@day2day.com',
                'city_id' => $delhi->id,
                'outlet_type' => 'retail',
                'pos_enabled' => true,
                'pos_terminal_id' => 'POS-D2D-DEL-001',
                'is_active' => true,
            ],
            [
                'name' => 'Day2Day Bangalore Branch',
                'code' => 'D2D-BLR-001',
                'address' => 'Store 8, Commercial Street, Bangalore',
                'phone' => '+91-9999000004',
                'email' => 'bangalore@day2day.com',
                'city_id' => $bangalore->id,
                'outlet_type' => 'retail',
                'pos_enabled' => true,
                'pos_terminal_id' => 'POS-D2D-BLR-001',
                'is_active' => true,
            ],
            [
                'name' => 'Day2Day Pune Branch',
                'code' => 'D2D-PUN-001',
                'address' => 'Shop 25, FC Road, Pune',
                'phone' => '+91-9999000005',
                'email' => 'pune@day2day.com',
                'city_id' => $pune->id,
                'outlet_type' => 'retail',
                'pos_enabled' => true,
                'pos_terminal_id' => 'POS-D2D-PUN-001',
                'is_active' => true,
            ],
            [
                'name' => 'Day2Day Chennai Branch',
                'code' => 'D2D-CHN-001',
                'address' => 'No. 45, T. Nagar, Chennai',
                'phone' => '+91-9999000006',
                'email' => 'chennai@day2day.com',
                'city_id' => $chennai->id,
                'outlet_type' => 'retail',
                'pos_enabled' => true,
                'pos_terminal_id' => 'POS-D2D-CHN-001',
                'is_active' => true,
            ],
            [
                'name' => 'Day2Day Hyderabad Branch',
                'code' => 'D2D-HYD-001',
                'address' => 'Shop 18, Abids, Hyderabad',
                'phone' => '+91-9999000007',
                'email' => 'hyderabad@day2day.com',
                'city_id' => $hyderabad->id,
                'outlet_type' => 'retail',
                'pos_enabled' => true,
                'pos_terminal_id' => 'POS-D2D-HYD-001',
                'is_active' => true,
            ],
            [
                'name' => 'Day2Day Kolkata Branch',
                'code' => 'D2D-KOL-001',
                'address' => 'Store 22, Park Street, Kolkata',
                'phone' => '+91-9999000008',
                'email' => 'kolkata@day2day.com',
                'city_id' => $kolkata->id,
                'outlet_type' => 'retail',
                'pos_enabled' => true,
                'pos_terminal_id' => 'POS-D2D-KOL-001',
                'is_active' => true,
            ],
            [
                'name' => 'Day2Day Ahmedabad Branch',
                'code' => 'D2D-AMD-001',
                'address' => 'Shop 9, CG Road, Ahmedabad',
                'phone' => '+91-9999000009',
                'email' => 'ahmedabad@day2day.com',
                'city_id' => $ahmedabad->id,
                'outlet_type' => 'retail',
                'pos_enabled' => true,
                'pos_terminal_id' => 'POS-D2D-AMD-001',
                'is_active' => true,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::firstOrCreate(['code' => $branch['code']], $branch);
        }
    }

    private function setupDay2DayUsers()
    {
        $this->command->info('Setting up Day2Day users...');

        $adminRole = Role::where('name', 'admin')->first();
        $branchManagerRole = Role::where('name', 'branch_manager')->first();
        $cashierRole = Role::where('name', 'cashier')->first();

        $mainBranch = Branch::where('code', 'D2D-MAIN')->first();
        $mumbaiBranch = Branch::where('code', 'D2D-MUM-001')->first();
        $delhiBranch = Branch::where('code', 'D2D-DEL-001')->first();
        $bangaloreBranch = Branch::where('code', 'D2D-BLR-001')->first();
        $puneBranch = Branch::where('code', 'D2D-PUN-001')->first();
        $chennaiBranch = Branch::where('code', 'D2D-CHN-001')->first();
        $hyderabadBranch = Branch::where('code', 'D2D-HYD-001')->first();
        $kolkataBranch = Branch::where('code', 'D2D-KOL-001')->first();
        $ahmedabadBranch = Branch::where('code', 'D2D-AMD-001')->first();

        $users = [
            // Main Branch Admin
            [
                'name' => 'Day2Day Admin',
                'email' => 'admin@day2day.com',
                'phone' => '+91-9999000001',
                'address' => 'Corporate Office, Mumbai',
                'password' => Hash::make('admin123'),
                'role_id' => $adminRole->id,
                'branch_id' => $mainBranch->id,
                'is_active' => true,
            ],
            
            // Branch Managers for each city
            [
                'name' => 'Mumbai Branch Manager',
                'email' => 'manager.mumbai@day2day.com',
                'phone' => '+91-9999000002',
                'address' => 'Mumbai Branch Office',
                'password' => Hash::make('manager123'),
                'role_id' => $branchManagerRole->id,
                'branch_id' => $mumbaiBranch->id,
                'is_active' => true,
            ],
            [
                'name' => 'Delhi Branch Manager',
                'email' => 'manager.delhi@day2day.com',
                'phone' => '+91-9999000003',
                'address' => 'Delhi Branch Office',
                'password' => Hash::make('manager123'),
                'role_id' => $branchManagerRole->id,
                'branch_id' => $delhiBranch->id,
                'is_active' => true,
            ],
            [
                'name' => 'Bangalore Branch Manager',
                'email' => 'manager.bangalore@day2day.com',
                'phone' => '+91-9999000004',
                'address' => 'Bangalore Branch Office',
                'password' => Hash::make('manager123'),
                'role_id' => $branchManagerRole->id,
                'branch_id' => $bangaloreBranch->id,
                'is_active' => true,
            ],
            [
                'name' => 'Pune Branch Manager',
                'email' => 'manager.pune@day2day.com',
                'phone' => '+91-9999000005',
                'address' => 'Pune Branch Office',
                'password' => Hash::make('manager123'),
                'role_id' => $branchManagerRole->id,
                'branch_id' => $puneBranch->id,
                'is_active' => true,
            ],
            [
                'name' => 'Chennai Branch Manager',
                'email' => 'manager.chennai@day2day.com',
                'phone' => '+91-9999000006',
                'address' => 'Chennai Branch Office',
                'password' => Hash::make('manager123'),
                'role_id' => $branchManagerRole->id,
                'branch_id' => $chennaiBranch->id,
                'is_active' => true,
            ],
            [
                'name' => 'Hyderabad Branch Manager',
                'email' => 'manager.hyderabad@day2day.com',
                'phone' => '+91-9999000007',
                'address' => 'Hyderabad Branch Office',
                'password' => Hash::make('manager123'),
                'role_id' => $branchManagerRole->id,
                'branch_id' => $hyderabadBranch->id,
                'is_active' => true,
            ],
            [
                'name' => 'Kolkata Branch Manager',
                'email' => 'manager.kolkata@day2day.com',
                'phone' => '+91-9999000008',
                'address' => 'Kolkata Branch Office',
                'password' => Hash::make('manager123'),
                'role_id' => $branchManagerRole->id,
                'branch_id' => $kolkataBranch->id,
                'is_active' => true,
            ],
            [
                'name' => 'Ahmedabad Branch Manager',
                'email' => 'manager.ahmedabad@day2day.com',
                'phone' => '+91-9999000009',
                'address' => 'Ahmedabad Branch Office',
                'password' => Hash::make('manager123'),
                'role_id' => $branchManagerRole->id,
                'branch_id' => $ahmedabadBranch->id,
                'is_active' => true,
            ],
            
            // Sample Cashiers for few branches
            [
                'name' => 'Mumbai Cashier',
                'email' => 'cashier.mumbai@day2day.com',
                'phone' => '+91-9999001002',
                'address' => 'Mumbai Branch',
                'password' => Hash::make('cashier123'),
                'role_id' => $cashierRole->id,
                'branch_id' => $mumbaiBranch->id,
                'is_active' => true,
            ],
            [
                'name' => 'Delhi Cashier',
                'email' => 'cashier.delhi@day2day.com',
                'phone' => '+91-9999001003',
                'address' => 'Delhi Branch',
                'password' => Hash::make('cashier123'),
                'role_id' => $cashierRole->id,
                'branch_id' => $delhiBranch->id,
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(['email' => $user['email']], $user);
        }
    }

    private function setupDay2DayProducts()
    {
        $this->command->info('Setting up Day2Day products with city-specific pricing...');

        // Sample Day2Day products (using existing schema structure)
        $products = [
            [
                'name' => 'Daily Essentials Kit',
                'code' => 'D2D-ESS-001',
                'description' => 'Complete daily essentials package',
                'category' => 'fruit', // Using existing enum values
                'weight_unit' => 'pcs',
                'purchase_price' => 150.00,
                'selling_price' => 200.00,
                'mrp' => 250.00,
                'stock_threshold' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Premium Grocery Bundle',
                'code' => 'D2D-GRO-001',
                'description' => 'Premium grocery items bundle',
                'category' => 'vegetable',
                'weight_unit' => 'pcs',
                'purchase_price' => 300.00,
                'selling_price' => 400.00,
                'mrp' => 500.00,
                'stock_threshold' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Health & Wellness Pack',
                'code' => 'D2D-HW-001',
                'description' => 'Health and wellness products',
                'category' => 'leafy',
                'weight_unit' => 'pcs',
                'purchase_price' => 250.00,
                'selling_price' => 350.00,
                'mrp' => 450.00,
                'stock_threshold' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Home Care Essentials',
                'code' => 'D2D-HC-001',
                'description' => 'Home care and cleaning products',
                'category' => 'exotic',
                'weight_unit' => 'pcs',
                'purchase_price' => 180.00,
                'selling_price' => 240.00,
                'mrp' => 300.00,
                'stock_threshold' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Personal Care Kit',
                'code' => 'D2D-PC-001',
                'description' => 'Personal care and hygiene products',
                'category' => 'fruit',
                'weight_unit' => 'pcs',
                'purchase_price' => 200.00,
                'selling_price' => 280.00,
                'mrp' => 350.00,
                'stock_threshold' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::firstOrCreate(
                ['code' => $productData['code']], 
                $productData
            );

            // Create city-specific pricing
            $this->createCitySpecificPricing($product);
        }
    }

    private function createCitySpecificPricing($product)
    {
        $cities = City::all();
        
        // City-specific pricing multipliers based on cost of living
        $cityMultipliers = [
            'MUM' => 1.25,  // Mumbai - highest cost
            'DEL' => 1.20,  // Delhi - high cost
            'BLR' => 1.15,  // Bangalore - high cost
            'CHN' => 1.10,  // Chennai - medium-high cost
            'PUN' => 1.05,  // Pune - medium cost
            'HYD' => 1.00,  // Hyderabad - base cost
            'KOL' => 0.95,  // Kolkata - lower cost
            'AMD' => 0.90,  // Ahmedabad - lowest cost
        ];

        foreach ($cities as $city) {
            $multiplier = $cityMultipliers[$city->code] ?? 1.0;
            
            CityProductPricing::firstOrCreate(
                [
                    'city_id' => $city->id,
                    'product_id' => $product->id,
                ],
                [
                    'selling_price' => round($product->selling_price * $multiplier, 2),
                    'mrp' => round($product->mrp * $multiplier, 2),
                    'discount_percentage' => 0,
                    'is_available' => true,
                    'effective_from' => now()->startOfDay(),
                    'effective_until' => null,
                ]
            );
        }
    }

    private function displayDay2DayCredentials()
    {
        $this->command->info('');
        $this->command->info('=== DAY2DAY LOGIN CREDENTIALS ===');
        $this->command->info('');
        $this->command->info('🏢 Main Branch Admin (Supplies to all branches):');
        $this->command->info('Email: admin@day2day.com');
        $this->command->info('Password: admin123');
        $this->command->info('');
        $this->command->info('🏪 Branch Managers (Purchase entries & POS):');
        $this->command->info('Mumbai: manager.mumbai@day2day.com / manager123');
        $this->command->info('Delhi: manager.delhi@day2day.com / manager123');
        $this->command->info('Bangalore: manager.bangalore@day2day.com / manager123');
        $this->command->info('Pune: manager.pune@day2day.com / manager123');
        $this->command->info('Chennai: manager.chennai@day2day.com / manager123');
        $this->command->info('Hyderabad: manager.hyderabad@day2day.com / manager123');
        $this->command->info('Kolkata: manager.kolkata@day2day.com / manager123');
        $this->command->info('Ahmedabad: manager.ahmedabad@day2day.com / manager123');
        $this->command->info('');
        $this->command->info('💰 Sample Cashiers (POS operations):');
        $this->command->info('Mumbai: cashier.mumbai@day2day.com / cashier123');
        $this->command->info('Delhi: cashier.delhi@day2day.com / cashier123');
        $this->command->info('');
        $this->command->info('🌟 Features Available:');
        $this->command->info('• Admin can supply materials to branches');
        $this->command->info('• Branches can record purchase entries');
        $this->command->info('• Branch-specific inventory & sales tracking');
        $this->command->info('• City-specific product pricing');
        $this->command->info('• POS system for each branch');
        $this->command->info('• Damage/wastage tracking');
        $this->command->info('• Comprehensive reporting');
        $this->command->info('');
        $this->command->info('=====================================');
    }
}