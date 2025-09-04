# 🔧 Troubleshooting Guide - Food Company Management System

## 🚨 Common Issues and Solutions

### 📦 Inventory Issues

#### Issue: Stock Not Updating After Sale
**Symptoms**: 
- Order completed but stock remains same
- No stock movement record created

**Diagnosis**:
```bash
# Check if InventoryService is being called
tail -f storage/logs/laravel.log | grep "Stock updated"

# Check stock movements table
php artisan tinker
>>> App\Models\StockMovement::latest()->take(5)->get();
```

**Solutions**:
1. **Check OrderController integration**:
```php
// Ensure this is in OrderController::store()
$inventoryService = new InventoryService();
$stockUpdated = $inventoryService->updateStockAfterSale($orderItem);
```

2. **Verify database transaction**:
```php
// Check if transaction is committing
DB::beginTransaction();
// ... order processing ...
DB::commit(); // Make sure this executes
```

3. **Check user permissions**:
```php
// Ensure user can update inventory
$user = auth()->user();
if (!$user->can('update_inventory')) {
    // Grant permission or check role
}
```

#### Issue: Product Not Going "Sold Out" Online
**Symptoms**:
- Stock below threshold but still available online
- Customers can still order out-of-stock items

**Diagnosis**:
```php
php artisan tinker
>>> $product = App\Models\Product::find(1);
>>> $branch = App\Models\Branch::find(1);
>>> $currentStock = $product->getCurrentStock($branch->id);
>>> $threshold = $product->stock_threshold;
>>> $isOnline = $product->branches()->where('branches.id', $branch->id)->first()->pivot->is_available_online;
>>> echo "Stock: $currentStock, Threshold: $threshold, Online: " . ($isOnline ? 'YES' : 'NO');
```

**Solutions**:
1. **Manual threshold update**:
```php
$inventoryService = new InventoryService();
$inventoryService->checkAndUpdateOnlineAvailability($product, $branch->id, $currentStock);
```

2. **Run automated task**:
```bash
php artisan system:process-automated-tasks --online-availability
```

3. **Check threshold settings**:
```php
// Update threshold if needed
$product->update(['stock_threshold' => 10]); // Increase threshold
```

### 📉 Loss Tracking Issues

#### Issue: Loss Not Recording Properly
**Symptoms**:
- Loss tracking form submits but no record created
- Stock not reducing after loss recording

**Diagnosis**:
```php
# Check recent loss records
php artisan tinker
>>> App\Models\LossTracking::latest()->take(3)->get();

# Check if stock is updating
>>> $product = App\Models\Product::find(1);
>>> $product->getCurrentStock(1); // Check current stock
```

**Solutions**:
1. **Check validation errors**:
```php
// Add validation debugging
$validator = Validator::make($request->all(), $rules);
if ($validator->fails()) {
    Log::error('Loss tracking validation failed', $validator->errors()->toArray());
}
```

2. **Verify database constraints**:
```sql
-- Check if foreign key constraints are causing issues
SHOW CREATE TABLE loss_tracking;
```

3. **Manual loss recording**:
```php
// Record loss manually for testing
App\Models\LossTracking::create([
    'product_id' => 1,
    'branch_id' => 1,
    'loss_type' => 'wastage',
    'quantity_lost' => 1.0,
    'financial_loss' => 100.00,
    'reason' => 'Test loss',
    'user_id' => 1,
]);
```

### 💰 Expense Allocation Issues

#### Issue: Expenses Not Allocating to Products
**Symptoms**:
- Expense created but no allocation records
- Product costs not updating

**Diagnosis**:
```php
php artisan tinker
>>> $expense = App\Models\Expense::latest()->first();
>>> $expense->allocations; // Should show allocation records
>>> $expense->allocation_method; // Check allocation method
```

**Solutions**:
1. **Check allocation method**:
```php
// Ensure allocation method is not 'none' or 'manual'
$expense->update(['allocation_method' => 'equal']);
```

