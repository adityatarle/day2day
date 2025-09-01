<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the admin role
        $adminRole = Role::where('name', 'admin')->first();
        
        // Get the main branch
        $mainBranch = Branch::where('code', 'MB001')->first();

        if ($adminRole && $mainBranch) {
            User::create([
                'name' => 'System Administrator',
                'email' => 'admin@example.com',
                'phone' => '+91-9876543200',
                'password' => Hash::make('admin123'),
                'role_id' => $adminRole->id,
                'branch_id' => $mainBranch->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }
    }
}