<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\Product;
use App\Models\CityProductPricing;

class CityProductPricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = City::all();
        $products = Product::take(10)->get(); // Get first 10 products

        foreach ($cities as $city) {
            foreach ($products as $product) {
                // Create different pricing for each city
                $basePriceMultiplier = match($city->code) {
                    'MUM' => 1.2,  // Mumbai - 20% higher
                    'DEL' => 1.15, // Delhi - 15% higher
                    'BLR' => 1.1,  // Bangalore - 10% higher
                    'CHN' => 1.0,  // Chennai - base price
                    'PUN' => 0.95, // Pune - 5% lower
                    'HYD' => 0.9,  // Hyderabad - 10% lower
                    default => 1.0
                };

                $cityPrice = $product->selling_price * $basePriceMultiplier;
                $cityMrp = $product->mrp * $basePriceMultiplier;

                CityProductPricing::create([
                    'city_id' => $city->id,
                    'product_id' => $product->id,
                    'selling_price' => round($cityPrice, 2),
                    'mrp' => round($cityMrp, 2),
                    'discount_percentage' => 0,
                    'is_available' => true,
                    'effective_from' => now()->startOfDay(),
                    'effective_until' => null, // No end date
                ]);
            }
        }
    }
}