2. **Manual allocation**:
```php
// Allocate manually
$products = App\Models\Product::active()->take(5)->get();
$amountPerProduct = $expense->amount / $products->count();

foreach ($products as $product) {
    $expense->allocations()->create([
        'product_id' => $product->id,
        'allocated_amount' => $amountPerProduct,
        'allocation_date' => now(),
    ]);
}
```

3. **Check ExpenseAllocation model**:
```php
// Verify model exists and is properly configured
>>> App\Models\ExpenseAllocation::count();
```

### 🚚 Delivery Issues

#### Issue: Delivery Boy Can't Access Orders
**Symptoms**:
- Empty delivery orders list
- "Unauthorized access" errors

**Diagnosis**:
```php
php artisan tinker
>>> $user = App\Models\User::where('email', 'delivery@foodcompany.com')->first();
>>> $user->role; // Should be 'delivery_boy'
>>> $user->branch_id; // Should have branch assigned
```

**Solutions**:
1. **Fix user role**:
```php
$user->update(['role' => 'delivery_boy']);
```

2. **Assign to branch**:
```php
$user->update(['branch_id' => 1]);
```

3. **Check delivery assignments**:
```php
// Assign orders to delivery boy
$orders = App\Models\Order::where('order_type', 'online')
                          ->where('status', 'confirmed')
                          ->get();

foreach ($orders as $order) {
    $order->delivery()->create([
        'delivery_boy_id' => $user->id,
        'status' => 'assigned',
        'assigned_at' => now(),
    ]);
}
```

### 🏭 Wholesale Pricing Issues

#### Issue: Wholesale Pricing Not Applying
**Symptoms**:
- Wholesale customers getting regular prices
- Discount calculations not working

**Diagnosis**:
```php
php artisan tinker
>>> $customer = App\Models\Customer::where('customer_type', 'distributor')->first();
>>> $product = App\Models\Product::first();
>>> $pricing = App\Models\WholesalePricing::where('product_id', $product->id)
                                          ->where('customer_type', $customer->customer_type)
                                          ->where('min_quantity', '<=', 100)
                                          ->first();
>>> $pricing; // Should return pricing tier
```

**Solutions**:
1. **Create missing pricing tiers**:
```php
App\Models\WholesalePricing::create([
    'product_id' => 1,
    'customer_type' => 'distributor',
    'min_quantity' => 50,
    'max_quantity' => null,
    'wholesale_price' => 85.00,
    'discount_percentage' => 15,
    'is_active' => true,
]);
```

2. **Check customer type**:
```php
// Ensure customer has correct type
$customer->update(['customer_type' => 'distributor']);
```

## 🔍 Database Issues

### Migration Problems

#### Issue: Migration Fails
**Error**: "Table already exists" or "Column already exists"

**Solutions**:
1. **Check migration status**:
```bash
php artisan migrate:status
```

2. **Reset migrations** (⚠️ CAUTION: Deletes all data):
```bash
php artisan migrate:reset
php artisan migrate
php artisan db:seed
```

3. **Skip problematic migration**:
```php
// Mark migration as run without executing
DB::table('migrations')->insert([
    'migration' => '2024_01_15_000001_enhance_expense_system',
    'batch' => 1
]);
```

4. **Fix migration manually**:
```php
// Check if table/column exists before creating
if (!Schema::hasTable('expense_allocations')) {
    Schema::create('expense_allocations', function (Blueprint $table) {
        // ... table definition
    });
}

if (!Schema::hasColumn('expenses', 'expense_type')) {
    Schema::table('expenses', function (Blueprint $table) {
        $table->string('expense_type')->default('operational');
    });
}
```

### Database Performance Issues

#### Issue: Slow Queries
**Symptoms**:
- Pages loading slowly
- API responses taking too long

**Diagnosis**:
```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;
SET GLOBAL slow_query_log_file = '/var/log/mysql/slow.log';

-- Check slow queries
tail -f /var/log/mysql/slow.log
```

