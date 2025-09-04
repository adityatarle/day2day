<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions for different modules
        $permissions = [
            // User Management
            ['name' => 'user.create', 'display_name' => 'Create User', 'description' => 'Can create new users', 'module' => 'user_management'],
            ['name' => 'user.edit', 'display_name' => 'Edit User', 'description' => 'Can edit existing users', 'module' => 'user_management'],
            ['name' => 'user.delete', 'display_name' => 'Delete User', 'description' => 'Can delete users', 'module' => 'user_management'],
            ['name' => 'user.view', 'display_name' => 'View User', 'description' => 'Can view user details', 'module' => 'user_management'],
            
            // Branch Management
            ['name' => 'branch.create', 'display_name' => 'Create Branch', 'description' => 'Can create new branches', 'module' => 'branch_management'],
            ['name' => 'branch.edit', 'display_name' => 'Edit Branch', 'description' => 'Can edit existing branches', 'module' => 'branch_management'],
            ['name' => 'branch.delete', 'display_name' => 'Delete Branch', 'description' => 'Can delete branches', 'module' => 'branch_management'],
            ['name' => 'branch.view', 'display_name' => 'View Branch', 'description' => 'Can view branch details', 'module' => 'branch_management'],
            
            // Inventory Management
            ['name' => 'inventory.view', 'display_name' => 'View Inventory', 'description' => 'Can view inventory', 'module' => 'inventory'],
            ['name' => 'inventory.edit', 'display_name' => 'Edit Inventory', 'description' => 'Can edit inventory', 'module' => 'inventory'],
            ['name' => 'inventory.adjust', 'display_name' => 'Adjust Inventory', 'description' => 'Can adjust inventory levels', 'module' => 'inventory'],
            ['name' => 'inventory.reports', 'display_name' => 'Inventory Reports', 'description' => 'Can view inventory reports', 'module' => 'inventory'],
            
            // Sales Management
            ['name' => 'sales.create', 'display_name' => 'Create Sale', 'description' => 'Can create sales transactions', 'module' => 'sales'],
            ['name' => 'sales.edit', 'display_name' => 'Edit Sale', 'description' => 'Can edit sales transactions', 'module' => 'sales'],
            ['name' => 'sales.view', 'display_name' => 'View Sales', 'description' => 'Can view sales data', 'module' => 'sales'],
            ['name' => 'sales.reports', 'display_name' => 'Sales Reports', 'description' => 'Can view sales reports', 'module' => 'sales'],
            
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
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']], 
                $permission
            );
        }

        // Create roles with their permissions
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Admin',
                'description' => 'Ultimate system access with complete control over all operations, users, and system settings',
                'permissions' => Permission::all()->pluck('name')->toArray()
            ],
            [
                'name' => 'admin',
                'display_name' => 'Admin (Owner/Manager)',
                'description' => 'Full access to business operations but limited system administration',
                'permissions' => [
                    'user.create', 'user.edit', 'user.view',
                    'branch.create', 'branch.edit', 'branch.view',
                    'inventory.view', 'inventory.edit', 'inventory.adjust', 'inventory.reports',
                    'sales.create', 'sales.edit', 'sales.view', 'sales.reports',
                    'purchase.create', 'purchase.edit', 'purchase.view', 'purchase.approve',
                    'customer.create', 'customer.edit', 'customer.view',
                    'finance.view', 'finance.edit', 'finance.reports',
                    'analytics.view', 'reports.view'
                ]
            ],
            [
                'name' => 'branch_manager',
                'display_name' => 'Branch Manager',
                'description' => 'Manages a specific branch with limited admin access',
                'permissions' => [
                    'user.view', 'user.edit',
                    'branch.view', 'branch.edit',
                    'inventory.view', 'inventory.edit', 'inventory.adjust', 'inventory.reports',
                    'sales.create', 'sales.edit', 'sales.view', 'sales.reports',
                    'purchase.create', 'purchase.edit', 'purchase.view',
                    'customer.create', 'customer.edit', 'customer.view',
                    'finance.view', 'finance.reports',
                    'analytics.view', 'reports.view'
                ]
            ],
            [
                'name' => 'cashier',
                'display_name' => 'Cashier (On-Shop Sales)',
                'description' => 'Handles on-shop sales and basic operations',
                'permissions' => [
                    'inventory.view',
                    'sales.create', 'sales.edit', 'sales.view',
                    'customer.create', 'customer.edit', 'customer.view',
                    'finance.view'
                ]
            ],
            [
                'name' => 'delivery_boy',
                'display_name' => 'Delivery Boy (Online Delivery + Returns)',
                'description' => 'Handles deliveries and return adjustments',
                'permissions' => [
                    'inventory.view',
                    'sales.view', 'sales.edit',
                    'customer.view'
                ]
            ]
        ];

        foreach ($roles as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);
            
            $role = Role::firstOrCreate(
                ['name' => $roleData['name']], 
                $roleData
            );
            
            // Sync permissions to role (this will replace existing permissions)
            $permissionModels = Permission::whereIn('name', $permissions)->get();
            $role->permissions()->sync($permissionModels);
        }
    }
}