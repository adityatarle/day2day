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
        // Use the comprehensive LoginSystemSeeder
        $this->call([
            LoginSystemSeeder::class,
        ]);

        // Optionally call other data seeders if needed
        // $this->call([
        //     BasicDataSeeder::class,
        //     CitySeeder::class,
        //     CityProductPricingSeeder::class,
        //     EnhancedSystemSeeder::class,
        // ]);
    }
}