**Solutions**:
1. **Add missing indexes**:
```sql
-- Frequently used queries
CREATE INDEX idx_orders_date_branch ON orders(order_date, branch_id);
CREATE INDEX idx_products_category_active ON products(category, is_active);
CREATE INDEX idx_loss_tracking_date_type ON loss_tracking(created_at, loss_type);
```

2. **Optimize queries**:
```php
// Use eager loading
$orders = Order::with(['orderItems.product', 'customer', 'branch'])->get();

// Use select to limit columns
$products = Product::select('id', 'name', 'selling_price')->get();

// Use pagination for large datasets
$orders = Order::paginate(20);
```

## 🌐 API Issues

### Authentication Problems

#### Issue: API Token Not Working
**Symptoms**:
- "Unauthenticated" errors
- Token expired messages

**Solutions**:
1. **Generate new token**:
```php
php artisan tinker
>>> $user = App\Models\User::find(1);
>>> $token = $user->createToken('api-token');
>>> echo $token->plainTextToken;
```

2. **Check token expiration**:
```php
// In config/sanctum.php
'expiration' => 60 * 24, // 24 hours

// Or create non-expiring token
$token = $user->createToken('api-token', ['*'], null);
```

3. **Verify middleware**:
```php
// Ensure routes are protected
Route::middleware('auth:sanctum')->group(function () {
    // API routes
});
```

### CORS Issues

#### Issue: Frontend Can't Access API
**Symptoms**:
- "CORS policy" errors in browser
- API calls failing from frontend

**Solutions**:
1. **Configure CORS**:
```bash
composer require fruitcake/laravel-cors
```

```php
// In config/cors.php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_origins' => ['http://localhost:3000', 'https://yourdomain.com'],
'allowed_origins_patterns' => [],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => true,
```

2. **Add CORS middleware**:
```php
// In app/Http/Kernel.php
protected $middleware = [
    // ...
    \Fruitcake\Cors\HandleCors::class,
];
```

## 📱 Mobile App Issues

### Location Tracking Problems

#### Issue: GPS Location Not Updating
**Symptoms**:
- Delivery tracking not working
- Location updates failing

**Solutions**:
1. **Check location permissions**:
```javascript
// In mobile app
navigator.geolocation.getCurrentPosition(
    position => {
        // Send to API
        fetch('/api/delivery/orders/123/location', {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
            }),
        });
    },
    error => console.error('Location error:', error)
);
```

2. **Verify API endpoint**:
```bash
# Test location update
curl -X PUT -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"latitude": 28.6139, "longitude": 77.2090}' \
     http://localhost:8000/api/delivery/orders/123/location
```

## 💾 Data Issues

### Data Inconsistency

#### Issue: Stock Numbers Don't Match
**Symptoms**:
- System stock differs from physical count
- Negative stock values

**Diagnosis**:
```php
php artisan tinker
>>> $product = App\Models\Product::find(1);
>>> $branch = App\Models\Branch::find(1);
>>> $systemStock = $product->getCurrentStock($branch->id);
>>> $movements = App\Models\StockMovement::where('product_id', 1)
                                         ->where('branch_id', 1)
                                         ->orderBy('movement_date', 'desc')
                                         ->take(10)
                                         ->get();
>>> $movements; // Review recent movements
```

**Solutions**:
1. **Stock reconciliation**:
```php
// Adjust stock to match physical count
$actualStock = 45.5; // Physical count
$systemStock = $product->getCurrentStock($branch->id);
$difference = $actualStock - $systemStock;

if ($difference != 0) {
    // Record adjustment
    App\Models\StockMovement::create([
        'product_id' => $product->id,
        'branch_id' => $branch->id,
        'movement_type' => 'adjustment',
        'quantity' => $difference,
        'reference_type' => 'stock_reconciliation',
        'user_id' => auth()->id(),
        'movement_date' => now(),
        'notes' => "Stock reconciliation - Physical count: {$actualStock}kg",
    ]);
    
    // Update actual stock
    $product->updateStock($branch->id, $actualStock);
}
```

