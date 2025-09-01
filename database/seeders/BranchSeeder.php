<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Main Branch',
                'code' => 'MB001',
                'address' => '123 Main Street, City Center',
                'phone' => '+91-9876543210',
                'email' => 'main@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'North Branch',
                'code' => 'NB002',
                'address' => '456 North Avenue, North City',
                'phone' => '+91-9876543211',
                'email' => 'north@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'South Branch',
                'code' => 'SB003',
                'address' => '789 South Road, South City',
                'phone' => '+91-9876543212',
                'email' => 'south@example.com',
                'is_active' => true,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }
    }
}