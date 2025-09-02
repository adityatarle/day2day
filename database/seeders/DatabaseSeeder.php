<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Branch;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call existing seeders in order
        $this->call([
            RoleSeeder::class,
            BranchSeeder::class,
            AdminUserSeeder::class,
        ]);

        // Add a simple test user for easy login testing
        $adminRole = Role::where('name', 'admin')->first();
        $mainBranch = Branch::first();

        if ($adminRole && $mainBranch) {
            // Check if test user already exists
            if (!User::where('email', 'test@foodcompany.com')->exists()) {
                User::create([
                    'name' => 'Test User',
                    'email' => 'test@foodcompany.com',
                    'phone' => '+1234567899',
                    'password' => Hash::make('password123'),
                    'role_id' => $adminRole->id,
                    'branch_id' => $mainBranch->id,
                    'is_active' => true,
                ]);
            }
        }
    }
}
