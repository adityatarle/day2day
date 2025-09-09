<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GstRate;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Vendor;
use App\Models\City;
use Illuminate\Support\Str;

class InventorySystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create GST Rates
        $gstRates = [
            ['name' => 'GST 0%', 'rate' => 0.00, 'description' => 'No GST'],
            ['name' => 'GST 5%', 'rate' => 5.00, 'description' => 'Essential items'],
            ['name' => 'GST 12%', 'rate' => 12.00, 'description' => 'Standard rate'],
            ['name' => 'GST 18%', 'rate' => 18.00, 'description' => 'Standard rate'],
            ['name' => 'GST 28%', 'rate' => 28.00, 'description' => 'Luxury items'],
        ];

        foreach ($gstRates as $rate) {
            GstRate::firstOrCreate(['rate' => $rate['rate']], $rate);
        }

        // Create Cities
        $cities = [
            ['name' => 'Mumbai', 'state' => 'Maharashtra', 'code' => 'MUM'],
            ['name' => 'Delhi', 'state' => 'Delhi', 'code' => 'DEL'],
            ['name' => 'Bangalore', 'state' => 'Karnataka', 'code' => 'BLR'],
            ['name' => 'Chennai', 'state' => 'Tamil Nadu', 'code' => 'CHN'],
            ['name' => 'Pune', 'state' => 'Maharashtra', 'code' => 'PUN'],
        ];

        foreach ($cities as $cityData) {
            City::firstOrCreate(['name' => $cityData['name']], $cityData);
        }

        // Create Branches
        $branches = [
            [
                'name' => 'Main Store - Mumbai',
                'code' => 'MUM001',
                'address' => 'Shop No. 1, ABC Complex, Mumbai',
                'phone' => '9876543210',
                'email' => 'mumbai@freshmart.com',
                'city_id' => City::where('name', 'Mumbai')->first()->id,
                'outlet_type' => 'retail',
                'pos_enabled' => true,
                'pos_terminal_id' => 'POS001',
            ],
            [
                'name' => 'Branch Store - Delhi',
                'code' => 'DEL001',
                'address' => 'Shop No. 5, XYZ Market, Delhi',
                'phone' => '9876543211',
                'email' => 'delhi@freshmart.com',
                'city_id' => City::where('name', 'Delhi')->first()->id,
                'outlet_type' => 'retail',
                'pos_enabled' => true,
                'pos_terminal_id' => 'POS002',
            ],
        ];

        foreach ($branches as $branchData) {
            Branch::firstOrCreate(['code' => $branchData['code']], $branchData);
        }

        // Create Vendors
        $vendors = [
            [
                'name' => 'Fresh Farms Supplier',
                'code' => 'VEN001',
                'address' => 'Farm House, Nashik, Maharashtra',
                'phone' => '9876543212',
                'email' => 'contact@freshfarms.com',
                'gst_number' => '27ABCDE1234F1Z5',
            ],
            [
                'name' => 'Organic Produce Co.',
                'code' => 'VEN002',
                'address' => 'Organic Valley, Pune, Maharashtra',
                'phone' => '9876543213',
                'email' => 'info@organicproduce.com',
                'gst_number' => '27FGHIJ5678K2Z6',
            ],
            [
                'name' => 'Exotic Fruits Import',
                'code' => 'VEN003',
                'address' => 'Import Hub, Mumbai, Maharashtra',
                'phone' => '9876543214',
                'email' => 'sales@exoticfruits.com',
                'gst_number' => '27LMNOP9012L3Z7',
            ],
        ];

        foreach ($vendors as $vendorData) {
            Vendor::firstOrCreate(['code' => $vendorData['code']], $vendorData);
        }

        // Create Products
        $products = [
            // Fruits
            [
                'name' => 'Apple - Red Delicious',
                'code' => 'APPLE001',
                'hsn_code' => '08081000',
                'category' => 'fruit',
                'subcategory' => 'seasonal',
                'weight_unit' => 'kg',
                'purchase_price' => 120.00,
                'mrp' => 180.00,
                'selling_price' => 160.00,
                'stock_threshold' => 5,
                'shelf_life_days' => 15,
                'storage_temperature' => '2-8°C',
                'is_perishable' => true,
            ],
            [
                'name' => 'Banana - Robusta',
                'code' => 'BANANA001',
                'hsn_code' => '08030000',
                'category' => 'fruit',
                'subcategory' => 'tropical',
                'weight_unit' => 'kg',
                'purchase_price' => 40.00,
                'mrp' => 70.00,
                'selling_price' => 60.00,
                'stock_threshold' => 10,
                'shelf_life_days' => 7,
                'storage_temperature' => '12-15°C',
                'is_perishable' => true,
            ],
            [
                'name' => 'Orange - Nagpur',
                'code' => 'ORANGE001',
                'hsn_code' => '08051000',
                'category' => 'fruit',
                'subcategory' => 'citrus',
                'weight_unit' => 'kg',
                'purchase_price' => 60.00,
                'mrp' => 100.00,
                'selling_price' => 85.00,
                'stock_threshold' => 8,
                'shelf_life_days' => 20,
                'storage_temperature' => '2-8°C',
                'is_perishable' => true,
            ],
            // Vegetables
            [
                'name' => 'Tomato - Hybrid',
                'code' => 'TOMATO001',
                'hsn_code' => '07020000',
                'category' => 'vegetable',
                'subcategory' => 'gourd',
                'weight_unit' => 'kg',
                'purchase_price' => 25.00,
                'mrp' => 50.00,
                'selling_price' => 40.00,
                'stock_threshold' => 15,
                'shelf_life_days' => 10,
                'storage_temperature' => '8-12°C',
                'is_perishable' => true,
            ],
            [
                'name' => 'Onion - Red',
                'code' => 'ONION001',
                'hsn_code' => '07031000',
                'category' => 'vegetable',
                'subcategory' => 'bulb',
                'weight_unit' => 'kg',
                'purchase_price' => 20.00,
                'mrp' => 40.00,
                'selling_price' => 35.00,
                'stock_threshold' => 20,
                'shelf_life_days' => 30,
                'storage_temperature' => 'Room Temperature',
                'is_perishable' => false,
            ],
            [
                'name' => 'Potato - Fresh',
                'code' => 'POTATO001',
                'hsn_code' => '07019000',
                'category' => 'vegetable',
                'subcategory' => 'root',
                'weight_unit' => 'kg',
                'purchase_price' => 18.00,
                'mrp' => 35.00,
                'selling_price' => 30.00,
                'stock_threshold' => 25,
                'shelf_life_days' => 45,
                'storage_temperature' => 'Room Temperature',
                'is_perishable' => false,
            ],
            // Leafy Vegetables
            [
                'name' => 'Spinach - Fresh',
                'code' => 'SPINACH001',
                'hsn_code' => '07099000',
                'category' => 'leafy',
                'subcategory' => 'greens',
                'weight_unit' => 'kg',
                'purchase_price' => 15.00,
                'mrp' => 35.00,
                'selling_price' => 28.00,
                'stock_threshold' => 5,
                'shelf_life_days' => 3,
                'storage_temperature' => '0-2°C',
                'is_perishable' => true,
            ],
            [
                'name' => 'Coriander - Fresh',
                'code' => 'CORIANDER001',
                'hsn_code' => '07099000',
                'category' => 'leafy',
                'subcategory' => 'herbs',
                'weight_unit' => 'kg',
                'purchase_price' => 30.00,
                'mrp' => 60.00,
                'selling_price' => 50.00,
                'stock_threshold' => 2,
                'shelf_life_days' => 5,
                'storage_temperature' => '0-2°C',
                'is_perishable' => true,
            ],
            // Exotic Items
            [
                'name' => 'Avocado - Imported',
                'code' => 'AVOCADO001',
                'hsn_code' => '08044000',
                'category' => 'exotic',
                'subcategory' => 'imported',
                'weight_unit' => 'pcs',
                'purchase_price' => 80.00,
                'mrp' => 150.00,
                'selling_price' => 120.00,
                'stock_threshold' => 10,
                'shelf_life_days' => 7,
                'storage_temperature' => '8-12°C',
                'is_perishable' => true,
            ],
            [
                'name' => 'Dragon Fruit',
                'code' => 'DRAGON001',
                'hsn_code' => '08109000',
                'category' => 'exotic',
                'subcategory' => 'specialty',
                'weight_unit' => 'pcs',
                'purchase_price' => 150.00,
                'mrp' => 250.00,
                'selling_price' => 220.00,
                'stock_threshold' => 5,
                'shelf_life_days' => 10,
                'storage_temperature' => '8-12°C',
                'is_perishable' => true,
            ],
        ];

        $gst5 = GstRate::where('rate', 5.00)->first();
        $gst0 = GstRate::where('rate', 0.00)->first();
        $branches = Branch::all();
        $vendors = Vendor::all();

        foreach ($products as $productData) {
            $product = Product::firstOrCreate(['code' => $productData['code']], $productData);
            
            // Assign GST rates (fruits and vegetables typically have 5% GST or 0%)
            if (in_array($product->category, ['fruit', 'vegetable', 'leafy'])) {
                $product->gstRates()->syncWithoutDetaching([$gst0->id]);
            } else {
                $product->gstRates()->syncWithoutDetaching([$gst5->id]);
            }

            // Assign to branches with initial stock
            foreach ($branches as $branch) {
                $product->branches()->syncWithoutDetaching([
                    $branch->id => [
                        'selling_price' => $product->selling_price + (rand(-10, 20)), // Slight variation per branch
                        'current_stock' => rand(20, 100), // Random initial stock
                        'is_available_online' => true,
                    ]
                ]);
            }

            // Assign vendors
            $randomVendor = $vendors->random();
            $product->vendors()->syncWithoutDetaching([
                $randomVendor->id => [
                    'supply_price' => $product->purchase_price - rand(5, 15),
                    'is_primary_supplier' => true,
                ]
            ]);

            // Add secondary vendor for some products
            if (rand(1, 3) == 1) {
                $secondaryVendor = $vendors->where('id', '!=', $randomVendor->id)->random();
                $product->vendors()->syncWithoutDetaching([
                    $secondaryVendor->id => [
                        'supply_price' => $product->purchase_price - rand(0, 10),
                        'is_primary_supplier' => false,
                    ]
                ]);
            }
        }

        $this->command->info('Inventory system seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- ' . GstRate::count() . ' GST rates');
        $this->command->info('- ' . City::count() . ' cities');
        $this->command->info('- ' . Branch::count() . ' branches');
        $this->command->info('- ' . Vendor::count() . ' vendors');
        $this->command->info('- ' . Product::count() . ' products');
    }
}