2. **Audit stock movements**:
```php
// Check for missing movements
$totalIn = App\Models\StockMovement::where('product_id', 1)
                                   ->where('branch_id', 1)
                                   ->where('quantity', '>', 0)
                                   ->sum('quantity');

$totalOut = App\Models\StockMovement::where('product_id', 1)
                                    ->where('branch_id', 1)
                                    ->where('quantity', '<', 0)
                                    ->sum('quantity');

$calculatedStock = $totalIn + $totalOut; // totalOut is negative
```

### Order Processing Issues

#### Issue: Orders Stuck in Processing
**Symptoms**:
- Orders not moving to confirmed status
- Payment processed but order status unchanged

**Diagnosis**:
```bash
# Check queue jobs
php artisan queue:monitor

# Check failed jobs
php artisan queue:failed

# Check order status
php artisan tinker
>>> App\Models\Order::where('status', 'processing')->count();
```

**Solutions**:
1. **Process stuck orders manually**:
```php
php artisan tinker
>>> $stuckOrders = App\Models\Order::where('status', 'processing')
                                   ->where('created_at', '<', now()->subHours(1))
                                   ->get();
>>> foreach ($stuckOrders as $order) {
>>>     $order->update(['status' => 'confirmed']);
>>> }
```

2. **Restart queue workers**:
```bash
php artisan queue:restart
php artisan queue:work --tries=3
```

3. **Check for exceptions**:
```bash
# Review error logs
grep -i "error\|exception" storage/logs/laravel.log | tail -20
```

## 🔐 Permission Issues

### Role-Based Access Problems

#### Issue: Users Can't Access Features
**Symptoms**:
- "Unauthorized" errors
- Missing menu items or buttons

**Diagnosis**:
```php
php artisan tinker
>>> $user = App\Models\User::where('email', 'user@example.com')->first();
>>> $user->role; // Check assigned role
>>> $user->branch_id; // Check branch assignment
>>> $user->is_active; // Check if active
```

**Solutions**:
1. **Fix user role**:
```php
$user->update([
    'role' => 'branch_manager',
    'branch_id' => 1,
    'is_active' => true,
]);
```

2. **Check middleware**:
```php
// In routes/api.php, verify role middleware
Route::middleware('role:admin,branch_manager')->group(function () {
    // Protected routes
});
```

3. **Create missing role**:
```php
App\Models\Role::create([
    'name' => 'missing_role',
    'display_name' => 'Missing Role',
    'description' => 'Role description',
]);
```

## 💸 Payment Issues

### Payment Processing Problems

#### Issue: Payment Not Recording
**Symptoms**:
- Payment completed but not showing in system
- Order payment status not updating

**Diagnosis**:
```php
php artisan tinker
>>> $order = App\Models\Order::find(123);
>>> $order->payments; // Check payment records
>>> $order->payment_status; // Check status
>>> $totalPaid = $order->payments->sum('amount');
>>> echo "Total Paid: ₹{$totalPaid}, Order Total: ₹{$order->total_amount}";
```

**Solutions**:
1. **Manual payment record**:
```php
App\Models\Payment::create([
    'order_id' => $order->id,
    'customer_id' => $order->customer_id,
    'branch_id' => $order->branch_id,
    'user_id' => 1,
    'amount' => $order->total_amount,
    'payment_method' => 'cash',
    'payment_type' => 'order_payment',
    'payment_date' => now(),
    'status' => 'completed',
    'reference_number' => 'MANUAL-' . strtoupper(Str::random(8)),
]);

// Update order status
$order->update(['payment_status' => 'paid']);
```

2. **Fix payment gateway integration**:
```php
// Check payment gateway response
Log::info('Payment gateway response', $response);

// Verify webhook processing
// Check webhook endpoint is accessible
```

