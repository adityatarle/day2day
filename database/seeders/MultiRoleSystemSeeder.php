<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Branch;
use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MultiRoleSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Setting up multi-role food company management system...');

        // Create or update roles and permissions
        $this->setupRolesAndPermissions();
        
        // Create cities
        $this->setupCities();
        
        // Create branches
        $this->setupBranches();
        
        // Create users with different roles
        $this->setupUsers();
        
        $this->command->info('Multi-role system setup completed successfully!');
        $this->displayLoginCredentials();
    }

    private function setupRolesAndPermissions()
    {
        $this->command->info('Setting up roles and permissions...');

        // Enhanced permissions for the multi-role system
        $permissions = [
            // User Management
            ['name' => 'user.create', 'display_name' => 'Create User', 'description' => 'Can create new users', 'module' => 'user_management'],
            ['name' => 'user.edit', 'display_name' => 'Edit User', 'description' => 'Can edit existing users', 'module' => 'user_management'],
            ['name' => 'user.delete', 'display_name' => 'Delete User', 'description' => 'Can delete users', 'module' => 'user_management'],
            ['name' => 'user.view', 'display_name' => 'View User', 'description' => 'Can view user details', 'module' => 'user_management'],
            ['name' => 'user.manage_roles', 'display_name' => 'Manage User Roles', 'description' => 'Can assign/change user roles', 'module' => 'user_management'],
            
            // Branch Management
            ['name' => 'branch.create', 'display_name' => 'Create Branch', 'description' => 'Can create new branches', 'module' => 'branch_management'],
            ['name' => 'branch.edit', 'display_name' => 'Edit Branch', 'description' => 'Can edit existing branches', 'module' => 'branch_management'],
            ['name' => 'branch.delete', 'display_name' => 'Delete Branch', 'description' => 'Can delete branches', 'module' => 'branch_management'],
            ['name' => 'branch.view', 'display_name' => 'View Branch', 'description' => 'Can view branch details', 'module' => 'branch_management'],
            ['name' => 'branch.assign_manager', 'display_name' => 'Assign Branch Manager', 'description' => 'Can assign managers to branches', 'module' => 'branch_management'],
            
            // Inventory Management
            ['name' => 'inventory.view', 'display_name' => 'View Inventory', 'description' => 'Can view inventory', 'module' => 'inventory'],
            ['name' => 'inventory.edit', 'display_name' => 'Edit Inventory', 'description' => 'Can edit inventory', 'module' => 'inventory'],
            ['name' => 'inventory.adjust', 'display_name' => 'Adjust Inventory', 'description' => 'Can adjust inventory levels', 'module' => 'inventory'],
            ['name' => 'inventory.reports', 'display_name' => 'Inventory Reports', 'description' => 'Can view inventory reports', 'module' => 'inventory'],
            ['name' => 'inventory.transfer', 'display_name' => 'Transfer Inventory', 'description' => 'Can transfer inventory between branches', 'module' => 'inventory'],
            
            // Sales Management
            ['name' => 'sales.create', 'display_name' => 'Create Sale', 'description' => 'Can create sales transactions', 'module' => 'sales'],
            ['name' => 'sales.edit', 'display_name' => 'Edit Sale', 'description' => 'Can edit sales transactions', 'module' => 'sales'],
            ['name' => 'sales.view', 'display_name' => 'View Sales', 'description' => 'Can view sales data', 'module' => 'sales'],
            ['name' => 'sales.reports', 'display_name' => 'Sales Reports', 'description' => 'Can view sales reports', 'module' => 'sales'],
            ['name' => 'sales.refund', 'display_name' => 'Process Refunds', 'description' => 'Can process sales refunds', 'module' => 'sales'],
            
            // POS Management
            ['name' => 'pos.operate', 'display_name' => 'Operate POS', 'description' => 'Can operate POS system', 'module' => 'pos'],
            ['name' => 'pos.manage_sessions', 'display_name' => 'Manage POS Sessions', 'description' => 'Can manage POS sessions', 'module' => 'pos'],
            ['name' => 'pos.view_all_sessions', 'display_name' => 'View All POS Sessions', 'description' => 'Can view all POS sessions', 'module' => 'pos'],
            ['name' => 'pos.close_any_session', 'display_name' => 'Close Any POS Session', 'description' => 'Can close any POS session', 'module' => 'pos'],
            
            // Purchase Management
            ['name' => 'purchase.create', 'display_name' => 'Create Purchase', 'description' => 'Can create purchase orders', 'module' => 'purchases'],
            ['name' => 'purchase.edit', 'display_name' => 'Edit Purchase', 'description' => 'Can edit purchase orders', 'module' => 'purchases'],
            ['name' => 'purchase.view', 'display_name' => 'View Purchases', 'description' => 'Can view purchase data', 'module' => 'purchases'],
            ['name' => 'purchase.approve', 'display_name' => 'Approve Purchase', 'description' => 'Can approve purchase orders', 'module' => 'purchases'],
            
            // Customer Management
            ['name' => 'customer.create', 'display_name' => 'Create Customer', 'description' => 'Can create new customers', 'module' => 'customers'],
            ['name' => 'customer.edit', 'display_name' => 'Edit Customer', 'description' => 'Can edit existing customers', 'module' => 'customers'],
            ['name' => 'customer.view', 'display_name' => 'View Customer', 'description' => 'Can view customer details', 'module' => 'customers'],
            
            // Financial Management
            ['name' => 'finance.view', 'display_name' => 'View Finance', 'description' => 'Can view financial data', 'module' => 'finance'],
            ['name' => 'finance.edit', 'display_name' => 'Edit Finance', 'description' => 'Can edit financial data', 'module' => 'finance'],
            ['name' => 'finance.reports', 'display_name' => 'Financial Reports', 'description' => 'Can view financial reports', 'module' => 'finance'],
            
            // Analytics & Reports
            ['name' => 'analytics.view', 'display_name' => 'View Analytics', 'description' => 'Can view business analytics', 'module' => 'analytics'],
            ['name' => 'reports.view', 'display_name' => 'View Reports', 'description' => 'Can view business reports', 'module' => 'analytics'],
            ['name' => 'reports.export', 'display_name' => 'Export Reports', 'description' => 'Can export business reports', 'module' => 'analytics'],
            
            // System Administration
            ['name' => 'system.manage', 'display_name' => 'System Management', 'description' => 'Can manage system settings', 'module' => 'system'],
            ['name' => 'system.monitor', 'display_name' => 'System Monitoring', 'description' => 'Can monitor system health', 'module' => 'system'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']], 
                $permission
            );
        }

        // Create roles with specific permissions
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Admin',
                'description' => 'Ultimate system access with complete control over all operations, users, and system settings',
                'permissions' => Permission::all()->pluck('name')->toArray() // All permissions
            ],
            [
                'name' => 'branch_manager',
                'display_name' => 'Branch Manager',
                'description' => 'Manages a specific branch with staff and inventory control',
                'permissions' => [
                    'user.create', 'user.edit', 'user.view', // Can manage cashiers and delivery staff
                    'branch.view', 'branch.edit', // Can manage their own branch
                    'inventory.view', 'inventory.edit', 'inventory.adjust', 'inventory.reports',
                    'sales.create', 'sales.edit', 'sales.view', 'sales.reports', 'sales.refund',
                    'pos.operate', 'pos.manage_sessions', 'pos.view_all_sessions', 'pos.close_any_session',
                    'purchase.create', 'purchase.edit', 'purchase.view',
                    'customer.create', 'customer.edit', 'customer.view',
                    'finance.view', 'finance.reports',
                    'analytics.view', 'reports.view'
                ]
            ],
            [
                'name' => 'cashier',
                'display_name' => 'Cashier',
                'description' => 'Handles POS operations and sales transactions',
                'permissions' => [
                    'inventory.view', // Can check stock
                    'sales.create', 'sales.edit', 'sales.view', 'sales.refund',
                    'pos.operate', 'pos.manage_sessions', // Can operate POS
                    'customer.create', 'customer.edit', 'customer.view',
                    'finance.view' // Limited financial view
                ]
            ],
            [
                'name' => 'delivery_boy',
                'display_name' => 'Delivery Staff',
                'description' => 'Handles deliveries and return adjustments',
                'permissions' => [
                    'inventory.view',
                    'sales.view', 'sales.edit', // For delivery updates
                    'customer.view'
                ]
            ]
        ];

        foreach ($roles as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);
            
            $role = Role::firstOrCreate(
                ['name' => $roleData['name']], 
                $roleData + ['is_active' => true]
            );
            
            // Sync permissions to role
            $permissionModels = Permission::whereIn('name', $permissions)->get();
            $role->permissions()->sync($permissionModels);
        }
    }

    private function setupCities()
    {
        $this->command->info('Setting up cities...');

        $cities = [
            ['name' => 'Mumbai', 'state' => 'Maharashtra', 'code' => 'MUM', 'is_active' => true],
            ['name' => 'Delhi', 'state' => 'Delhi', 'code' => 'DEL', 'is_active' => true],
            ['name' => 'Bangalore', 'state' => 'Karnataka', 'code' => 'BLR', 'is_active' => true],
            ['name' => 'Pune', 'state' => 'Maharashtra', 'code' => 'PUN', 'is_active' => true],
            ['name' => 'Chennai', 'state' => 'Tamil Nadu', 'code' => 'CHN', 'is_active' => true],
        ];

        foreach ($cities as $city) {
            City::firstOrCreate(['name' => $city['name']], $city);
        }
    }

    private function setupBranches()
    {
        $this->command->info('Setting up branches...');

        $mumbai = City::where('name', 'Mumbai')->first();
        $delhi = City::where('name', 'Delhi')->first();
        $bangalore = City::where('name', 'Bangalore')->first();

        $branches = [
            [
                'name' => 'Mumbai Central Branch',
                'code' => 'MBC001',
                'address' => 'Shop 101, Central Plaza, Mumbai',
                'phone' => '+91-9876543210',
                'email' => 'mumbai.central@foodcompany.com',
                'city_id' => $mumbai->id,
                'outlet_type' => 'hybrid',
                'pos_enabled' => true,
                'pos_terminal_id' => 'POS-MBC-001',
                'is_active' => true,
            ],
            [
                'name' => 'Delhi Main Branch',
                'code' => 'DMB001',
                'address' => 'Unit 205, Metro Mall, New Delhi',
                'phone' => '+91-9876543211',
                'email' => 'delhi.main@foodcompany.com',
                'city_id' => $delhi->id,
                'outlet_type' => 'restaurant',
                'pos_enabled' => true,
                'pos_terminal_id' => 'POS-DMB-001',
                'is_active' => true,
            ],
            [
                'name' => 'Bangalore Tech Park Branch',
                'code' => 'BTP001',
                'address' => 'Food Court, Tech Park, Bangalore',
                'phone' => '+91-9876543212',
                'email' => 'bangalore.tech@foodcompany.com',
                'city_id' => $bangalore->id,
                'outlet_type' => 'takeaway',
                'pos_enabled' => true,
                'pos_terminal_id' => 'POS-BTP-001',
                'is_active' => true,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::firstOrCreate(['code' => $branch['code']], $branch);
        }
    }

    private function setupUsers()
    {
        $this->command->info('Setting up users...');

        $superAdminRole = Role::where('name', 'super_admin')->first();
        $branchManagerRole = Role::where('name', 'branch_manager')->first();
        $cashierRole = Role::where('name', 'cashier')->first();
        $deliveryRole = Role::where('name', 'delivery_boy')->first();

        $mumbaibranch = Branch::where('code', 'MBC001')->first();
        $delhiBranch = Branch::where('code', 'DMB001')->first();
        $bangaloreBranch = Branch::where('code', 'BTP001')->first();

        $users = [
            // Super Admin
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@foodcompany.com',
                'phone' => '+91-9999999999',
                'address' => 'Corporate Office, Business District',
                'password' => Hash::make('password123'),
                'role_id' => $superAdminRole->id,
                'branch_id' => null, // Super admin not tied to specific branch
                'is_active' => true,
            ],
            
            // Branch Managers
            [
                'name' => 'Mumbai Branch Manager',
                'email' => 'manager.mumbai@foodcompany.com',
                'phone' => '+91-9876543210',
                'address' => 'Mumbai Central Area',
                'password' => Hash::make('manager123'),
                'role_id' => $branchManagerRole->id,
                'branch_id' => $mumbaiBranch->id,
                'is_active' => true,
            ],
            [
                'name' => 'Delhi Branch Manager',
                'email' => 'manager.delhi@foodcompany.com',
                'phone' => '+91-9876543211',
                'address' => 'New Delhi Area',
                'password' => Hash::make('manager123'),
                'role_id' => $branchManagerRole->id,
                'branch_id' => $delhiBranch->id,
                'is_active' => true,
            ],
            [
                'name' => 'Bangalore Branch Manager',
                'email' => 'manager.bangalore@foodcompany.com',
                'phone' => '+91-9876543212',
                'address' => 'Bangalore Tech Park Area',
                'password' => Hash::make('manager123'),
                'role_id' => $branchManagerRole->id,
                'branch_id' => $bangaloreBranch->id,
                'is_active' => true,
            ],
            
            // Cashiers
            [
                'name' => 'Mumbai Cashier 1',
                'email' => 'cashier1.mumbai@foodcompany.com',
                'phone' => '+91-9876543213',
                'address' => 'Mumbai Local Area',
                'password' => Hash::make('cashier123'),
                'role_id' => $cashierRole->id,
                'branch_id' => $mumbaiBranch->id,
                'is_active' => true,
            ],
            [
                'name' => 'Mumbai Cashier 2',
                'email' => 'cashier2.mumbai@foodcompany.com',
                'phone' => '+91-9876543214',
                'address' => 'Mumbai Local Area',
                'password' => Hash::make('cashier123'),
                'role_id' => $cashierRole->id,
                'branch_id' => $mumbaiBranch->id,
                'is_active' => true,
            ],
            [
                'name' => 'Delhi Cashier 1',
                'email' => 'cashier1.delhi@foodcompany.com',
                'phone' => '+91-9876543215',
                'address' => 'Delhi Local Area',
                'password' => Hash::make('cashier123'),
                'role_id' => $cashierRole->id,
                'branch_id' => $delhiBranch->id,
                'is_active' => true,
            ],
            [
                'name' => 'Bangalore Cashier 1',
                'email' => 'cashier1.bangalore@foodcompany.com',
                'phone' => '+91-9876543216',
                'address' => 'Bangalore Local Area',
                'password' => Hash::make('cashier123'),
                'role_id' => $cashierRole->id,
                'branch_id' => $bangaloreBranch->id,
                'is_active' => true,
            ],
            
            // Delivery Staff
            [
                'name' => 'Mumbai Delivery 1',
                'email' => 'delivery1.mumbai@foodcompany.com',
                'phone' => '+91-9876543217',
                'address' => 'Mumbai Delivery Zone',
                'password' => Hash::make('delivery123'),
                'role_id' => $deliveryRole->id,
                'branch_id' => $mumbaiBranch->id,
                'is_active' => true,
            ],
            [
                'name' => 'Delhi Delivery 1',
                'email' => 'delivery1.delhi@foodcompany.com',
                'phone' => '+91-9876543218',
                'address' => 'Delhi Delivery Zone',
                'password' => Hash::make('delivery123'),
                'role_id' => $deliveryRole->id,
                'branch_id' => $delhiBranch->id,
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(['email' => $user['email']], $user);
        }
    }

    private function displayLoginCredentials()
    {
        $this->command->info('');
        $this->command->info('=== LOGIN CREDENTIALS ===');
        $this->command->info('');
        $this->command->info('Super Admin:');
        $this->command->info('Email: superadmin@foodcompany.com');
        $this->command->info('Password: password123');
        $this->command->info('');
        $this->command->info('Branch Managers:');
        $this->command->info('Mumbai: manager.mumbai@foodcompany.com / manager123');
        $this->command->info('Delhi: manager.delhi@foodcompany.com / manager123');
        $this->command->info('Bangalore: manager.bangalore@foodcompany.com / manager123');
        $this->command->info('');
        $this->command->info('Cashiers:');
        $this->command->info('Mumbai: cashier1.mumbai@foodcompany.com / cashier123');
        $this->command->info('Delhi: cashier1.delhi@foodcompany.com / cashier123');
        $this->command->info('Bangalore: cashier1.bangalore@foodcompany.com / cashier123');
        $this->command->info('');
        $this->command->info('=========================');
    }
}