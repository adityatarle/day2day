# 🚀 Advanced Inventory Management - Quick Setup Guide

## Prerequisites

- Laravel 12 installed and configured
- Database connection configured
- Existing fruit & vegetable business system running

---

## Step-by-Step Setup

### Step 1: Run Database Migrations

```bash
php artisan migrate
```

This will create all necessary tables:
- Smart Reordering System (6 tables)
- Batch & Expiry Management (6 tables)
- Multi-Warehouse Support (7 tables)
- Stock Reconciliation (9 tables)

**Expected Output:**
```
Migration: 2025_10_01_000001_create_smart_reordering_system
Migration: 2025_10_01_000002_create_batch_expiry_management_system
Migration: 2025_10_01_000003_create_multi_warehouse_system
Migration: 2025_10_01_000004_create_stock_reconciliation_physical_verification_system
```

---

### Step 2: Initialize Smart Reordering System

```bash
php artisan inventory:init-reordering
```

This command:
- ✅ Creates reorder point configurations for all products and branches
- ✅ Calculates initial average daily sales
- ✅ Sets up vendor lead times
- ✅ Generates initial 7-day demand forecasts

**Expected Output:**
```
Initializing reordering system...
Creating reorder point configurations...
Created 150 reorder point configurations
Generating initial demand forecasts...
Generated 1050 demand forecasts
Reordering system initialized successfully!
```

---

### Step 3: Configure Task Scheduling

Edit `app/Console/Kernel.php` and add:

```php
protected function schedule(Schedule $schedule)
{
    // Inventory management tasks (daily at 2:00 AM)
    $schedule->command('inventory:process-tasks')
             ->dailyAt('02:00')
             ->withoutOverlapping();
    
    // Generate cycle count sessions (daily at 1:00 AM)
    $schedule->command('inventory:generate-cycle-counts')
             ->dailyAt('01:00');
}
```

**Start the scheduler:**

For development:
```bash
php artisan schedule:work
```

For production (add to crontab):
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

### Step 4: Set Up Initial Warehouses (Optional)

```bash
php artisan tinker
```

```php
use App\Models\Warehouse;
use App\Models\Branch;

// Create main central warehouse
$mainWarehouse = Warehouse::create([
    'code' => 'WH-CENTRAL-001',
    'name' => 'Main Central Warehouse',
    'type' => 'central',
    'address' => 'Main Warehouse Complex, Industrial Area',
    'city' => 'Mumbai',
    'state' => 'Maharashtra',
    'pincode' => '400001',
    'storage_capacity' => 100000, // 100 tons
    'storage_zones' => ['cold_1', 'cold_2', 'dry_1', 'dry_2', 'freezer_1'],
    'manager_name' => 'Warehouse Manager',
    'manager_phone' => '9876543210',
    'is_active' => true,
]);

// Create cold storage warehouse
$coldStorage = Warehouse::create([
    'code' => 'WH-COLD-001',
    'name' => 'Cold Storage Facility',
    'type' => 'cold_storage',
    'address' => 'Cold Storage Complex',
    'city' => 'Mumbai',
    'storage_capacity' => 50000,
    'storage_zones' => ['cold_1', 'cold_2', 'cold_3'],
    'is_active' => true,
]);

// Set up proximity data for branches
use App\Models\WarehouseBranchProximity;

$branches = Branch::all();
foreach ($branches as $branch) {
    WarehouseBranchProximity::create([
        'warehouse_id' => $mainWarehouse->id,
        'branch_id' => $branch->id,
        'distance_km' => rand(5, 50), // Set actual distances
        'estimated_travel_minutes' => rand(30, 180),
        'standard_transfer_cost' => rand(200, 1000),
        'is_primary_supplier' => true,
    ]);
}
```

---

### Step 5: Configure Cycle Count Schedules

