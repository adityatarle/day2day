<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use App\Models\City;
use Illuminate\Support\Facades\Hash;

class LoginSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create cities first
        $cities = [
            ['name' => 'Downtown', 'state' => 'State1', 'postal_code' => '12345', 'code' => 'DT'],
            ['name' => 'Uptown', 'state' => 'State1', 'postal_code' => '12346', 'code' => 'UT'],
            ['name' => 'Suburb', 'state' => 'State2', 'postal_code' => '54321', 'code' => 'SB'],
        ];

        foreach ($cities as $cityData) {
            City::firstOrCreate(['name' => $cityData['name']], $cityData);
        }

        // Create roles
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full system access and management',
                'is_active' => true,
            ],
            [
                'name' => 'branch_manager',
                'display_name' => 'Branch Manager',
                'description' => 'Manage branch operations and staff',
                'is_active' => true,
            ],
            [
                'name' => 'cashier',
                'display_name' => 'Cashier',
                'description' => 'POS system and daily operations',
                'is_active' => true,
            ],
            [
                'name' => 'delivery_boy',
                'display_name' => 'Delivery Staff',
                'description' => 'Delivery operations and tracking',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(['name' => $roleData['name']], $roleData);
        }

        // Get role IDs
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'branch_manager')->first();
        $cashierRole = Role::where('name', 'cashier')->first();
        $deliveryRole = Role::where('name', 'delivery_boy')->first();

        // Create branches with outlet codes
        $branches = [
            [
                'name' => 'Main Branch',
                'code' => 'FDC001',
                'address' => '123 Main Street, Downtown',
                'phone' => '(555) 123-4567',
                'email' => 'main@foodcompany.com',
                'is_active' => true,
                'city_id' => City::where('name', 'Downtown')->first()->id,
                'outlet_type' => 'restaurant',
                'pos_enabled' => true,
                'pos_terminal_id' => 'POS001',
                'operating_hours' => [
                    'monday' => ['open' => '09:00', 'close' => '22:00'],
                    'tuesday' => ['open' => '09:00', 'close' => '22:00'],
                    'wednesday' => ['open' => '09:00', 'close' => '22:00'],
                    'thursday' => ['open' => '09:00', 'close' => '22:00'],
                    'friday' => ['open' => '09:00', 'close' => '23:00'],
                    'saturday' => ['open' => '10:00', 'close' => '23:00'],
                    'sunday' => ['open' => '10:00', 'close' => '21:00'],
                ],
            ],
            [
                'name' => 'Downtown Branch',
                'code' => 'FDC002',
                'address' => '456 Business Ave, Downtown',
                'phone' => '(555) 234-5678',
                'email' => 'downtown@foodcompany.com',
                'is_active' => true,
                'city_id' => City::where('name', 'Downtown')->first()->id,
                'outlet_type' => 'quick_service',
                'pos_enabled' => true,
                'pos_terminal_id' => 'POS002',
                'operating_hours' => [
                    'monday' => ['open' => '08:00', 'close' => '20:00'],
                    'tuesday' => ['open' => '08:00', 'close' => '20:00'],
                    'wednesday' => ['open' => '08:00', 'close' => '20:00'],
                    'thursday' => ['open' => '08:00', 'close' => '20:00'],
                    'friday' => ['open' => '08:00', 'close' => '21:00'],
                    'saturday' => ['open' => '09:00', 'close' => '21:00'],
                    'sunday' => ['open' => '09:00', 'close' => '19:00'],
                ],
            ],
            [
                'name' => 'Uptown Express',
                'code' => 'FDC003',
                'address' => '789 Uptown Plaza, Uptown',
                'phone' => '(555) 345-6789',
                'email' => 'uptown@foodcompany.com',
                'is_active' => true,
                'city_id' => City::where('name', 'Uptown')->first()->id,
                'outlet_type' => 'food_court',
                'pos_enabled' => true,
                'pos_terminal_id' => 'POS003',
                'operating_hours' => [
                    'monday' => ['open' => '10:00', 'close' => '22:00'],
                    'tuesday' => ['open' => '10:00', 'close' => '22:00'],
                    'wednesday' => ['open' => '10:00', 'close' => '22:00'],
                    'thursday' => ['open' => '10:00', 'close' => '22:00'],
                    'friday' => ['open' => '10:00', 'close' => '23:00'],
                    'saturday' => ['open' => '10:00', 'close' => '23:00'],
                    'sunday' => ['open' => '11:00', 'close' => '21:00'],
                ],
            ],
        ];

        foreach ($branches as $branchData) {
            Branch::firstOrCreate(['code' => $branchData['code']], $branchData);
        }

        // Get branch IDs
        $mainBranch = Branch::where('code', 'FDC001')->first();
        $downtownBranch = Branch::where('code', 'FDC002')->first();
        $uptownBranch = Branch::where('code', 'FDC003')->first();

        // Create users
        $users = [
            // Admin Users
            [
                'name' => 'System Administrator',
                'email' => 'admin@foodcompany.com',
                'password' => Hash::make('admin123'),
                'phone' => '(555) 100-0001',
                'address' => 'Head Office, 100 Corporate Blvd',
                'role_id' => $adminRole->id,
                'branch_id' => null, // Admins are not tied to specific branches
                'is_active' => true,
            ],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@foodcompany.com',
                'password' => Hash::make('super123'),
                'phone' => '(555) 100-0002',
                'address' => 'Head Office, 100 Corporate Blvd',
                'role_id' => $adminRole->id,
                'branch_id' => null,
                'is_active' => true,
            ],

            // Branch Managers
            [
                'name' => 'John Manager',
                'email' => 'manager@foodcompany.com',
                'password' => Hash::make('manager123'),
                'phone' => '(555) 200-0001',
                'address' => '123 Main Street, Downtown',
                'role_id' => $managerRole->id,
                'branch_id' => $mainBranch->id,
                'is_active' => true,
            ],
            [
                'name' => 'Sarah Downtown',
                'email' => 'manager2@foodcompany.com',
                'password' => Hash::make('manager123'),
                'phone' => '(555) 200-0002',
                'address' => '456 Business Ave, Downtown',
                'role_id' => $managerRole->id,
                'branch_id' => $downtownBranch->id,
                'is_active' => true,
            ],
            [
                'name' => 'Mike Uptown',
                'email' => 'manager3@foodcompany.com',
                'password' => Hash::make('manager123'),
                'phone' => '(555) 200-0003',
                'address' => '789 Uptown Plaza, Uptown',
                'role_id' => $managerRole->id,
                'branch_id' => $uptownBranch->id,
                'is_active' => true,
            ],

            // Cashiers
            [
                'name' => 'Alice Cashier',
                'email' => 'cashier@foodcompany.com',
                'password' => Hash::make('cashier123'),
                'phone' => '(555) 300-0001',
                'address' => '123 Main Street, Downtown',
                'role_id' => $cashierRole->id,
                'branch_id' => $mainBranch->id,
                'is_active' => true,
            ],
            [
                'name' => 'Bob Counter',
                'email' => 'cashier2@foodcompany.com',
                'password' => Hash::make('cashier123'),
                'phone' => '(555) 300-0002',
                'address' => '456 Business Ave, Downtown',
                'role_id' => $cashierRole->id,
                'branch_id' => $downtownBranch->id,
                'is_active' => true,
            ],
            [
                'name' => 'Carol POS',
                'email' => 'cashier3@foodcompany.com',
                'password' => Hash::make('cashier123'),
                'phone' => '(555) 300-0003',
                'address' => '789 Uptown Plaza, Uptown',
                'role_id' => $cashierRole->id,
                'branch_id' => $uptownBranch->id,
                'is_active' => true,
            ],
            [
                'name' => 'David Sales',
                'email' => 'cashier4@foodcompany.com',
                'password' => Hash::make('cashier123'),
                'phone' => '(555) 300-0004',
                'address' => '123 Main Street, Downtown',
                'role_id' => $cashierRole->id,
                'branch_id' => $mainBranch->id,
                'is_active' => true,
            ],

            // Delivery Staff
            [
                'name' => 'Express Delivery',
                'email' => 'delivery@foodcompany.com',
                'password' => Hash::make('delivery123'),
                'phone' => '(555) 400-0001',
                'address' => '123 Main Street, Downtown',
                'role_id' => $deliveryRole->id,
                'branch_id' => $mainBranch->id,
                'is_active' => true,
            ],
            [
                'name' => 'Fast Runner',
                'email' => 'delivery2@foodcompany.com',
                'password' => Hash::make('delivery123'),
                'phone' => '(555) 400-0002',
                'address' => '456 Business Ave, Downtown',
                'role_id' => $deliveryRole->id,
                'branch_id' => $downtownBranch->id,
                'is_active' => true,
            ],

            // Test Users
            [
                'name' => 'Test User',
                'email' => 'test@foodcompany.com',
                'password' => Hash::make('password123'),
                'phone' => '(555) 999-0001',
                'address' => 'Test Address',
                'role_id' => $cashierRole->id, // Default to cashier for testing
                'branch_id' => $mainBranch->id,
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(['email' => $userData['email']], $userData);
        }

        $this->command->info('Login system seeder completed successfully!');
        $this->command->info('Created:');
        $this->command->info('- ' . count($cities) . ' cities');
        $this->command->info('- ' . count($roles) . ' roles');
        $this->command->info('- ' . count($branches) . ' branches/outlets');
        $this->command->info('- ' . count($users) . ' users');
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('Admin: admin@foodcompany.com / admin123');
        $this->command->info('Manager: manager@foodcompany.com / manager123');
        $this->command->info('Cashier: cashier@foodcompany.com / cashier123 (Outlet: FDC001)');
        $this->command->info('Test: test@foodcompany.com / password123');
    }
}