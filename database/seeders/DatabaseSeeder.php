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
        // Use the comprehensive Multi-Role System Seeder
        $this->call([
            MultiRoleSystemSeeder::class,
            InventorySystemSeeder::class,
            Day2DaySeeder::class,
        ]);

        // Optionally call other data seeders for products and demo data
        // $this->call([
        //     BasicDataSeeder::class,
        //     CityProductPricingSeeder::class,
        //     EnhancedSystemSeeder::class,
        // ]);
    }
}