```php
use App\Models\CycleCountSchedule;
use App\Models\Branch;

$branches = Branch::all();

foreach ($branches as $branch) {
    // Monthly count for fruits
    CycleCountSchedule::create([
        'name' => "Monthly Fruit Count - {$branch->name}",
        'branch_id' => $branch->id,
        'product_category' => 'fruit',
        'frequency' => 'monthly',
        'schedule_days' => [1], // 1st of each month
        'next_count_date' => now()->startOfMonth()->toDateString(),
        'is_active' => true,
    ]);

    // Monthly count for vegetables
    CycleCountSchedule::create([
        'name' => "Monthly Vegetable Count - {$branch->name}",
        'branch_id' => $branch->id,
        'product_category' => 'vegetable',
        'frequency' => 'monthly',
        'schedule_days' => [15], // 15th of each month
        'next_count_date' => now()->startOfMonth()->addDays(14)->toDateString(),
        'is_active' => true,
    ]);
}
```

---

### Step 6: Add Seasonal Adjustments

```php
use App\Models\SeasonalAdjustment;

// Diwali festival (increased demand)
SeasonalAdjustment::create([
    'name' => 'Diwali Festival',
    'start_date' => '2025-10-20',
    'end_date' => '2025-11-05',
    'demand_multiplier' => 1.5, // 50% increase
    'description' => 'Increased demand during Diwali festival season',
    'is_active' => true,
]);

// Summer season (increased fruit demand)
SeasonalAdjustment::create([
    'name' => 'Summer Season',
    'start_date' => '2025-04-01',
    'end_date' => '2025-06-30',
    'category' => 'fruit',
    'demand_multiplier' => 1.3, // 30% increase
    'description' => 'Higher fruit consumption in summer',
    'is_active' => true,
]);

// Monsoon season (reduced demand for leafy vegetables)
SeasonalAdjustment::create([
    'name' => 'Monsoon Season',
    'start_date' => '2025-07-01',
    'end_date' => '2025-09-30',
    'category' => 'leafy',
    'demand_multiplier' => 0.8, // 20% decrease
    'description' => 'Lower demand for leafy vegetables in monsoon',
    'is_active' => true,
]);
```

---

### Step 7: Enable Auto-Reordering for Products

```php
use App\Models\Product;

// Enable auto-reordering for all active products
Product::active()->update([
    'enable_auto_reorder' => true,
    'min_order_quantity' => 10, // Minimum 10 kg/units
]);

// Set optimal stock levels for high-demand products
Product::whereIn('category', ['fruit', 'vegetable'])
       ->update([
           'optimal_stock_level' => 100, // 7 days worth
       ]);
```

---

### Step 8: Test the System

#### 8.1 Test Smart Reordering

```bash
php artisan inventory:process-tasks
```

Check the output for:
- ✅ Reorder configs recalculated
- ✅ Forecasts generated
- ✅ Alerts generated
- ✅ Auto POs generated

#### 8.2 Test Expiry Alerts

```php
use App\Models\ExpiryAlert;

$alerts = ExpiryAlert::generateAlerts();
echo "Generated {$alerts} expiry alerts\n";

// View unacknowledged alerts
$unacknowledged = ExpiryAlert::unacknowledged()->get();
```

#### 8.3 Test Price Adjustments

```php
use App\Models\BatchPriceAdjustment;

$discounts = BatchPriceAdjustment::applyAutomaticDiscounts();
echo "Applied {$discounts} near-expiry discounts\n";
```

#### 8.4 View Dashboard Stats

```php
use App\Services\SmartReorderingService;

$service = new SmartReorderingService();
$stats = $service->getDashboardStats();

print_r($stats);
```

---

### Step 9: Verify Database Records

```bash
php artisan tinker
```

```php
// Check reorder configs
\App\Models\ReorderPointConfig::count();

// Check forecasts
\App\Models\DemandForecast::count();

// Check alerts
\App\Models\ReorderAlert::unresolved()->count();

// Check auto POs
\App\Models\AutoPurchaseOrder::pending()->count();

// Check expiry alerts
\App\Models\ExpiryAlert::unacknowledged()->count();
```

