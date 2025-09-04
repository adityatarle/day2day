<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\City;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            [
                'name' => 'Mumbai',
                'state' => 'Maharashtra',
                'country' => 'India',
                'code' => 'MUM',
                'delivery_charge' => 50.00,
                'tax_rate' => 18.00,
                'is_active' => true,
            ],
            [
                'name' => 'Delhi',
                'state' => 'Delhi',
                'country' => 'India',
                'code' => 'DEL',
                'delivery_charge' => 45.00,
                'tax_rate' => 18.00,
                'is_active' => true,
            ],
            [
                'name' => 'Bangalore',
                'state' => 'Karnataka',
                'country' => 'India',
                'code' => 'BLR',
                'delivery_charge' => 40.00,
                'tax_rate' => 18.00,
                'is_active' => true,
            ],
            [
                'name' => 'Chennai',
                'state' => 'Tamil Nadu',
                'country' => 'India',
                'code' => 'CHN',
                'delivery_charge' => 35.00,
                'tax_rate' => 18.00,
                'is_active' => true,
            ],
            [
                'name' => 'Pune',
                'state' => 'Maharashtra',
                'country' => 'India',
                'code' => 'PUN',
                'delivery_charge' => 30.00,
                'tax_rate' => 18.00,
                'is_active' => true,
            ],
            [
                'name' => 'Hyderabad',
                'state' => 'Telangana',
                'country' => 'India',
                'code' => 'HYD',
                'delivery_charge' => 35.00,
                'tax_rate' => 18.00,
                'is_active' => true,
            ],
        ];

        foreach ($cities as $cityData) {
            City::create($cityData);
        }
    }
}
