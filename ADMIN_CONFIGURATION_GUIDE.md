# 🔧 Admin Configuration Guide - Food Company Management System

## 📋 Table of Contents
1. [System Administration](#system-administration)
2. [Advanced Configuration](#advanced-configuration)
3. [Performance Optimization](#performance-optimization)
4. [Security Configuration](#security-configuration)
5. [Backup and Recovery](#backup-and-recovery)
6. [Monitoring and Alerts](#monitoring-and-alerts)
7. [API Configuration](#api-configuration)
8. [Automated Tasks Setup](#automated-tasks-setup)

## 🛠️ System Administration

### User Management

#### Role Configuration
```php
// Access via: php artisan tinker

// Create custom roles
$customRole = App\Models\Role::create([
    'name' => 'store_supervisor',
    'display_name' => 'Store Supervisor',
    'description' => 'Supervises store operations with limited admin access'
]);

// Assign permissions (if using permission system)
$permissions = ['view_inventory', 'manage_orders', 'view_reports'];
$customRole->permissions()->sync($permissions);
```

#### Bulk User Creation
```php
// Create multiple users from array
$users = [
    [
        'name' => 'Store Manager 1',
        'email' => 'manager1@company.com',
        'role' => 'branch_manager',
        'branch_id' => 1,
    ],
    [
        'name' => 'Cashier 1',
        'email' => 'cashier1@company.com', 
        'role' => 'cashier',
        'branch_id' => 1,
    ],
    // Add more users...
];

foreach ($users as $userData) {
    App\Models\User::create(array_merge($userData, [
        'password' => Hash::make('default123'),
        'is_active' => true,
    ]));
}
```

### Branch Configuration

#### Advanced Branch Settings
```php
// Configure branch-specific settings
$branch = App\Models\Branch::find(1);

// Add custom configuration
$branch->update([
    'timezone' => 'Asia/Kolkata',
    'business_hours' => json_encode([
        'monday' => ['09:00', '21:00'],
        'tuesday' => ['09:00', '21:00'],
        'wednesday' => ['09:00', '21:00'],
        'thursday' => ['09:00', '21:00'],
        'friday' => ['09:00', '21:00'],
        'saturday' => ['09:00', '22:00'],
        'sunday' => ['10:00', '20:00'],
    ]),
    'delivery_radius' => 15, // km
    'min_order_amount' => 200, // ₹
    'delivery_charges' => 30, // ₹
]);
```

## ⚙️ Advanced Configuration

### Environment Variables

#### Production Configuration
```env
# Performance Settings
APP_ENV=production
APP_DEBUG=false
APP_LOG_LEVEL=error

# Database Optimization
DB_CONNECTION=mysql
DB_STRICT=false
DB_ENGINE=InnoDB

# Cache Configuration
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-business-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourcompany.com
MAIL_FROM_NAME="${APP_NAME}"

# File Storage (for production)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-aws-key
AWS_SECRET_ACCESS_KEY=your-aws-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name

# Backup Configuration
BACKUP_DISK=s3
BACKUP_NOTIFICATION_EMAIL=admin@yourcompany.com
```

### System Configuration

#### Stock Threshold Configuration
```php
// Set global threshold multipliers
config(['inventory.threshold_multipliers' => [
    'fruit' => 1.5,        // Fruits need higher stock
    'vegetable' => 1.2,    // Vegetables moderate stock
    'leafy' => 2.0,        // Leafy vegetables need highest stock
    'exotic' => 1.0,       // Exotic items normal stock
]]);

// Perishability settings
config(['inventory.shelf_life_defaults' => [
    'fruit' => 7,          // days
    'vegetable' => 5,      // days  
    'leafy' => 2,          // days
    'exotic' => 10,        // days
    'herbs' => 3,          // days
    'dry_fruits' => 365,   // days
]]);
```

#### Loss Tracking Configuration
```php
// Configure loss thresholds
config(['loss_tracking.critical_thresholds' => [
    'financial_loss' => 1000,    // ₹1000+ is critical
    'quantity_percentage' => 10, // 10%+ loss is critical
    'frequency_threshold' => 5,  // 5+ losses per week is critical
]]);

// Auto-processing settings
config(['loss_tracking.auto_process' => [
    'expired_batches' => true,
    'weight_loss_threshold' => 5, // Auto-record if >5% weight loss
    'notification_threshold' => 500, // Notify if loss >₹500
]]);
```

#### Expense Allocation Configuration
```php
// Default allocation rules
config(['expense_allocation.default_rules' => [
    'transport' => 'equal',     // Equal distribution
    'labour' => 'weighted',     // By sales volume
    'operational' => 'equal',   // Equal distribution
    'overhead' => 'equal',      // Equal distribution
]]);

// Allocation weights
config(['expense_allocation.weights' => [
    'sales_volume' => 0.6,      // 60% weight to sales volume
    'stock_value' => 0.3,       // 30% weight to stock value
    'product_margin' => 0.1,    // 10% weight to profit margin
]]);
```

### Wholesale Configuration

#### Pricing Tier Templates
```php
// Create default pricing tiers for new products
$defaultTiers = [
    'distributor' => [
        ['min' => 100, 'max' => 499, 'discount' => 15],
        ['min' => 500, 'max' => 999, 'discount' => 20],
        ['min' => 1000, 'max' => null, 'discount' => 25],
    ],
    'retailer' => [
        ['min' => 50, 'max' => 199, 'discount' => 8],
        ['min' => 200, 'max' => 499, 'discount' => 12],
        ['min' => 500, 'max' => null, 'discount' => 15],
    ],
    'regular_wholesale' => [
        ['min' => 25, 'max' => 99, 'discount' => 5],
        ['min' => 100, 'max' => 299, 'discount' => 8],
        ['min' => 300, 'max' => null, 'discount' => 12],
    ],
];

// Apply to all products
foreach (App\Models\Product::active()->get() as $product) {
    foreach ($defaultTiers as $customerType => $tiers) {
        foreach ($tiers as $tier) {
            $discountPrice = $product->selling_price * (1 - $tier['discount'] / 100);
            
            App\Models\WholesalePricing::create([
                'product_id' => $product->id,
                'customer_type' => $customerType,
                'min_quantity' => $tier['min'],
                'max_quantity' => $tier['max'],
                'wholesale_price' => $discountPrice,
                'discount_percentage' => $tier['discount'],
                'is_active' => true,
            ]);
        }
    }
}
```

## 🚀 Performance Optimization

### Database Optimization

#### Add Performance Indexes
```sql
-- Inventory performance indexes
CREATE INDEX idx_product_branches_stock ON product_branches(current_stock, is_available_online);
CREATE INDEX idx_batches_expiry ON batches(expiry_date, status, current_quantity);
CREATE INDEX idx_stock_movements_date ON stock_movements(movement_date, product_id, branch_id);

-- Loss tracking indexes
CREATE INDEX idx_loss_tracking_type_date ON loss_tracking(loss_type, created_at);
CREATE INDEX idx_loss_tracking_financial ON loss_tracking(financial_loss, created_at);

-- Order performance indexes
CREATE INDEX idx_orders_date_status ON orders(order_date, status, order_type);
CREATE INDEX idx_order_items_product ON order_items(product_id, quantity);

-- Expense allocation indexes
CREATE INDEX idx_expense_allocations_product ON expense_allocations(product_id, allocation_date);
CREATE INDEX idx_expenses_type_date ON expenses(expense_type, expense_date);

-- Wholesale pricing indexes
CREATE INDEX idx_wholesale_pricing_lookup ON wholesale_pricing(product_id, customer_type, min_quantity);
```

#### Database Configuration Tuning
```sql
-- MySQL optimization settings
SET GLOBAL innodb_buffer_pool_size = 1073741824; -- 1GB
SET GLOBAL query_cache_size = 268435456; -- 256MB
SET GLOBAL tmp_table_size = 134217728; -- 128MB
SET GLOBAL max_heap_table_size = 134217728; -- 128MB
```

### Application Performance

#### Enable OPcache
```ini
; Add to php.ini
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.save_comments=1
opcache.fast_shutdown=1
```

#### Laravel Optimization
```bash
# Production optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Database query optimization
composer require barryvdh/laravel-debugbar --dev
composer require barryvdh/laravel-ide-helper --dev
```

### Queue Optimization
```bash
# Use Redis for better queue performance
composer require predis/predis

# Configure multiple queue workers
php artisan queue:work --queue=high,default,low --sleep=3 --tries=3
```

## 🔒 Security Configuration

### API Security

#### Rate Limiting
```php
// In app/Http/Kernel.php, configure rate limiting
'api' => [
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],

// Custom rate limits in RouteServiceProvider
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

// For mobile app (higher limits)
RateLimiter::for('mobile', function (Request $request) {
    return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
});
```

#### API Authentication
```php
// Configure Sanctum for API authentication
// In config/sanctum.php
'expiration' => 60 * 24, // 24 hours token expiration

// Create API tokens for different purposes
$user = App\Models\User::find(1);

// Mobile app token (longer expiration)
$mobileToken = $user->createToken('mobile-app', ['*'], now()->addDays(30));

// POS system token (limited permissions)
$posToken = $user->createToken('pos-system', ['orders:create', 'inventory:read']);

// Delivery app token
$deliveryToken = $user->createToken('delivery-app', ['delivery:*']);
```

### Data Validation and Sanitization
```php
// Custom validation rules in App\Rules\

// Validate product codes
class ProductCodeRule implements Rule
{
    public function passes($attribute, $value)
    {
        return preg_match('/^[A-Z0-9-]{3,20}$/', $value);
    }
}

// Validate quantities
class QuantityRule implements Rule
{
    public function passes($attribute, $value)
    {
        return is_numeric($value) && $value > 0 && $value <= 1000;
    }
}
```

## 💾 Backup and Recovery

### Automated Backup Setup

#### Database Backup Script
```bash
#!/bin/bash
# /usr/local/bin/food-company-backup.sh

BACKUP_DIR="/var/backups/food-company"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="food_company_db"
DB_USER="your_db_user"
DB_PASS="your_db_password"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup with compression
mysqldump -u $DB_USER -p$DB_PASS \
  --single-transaction \
  --routines \
  --triggers \
  $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Application files backup
tar -czf $BACKUP_DIR/app_backup_$DATE.tar.gz \
  --exclude='vendor' \
  --exclude='node_modules' \
  --exclude='storage/logs' \
  --exclude='storage/framework/cache' \
  /var/www/food-company

# Upload to cloud storage (optional)
aws s3 cp $BACKUP_DIR/db_backup_$DATE.sql.gz s3://your-backup-bucket/database/
aws s3 cp $BACKUP_DIR/app_backup_$DATE.tar.gz s3://your-backup-bucket/application/

# Keep only last 30 days locally
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
```

#### Recovery Procedures
```bash
# Database recovery
gunzip < /var/backups/food-company/db_backup_20240115_030000.sql.gz | \
mysql -u your_db_user -p your_db_password food_company_db

# Application recovery
cd /var/www
sudo rm -rf food-company
sudo tar -xzf /var/backups/food-company/app_backup_20240115_030000.tar.gz
sudo chown -R www-data:www-data food-company
```

### Point-in-Time Recovery
```bash
# Enable MySQL binary logging
# Add to /etc/mysql/mysql.conf.d/mysqld.cnf
log-bin=/var/log/mysql/mysql-bin.log
binlog_format=ROW
expire_logs_days=7

# Restore to specific point in time
mysqlbinlog --start-datetime="2024-01-15 14:30:00" \
           --stop-datetime="2024-01-15 14:35:00" \
           /var/log/mysql/mysql-bin.000001 | \
mysql -u root -p food_company_db
```

## 📊 Monitoring and Alerts

### System Health Monitoring

#### Laravel Health Checks
```php
// Create health check endpoint
// In routes/api.php
Route::get('/health', function () {
    $checks = [
        'database' => DB::connection()->getPdo() ? 'OK' : 'FAIL',
        'cache' => Cache::store()->put('health_check', 'OK', 60) ? 'OK' : 'FAIL',
        'queue' => Queue::size() < 100 ? 'OK' : 'HIGH_LOAD',
        'storage' => Storage::disk('local')->exists('.') ? 'OK' : 'FAIL',
    ];
    
    $status = in_array('FAIL', $checks) ? 500 : 200;
    
    return response()->json([
        'status' => $status === 200 ? 'healthy' : 'unhealthy',
        'checks' => $checks,
        'timestamp' => now(),
    ], $status);
});
```

#### Custom Monitoring Script
```bash
#!/bin/bash
# /usr/local/bin/monitor-food-company.sh

LOG_FILE="/var/log/food-company-monitor.log"
HEALTH_URL="http://localhost:8000/api/health"

# Check application health
HEALTH_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" $HEALTH_URL)

if [ $HEALTH_RESPONSE -ne 200 ]; then
    echo "$(date): Application health check failed (HTTP $HEALTH_RESPONSE)" >> $LOG_FILE
    # Send alert email
    echo "Food Company System Alert: Health check failed" | \
    mail -s "System Alert" admin@yourcompany.com
fi

# Check disk space
DISK_USAGE=$(df /var/www/food-company | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 85 ]; then
    echo "$(date): Disk usage high: ${DISK_USAGE}%" >> $LOG_FILE
fi

# Check queue size
QUEUE_SIZE=$(php /var/www/food-company/artisan queue:monitor | grep -o '[0-9]*' | head -1)
if [ $QUEUE_SIZE -gt 50 ]; then
    echo "$(date): Queue size high: $QUEUE_SIZE jobs" >> $LOG_FILE
fi

# Check for errors in logs
ERROR_COUNT=$(tail -n 100 /var/www/food-company/storage/logs/laravel.log | grep -c ERROR)
if [ $ERROR_COUNT -gt 5 ]; then
    echo "$(date): High error count in logs: $ERROR_COUNT errors" >> $LOG_FILE
fi
```

### Business Monitoring

#### Stock Alert Configuration
```php
// Configure automated stock alerts
// In app/Console/Commands/StockAlertCommand.php

class StockAlertCommand extends Command
{
    protected $signature = 'stock:alerts {--email} {--sms}';
    
    public function handle()
    {
        $inventoryService = new InventoryService();
        $alerts = $inventoryService->getStockAlerts();
        
        if (!empty($alerts['low_stock']) || !empty($alerts['out_of_stock'])) {
            $this->sendStockAlerts($alerts);
        }
        
        if (!empty($alerts['expiring_soon'])) {
            $this->sendExpiryAlerts($alerts['expiring_soon']);
        }
    }
    
    private function sendStockAlerts($alerts)
    {
        // Send email alerts to managers
        $managers = User::where('role', 'branch_manager')->get();
        
        foreach ($managers as $manager) {
            Mail::to($manager->email)->send(new StockAlertMail($alerts));
        }
    }
}
```

## 🔧 API Configuration

### API Rate Limiting by Role
```php
// In app/Http/Middleware/RoleBasedRateLimit.php

public function handle($request, Closure $next, ...$guards)
{
    $user = $request->user();
    
    $limits = [
        'admin' => 1000,        // 1000 requests per minute
        'branch_manager' => 500, // 500 requests per minute
        'cashier' => 200,       // 200 requests per minute
        'delivery_boy' => 100,  // 100 requests per minute
    ];
    
    $limit = $limits[$user->role] ?? 60;
    
    return RateLimiter::attempt(
        'api-' . $user->id,
        $limit,
        function () use ($next, $request) {
            return $next($request);
        },
        60
    );
}
```

### API Response Standardization
```php
// In app/Http/Controllers/Controller.php

protected function successResponse($data, $message = 'Success', $code = 200)
{
    return response()->json([
        'status' => 'success',
        'message' => $message,
        'data' => $data,
        'timestamp' => now(),
    ], $code);
}

protected function errorResponse($message, $errors = null, $code = 400)
{
    return response()->json([
        'status' => 'error',
        'message' => $message,
        'errors' => $errors,
        'timestamp' => now(),
    ], $code);
}
```

## ⏰ Automated Tasks Setup

### Cron Jobs Configuration
```bash
# Edit crontab for www-data user
sudo crontab -u www-data -e
```

```cron
# Food Company Management System Automated Tasks

# Process automated inventory tasks daily at 2 AM
0 2 * * * cd /var/www/food-company && php artisan system:process-automated-tasks

# Send stock alerts every morning at 8 AM
0 8 * * * cd /var/www/food-company && php artisan stock:alerts --email

# Process expired batches every 6 hours
0 */6 * * * cd /var/www/food-company && php artisan system:process-automated-tasks --expired-batches

# Generate daily reports at 11 PM
0 23 * * * cd /var/www/food-company && php artisan reports:generate-daily

# Clean up old logs weekly
0 3 * * 0 cd /var/www/food-company && php artisan log:clear --days=30

# Queue worker monitoring (restart if not running)
* * * * * cd /var/www/food-company && php artisan queue:restart

# Database backup daily at 3 AM
0 3 * * * /usr/local/bin/food-company-backup.sh

# System health check every 15 minutes
*/15 * * * * /usr/local/bin/monitor-food-company.sh
```

### Custom Automated Tasks

#### Auto Expense Allocation
```php
// Create command: php artisan make:command AutoAllocateExpenses

class AutoAllocateExpenses extends Command
{
    protected $signature = 'expenses:auto-allocate {--date=}';
    
    public function handle()
    {
        $date = $this->option('date') ?? now()->yesterday()->format('Y-m-d');
        
        // Get unallocated expenses from yesterday
        $expenses = Expense::where('allocation_method', 'none')
                          ->whereDate('expense_date', $date)
                          ->where('status', 'approved')
                          ->get();
        
        foreach ($expenses as $expense) {
            $this->allocateExpense($expense);
        }
    }
    
    private function allocateExpense($expense)
    {
        // Auto-allocation logic based on expense type
        $products = Product::active()->get();
        
        switch ($expense->expense_type) {
            case 'transport':
                // Allocate equally to all products
                $this->allocateEqually($expense, $products);
                break;
                
            case 'labour':
                // Allocate by sales volume
                $this->allocateByVolume($expense, $products);
                break;
                
            default:
                // Equal allocation
                $this->allocateEqually($expense, $products);
        }
    }
}
```

### Scheduled Reports

#### Daily Sales Report
```php
// Create command: php artisan make:command GenerateDailyReport

class GenerateDailyReport extends Command
{
    protected $signature = 'reports:generate-daily {--email}';
    
    public function handle()
    {
        $yesterday = now()->yesterday();
        
        // Generate comprehensive daily report
        $report = [
            'date' => $yesterday->format('Y-m-d'),
            'sales_summary' => $this->getSalesSummary($yesterday),
            'inventory_alerts' => $this->getInventoryAlerts(),
            'loss_summary' => $this->getLossSummary($yesterday),
            'expense_summary' => $this->getExpenseSummary($yesterday),
        ];
        
        // Save report
        Storage::put("reports/daily/daily_report_{$yesterday->format('Y_m_d')}.json", 
                    json_encode($report, JSON_PRETTY_PRINT));
        
        // Email to managers if requested
        if ($this->option('email')) {
            $managers = User::where('role', 'branch_manager')->get();
            foreach ($managers as $manager) {
                Mail::to($manager->email)->send(new DailyReportMail($report));
            }
        }
    }
}
```

## 🎛️ Advanced Features Configuration

### Multi-Currency Support (Future Enhancement)
```php
// Add to config/app.php
'currencies' => [
    'INR' => ['symbol' => '₹', 'name' => 'Indian Rupee'],
    'USD' => ['symbol' => '$', 'name' => 'US Dollar'],
    'EUR' => ['symbol' => '€', 'name' => 'Euro'],
],

'default_currency' => 'INR',
```

### Integration Configuration

#### Payment Gateway Integration
```php
// Razorpay configuration
'razorpay' => [
    'key_id' => env('RAZORPAY_KEY_ID'),
    'key_secret' => env('RAZORPAY_KEY_SECRET'),
    'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
],

// PayU configuration  
'payu' => [
    'merchant_key' => env('PAYU_MERCHANT_KEY'),
    'salt' => env('PAYU_SALT'),
    'test_mode' => env('PAYU_TEST_MODE', true),
],
```

#### SMS Gateway Configuration
```php
// SMS configuration for alerts
'sms' => [
    'provider' => env('SMS_PROVIDER', 'textlocal'),
    'textlocal' => [
        'api_key' => env('TEXTLOCAL_API_KEY'),
        'sender' => env('TEXTLOCAL_SENDER', 'FDFOOD'),
    ],
    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_FROM_NUMBER'),
    ],
],
```

### Notification Configuration

#### Email Notifications
```php
// Configure notification channels
// In config/mail.php

'mailers' => [
    'alerts' => [
        'transport' => 'smtp',
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'alerts@yourcompany.com',
        'password' => 'app-specific-password',
    ],
    'reports' => [
        'transport' => 'smtp',
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'reports@yourcompany.com',
        'password' => 'app-specific-password',
    ],
];
```

#### Notification Rules
```php
// In app/Notifications/

// Stock alert notification
class StockAlertNotification extends Notification
{
    public function via($notifiable)
    {
        return ['mail', 'database', 'sms'];
    }
    
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Stock Alert - ' . $this->product->name)
            ->line('Stock is running low for ' . $this->product->name)
            ->line('Current Stock: ' . $this->currentStock . ' ' . $this->product->weight_unit)
            ->line('Threshold: ' . $this->product->stock_threshold . ' ' . $this->product->weight_unit)
            ->action('View Inventory', url('/inventory'))
            ->line('Please restock as soon as possible.');
    }
}
```

## 🔍 Troubleshooting and Debugging

### Debug Mode Configuration
```php
// For development debugging
// In .env
APP_DEBUG=true
LOG_LEVEL=debug

// Enable query logging
DB_LOG_QUERIES=true

// Enable detailed error pages
APP_DEBUG_BLACKLIST_EXCEPTION_TYPES=false
```

### Performance Debugging
```bash
# Install Laravel Telescope for debugging
composer require laravel/telescope
php artisan telescope:install
php artisan migrate

# Monitor queries, requests, and performance
# Access at: http://yourapp.com/telescope
```

### Log Management
```php
// Custom log channels in config/logging.php
'channels' => [
    'inventory' => [
        'driver' => 'daily',
        'path' => storage_path('logs/inventory.log'),
        'level' => 'info',
        'days' => 30,
    ],
    'sales' => [
        'driver' => 'daily', 
        'path' => storage_path('logs/sales.log'),
        'level' => 'info',
        'days' => 90,
    ],
    'losses' => [
        'driver' => 'daily',
        'path' => storage_path('logs/losses.log'),
        'level' => 'warning',
        'days' => 365,
    ],
];

// Usage in controllers
Log::channel('inventory')->info('Stock updated', [
    'product_id' => $product->id,
    'old_stock' => $oldStock,
    'new_stock' => $newStock,
]);
```

## 📈 Analytics and Reporting Configuration

### Business Intelligence Setup
```php
// Create custom analytics
class BusinessAnalytics
{
    public function getDashboardMetrics($branchId = null, $period = 'today')
    {
        $startDate = match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
        };
        
        return [
            'sales_metrics' => $this->getSalesMetrics($branchId, $startDate),
            'inventory_metrics' => $this->getInventoryMetrics($branchId),
            'loss_metrics' => $this->getLossMetrics($branchId, $startDate),
            'expense_metrics' => $this->getExpenseMetrics($branchId, $startDate),
            'profit_metrics' => $this->getProfitMetrics($branchId, $startDate),
        ];
    }
}
```

### Custom Report Builder
```php
// Create flexible report builder
class ReportBuilder
{
    public function build($reportType, $filters = [])
    {
        switch ($reportType) {
            case 'inventory_valuation':
                return $this->buildInventoryValuation($filters);
            case 'loss_analysis':
                return $this->buildLossAnalysis($filters);
            case 'expense_allocation':
                return $this->buildExpenseAllocation($filters);
            case 'wholesale_performance':
                return $this->buildWholesalePerformance($filters);
        }
    }
}
```

## 🚀 Scaling Configuration

### Multi-Server Setup

#### Load Balancer Configuration
```nginx
# Nginx load balancer configuration
upstream food_company_app {
    server 192.168.1.10:8000 weight=3;
    server 192.168.1.11:8000 weight=2;
    server 192.168.1.12:8000 weight=1;
}

server {
    listen 80;
    server_name yourcompany.com;
    
    location / {
        proxy_pass http://food_company_app;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
```

#### Database Clustering
```php
// Master-slave database configuration
// In config/database.php
'mysql' => [
    'read' => [
        'host' => ['192.168.1.20', '192.168.1.21'],
    ],
    'write' => [
        'host' => ['192.168.1.20'],
    ],
    'sticky' => true,
    // ... other config
],
```

### Cache Optimization
```php
// Redis cluster configuration
'redis' => [
    'client' => 'predis',
    'cluster' => [
        'redis-cluster-1:7000',
        'redis-cluster-2:7000', 
        'redis-cluster-3:7000',
    ],
    'options' => [
        'cluster' => 'redis',
    ],
],
```

## 📋 Maintenance Checklist

### Daily Maintenance
- [ ] Check **system health** status
- [ ] Review **error logs**
- [ ] Monitor **queue processing**
- [ ] Verify **backup completion**
- [ ] Check **disk space** usage

### Weekly Maintenance  
- [ ] Review **performance metrics**
- [ ] Analyze **slow queries**
- [ ] Update **system packages**
- [ ] Test **backup restoration**
- [ ] Review **security logs**

### Monthly Maintenance
- [ ] **Database optimization** and cleanup
- [ ] **Cache performance** review
- [ ] **Security audit**
- [ ] **Capacity planning** review
- [ ] **User access** audit

## 🆘 Emergency Procedures

### System Recovery
```bash
# Quick system recovery steps
1. Check system status: systemctl status nginx php8.2-fpm mysql
2. Check application logs: tail -f /var/www/food-company/storage/logs/laravel.log
3. Restart services: sudo systemctl restart nginx php8.2-fpm
4. Clear caches: php artisan cache:clear && php artisan config:clear
5. Check database: php artisan migrate:status
```

### Data Recovery
```bash
# Emergency data recovery
1. Stop application: sudo systemctl stop nginx
2. Restore database: mysql -u user -p database < backup.sql
3. Restore files: tar -xzf app_backup.tar.gz
4. Fix permissions: sudo chown -R www-data:www-data /var/www/food-company
5. Start application: sudo systemctl start nginx
```

## 📞 Support Contacts

### Internal Support
- **System Admin**: admin@yourcompany.com
- **Technical Lead**: tech@yourcompany.com
- **Business Manager**: manager@yourcompany.com

### Emergency Contacts
- **24/7 Support**: +91-XXXX-XXXXXX
- **Database Admin**: dba@yourcompany.com
- **Security Team**: security@yourcompany.com

---

## 🎯 Configuration Checklist

### Initial Setup
- [ ] Environment variables configured
- [ ] Database optimized with indexes
- [ ] Automated tasks scheduled
- [ ] Backup system configured
- [ ] Monitoring alerts set up

### Security
- [ ] SSL certificate installed
- [ ] Rate limiting configured
- [ ] User permissions reviewed
- [ ] API authentication secured
- [ ] Firewall rules applied

### Performance
- [ ] OPcache enabled
- [ ] Database optimized
- [ ] Cache strategy implemented
- [ ] Queue workers configured
- [ ] CDN configured (if applicable)

### Business Configuration
- [ ] Stock thresholds set
- [ ] Loss tracking rules configured
- [ ] Expense allocation methods set
- [ ] Wholesale pricing created
- [ ] Branch settings configured

Your enhanced food company management system is now fully configured for enterprise-level operations! 🎉