---

## Verification Checklist

- [ ] All migrations run successfully
- [ ] Reorder point configs created for all products
- [ ] Demand forecasts generated
- [ ] Task scheduler configured and running
- [ ] Warehouses created (if using multi-warehouse)
- [ ] Cycle count schedules created
- [ ] Seasonal adjustments added
- [ ] Auto-reordering enabled for products
- [ ] Test commands run successfully
- [ ] Dashboard stats show correct data

---

## Common Commands

```bash
# Run all inventory tasks manually
php artisan inventory:process-tasks

# Run tasks asynchronously (requires queue worker)
php artisan inventory:process-tasks --async

# Initialize/reinitialize reordering system
php artisan inventory:init-reordering

# Generate cycle count sessions
php artisan inventory:generate-cycle-counts

# View scheduled tasks
php artisan schedule:list

# Run scheduler (development)
php artisan schedule:work

# Start queue worker (if using async)
php artisan queue:work
```

---

## Configuration Options

### Reorder Point Configuration

Edit in database or through code:
```php
$config = ReorderPointConfig::find($id);
$config->update([
    'lead_time_days' => 3,           // Adjust based on vendor
    'safety_stock_days' => 3,        // Increase for critical items
    'calculation_period_days' => 60, // Use longer period for stable items
    'auto_reorder_enabled' => true,  // Enable/disable auto-ordering
]);
```

### Variance Tolerance

```php
use App\Models\VarianceToleranceSetting;

VarianceToleranceSetting::create([
    'branch_id' => $branch->id,
    'product_category' => 'fruit',
    'tolerance_percentage' => 2.0, // 2% tolerance
    'auto_adjust_within_tolerance' => true,
    'require_approval_outside_tolerance' => true,
]);
```

---

## Monitoring & Maintenance

### Daily Tasks
1. Review pending auto purchase orders
2. Acknowledge expiry alerts
3. Review reorder alerts
4. Monitor wastage reports

### Weekly Tasks
1. Review forecast accuracy
2. Analyze variance reports
3. Review warehouse utilization
4. Check inventory accuracy metrics

### Monthly Tasks
1. Update seasonal adjustments
2. Review vendor lead times
3. Analyze wastage trends
4. Optimize reorder points
5. Review cycle count schedules

---

## Troubleshooting

### Issue: No reorder configs generated

**Solution:**
```bash
# Check if products exist
php artisan tinker
>>> App\Models\Product::count()

# Check if branches exist
>>> App\Models\Branch::count()

# Re-run initialization
php artisan inventory:init-reordering
```

### Issue: Forecasts not generating

**Solution:**
```php
// Check if there's sales history
use App\Models\OrderItem;
$salesCount = OrderItem::whereHas('order', function($q) {
    $q->where('status', 'completed')
      ->where('created_at', '>=', now()->subDays(30));
})->count();

// If no sales history, add sample data or wait for orders
```

### Issue: Scheduler not running

**Solution:**
```bash
# Test scheduler manually
php artisan schedule:run

# Check if cron is set up (production)
crontab -l

# View scheduled tasks
php artisan schedule:list
```

---

## Next Steps

1. **Configure API endpoints** (see ADVANCED_INVENTORY_MANAGEMENT_FEATURES.md)
2. **Build dashboard views** for monitoring
3. **Train staff** on new features
4. **Set up mobile scanning** for physical counts
5. **Integrate with IoT sensors** (optional)
6. **Customize alerts** and notifications
7. **Fine-tune reorder points** based on actual performance

---

## Support & Documentation

- **Full Documentation:** `ADVANCED_INVENTORY_MANAGEMENT_FEATURES.md`
- **System Logs:** `storage/logs/laravel.log`
- **Database Schema:** Review migration files in `database/migrations/`
- **Model Documentation:** Check PHPDoc comments in model files

---

**Setup Time:** ~30 minutes
**Last Updated:** October 1, 2025
**Version:** 1.0.0