## 🔄 Queue and Job Issues

### Queue Not Processing

#### Issue: Background Jobs Not Running
**Symptoms**:
- Email notifications not sending
- Stock updates delayed
- Reports not generating

**Diagnosis**:
```bash
# Check queue status
php artisan queue:monitor

# Check queue table
php artisan tinker
>>> DB::table('jobs')->count(); // Pending jobs
>>> DB::table('failed_jobs')->count(); // Failed jobs
```

**Solutions**:
1. **Start queue worker**:
```bash
# Start queue worker
php artisan queue:work

# Or as daemon
nohup php artisan queue:work --daemon > /dev/null 2>&1 &
```

2. **Process failed jobs**:
```bash
# Retry failed jobs
php artisan queue:retry all

# Or retry specific job
php artisan queue:retry 5
```

3. **Clear stuck jobs**:
```bash
# Clear all jobs
php artisan queue:clear

# Restart queue workers
php artisan queue:restart
```

## 📊 Reporting Issues

### Report Generation Problems

#### Issue: Reports Not Loading
**Symptoms**:
- Report pages showing errors
- Empty or incorrect data

**Diagnosis**:
```php
php artisan tinker
>>> $startDate = '2024-01-01';
>>> $endDate = '2024-01-31';
>>> $orders = App\Models\Order::whereBetween('order_date', [$startDate, $endDate])->count();
>>> echo "Orders found: {$orders}";
```

**Solutions**:
1. **Check date filters**:
```php
// Ensure date format is correct
$startDate = Carbon::parse($request->start_date)->startOfDay();
$endDate = Carbon::parse($request->end_date)->endOfDay();
```

2. **Verify data relationships**:
```php
// Check if relationships are loading
$orders = App\Models\Order::with(['orderItems.product', 'customer', 'branch'])->get();
```

3. **Add missing data**:
```php
// Create sample data for testing
factory(App\Models\Order::class, 10)->create();
```

## 🔧 System Maintenance

### Cache Issues

#### Issue: Cached Data Not Updating
**Symptoms**:
- Old data still showing
- Configuration changes not reflecting

**Solutions**:
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Clear OPcache (if enabled)
sudo systemctl reload php8.2-fpm
```

### File Permission Issues

#### Issue: Permission Denied Errors
**Symptoms**:
- Can't write to storage
- Log files not updating

**Solutions**:
```bash
# Fix Laravel permissions
sudo chown -R www-data:www-data /var/www/food-company
sudo chmod -R 755 /var/www/food-company
sudo chmod -R 775 /var/www/food-company/storage
sudo chmod -R 775 /var/www/food-company/bootstrap/cache

# SELinux issues (if applicable)
sudo setsebool -P httpd_can_network_connect 1
sudo chcon -R -t httpd_exec_t /var/www/food-company
```

## 🚨 Emergency Procedures

### System Down Recovery

#### Complete System Failure
1. **Check system status**:
```bash
sudo systemctl status nginx php8.2-fpm mysql
```

2. **Restart services**:
```bash
sudo systemctl restart mysql
sudo systemctl restart php8.2-fpm  
sudo systemctl restart nginx
```

3. **Check application**:
```bash
cd /var/www/food-company
php artisan down # Maintenance mode
php artisan migrate:status
php artisan up # Exit maintenance mode
```

### Data Corruption Recovery

#### Database Corruption
1. **Stop application**:
```bash
php artisan down
sudo systemctl stop nginx
```

2. **Restore from backup**:
```bash
mysql -u root -p food_company_db < /var/backups/food-company/latest_backup.sql
```

3. **Verify data integrity**:
```php
php artisan tinker
>>> App\Models\Product::count();
>>> App\Models\Order::count();
>>> // Check critical data
```

4. **Restart application**:
```bash
sudo systemctl start nginx
php artisan up
```

## 🔍 Diagnostic Commands

### System Health Check
```bash
# Create comprehensive health check script
#!/bin/bash
echo "=== Food Company System Health Check ==="

echo "1. Application Status:"
curl -s http://localhost:8000/api/health | jq .

echo "2. Database Connection:"
php artisan tinker --execute="echo DB::connection()->getPdo() ? 'Connected' : 'Failed';"

echo "3. Queue Status:"
php artisan queue:monitor

echo "4. Cache Status:"
php artisan tinker --execute="echo Cache::put('test', 'ok', 60) ? 'Working' : 'Failed';"

echo "5. Storage Status:"
php artisan tinker --execute="echo Storage::put('test.txt', 'test') ? 'Working' : 'Failed';"

echo "6. Recent Errors:"
tail -n 10 storage/logs/laravel.log | grep ERROR

echo "=== Health Check Complete ==="
```

### Performance Analysis
```bash
# Performance analysis script
#!/bin/bash
echo "=== Performance Analysis ==="

echo "1. Database Query Performance:"
mysql -u root -p -e "
SELECT 
    ROUND(AVG_TIMER_WAIT/1000000000000,6) as avg_exec_time,
    COUNT_STAR as exec_count,
    DIGEST_TEXT 
FROM performance_schema.events_statements_summary_by_digest 
ORDER BY AVG_TIMER_WAIT DESC 
LIMIT 10;"

echo "2. PHP Memory Usage:"
php -r "echo 'Memory Limit: ' . ini_get('memory_limit') . PHP_EOL;"
php -r "echo 'Peak Memory: ' . memory_get_peak_usage(true) / 1024 / 1024 . 'MB' . PHP_EOL;"

echo "3. Queue Performance:"
php artisan queue:monitor --verbose

echo "=== Analysis Complete ==="
```

## 📞 Getting Help

### Log Analysis
```bash
# Search for specific errors
grep -i "inventory\|stock" storage/logs/laravel.log | tail -20
grep -i "expense\|allocation" storage/logs/laravel.log | tail -20
grep -i "wholesale\|pricing" storage/logs/laravel.log | tail -20

# Check error frequency
grep -c "ERROR" storage/logs/laravel.log
grep -c "CRITICAL" storage/logs/laravel.log
```

### Debug Information Collection
```bash
# Collect system information for support
echo "=== System Information ===" > debug_info.txt
echo "PHP Version: $(php -v | head -1)" >> debug_info.txt
echo "Laravel Version: $(php artisan --version)" >> debug_info.txt
echo "Database: $(mysql --version)" >> debug_info.txt
echo "Disk Space: $(df -h /var/www/food-company)" >> debug_info.txt
echo "Memory Usage: $(free -h)" >> debug_info.txt
echo "Recent Errors:" >> debug_info.txt
tail -n 50 storage/logs/laravel.log | grep ERROR >> debug_info.txt
```

## 🎯 Prevention Best Practices

### Regular Maintenance
1. **Daily**: Check error logs and system health
2. **Weekly**: Review performance metrics and optimize
3. **Monthly**: Update dependencies and security patches
4. **Quarterly**: Full system audit and optimization

### Monitoring Setup
1. **Set up alerts** for critical errors
2. **Monitor disk space** and memory usage
3. **Track API response times**
4. **Monitor queue processing** delays

### Documentation
1. **Keep change logs** for system modifications
2. **Document custom configurations**
3. **Maintain troubleshooting notes**
4. **Update user guides** after changes

---

## 🆘 Emergency Contacts

### Internal Team
- **System Administrator**: admin@yourcompany.com
- **Database Administrator**: dba@yourcompany.com
- **Technical Lead**: tech@yourcompany.com

### External Support
- **Hosting Provider**: support@hostingprovider.com
- **Database Support**: mysql-support@provider.com
- **Laravel Community**: https://laravel.com/support

Remember: Always test solutions in a development environment before applying to production! 🚀