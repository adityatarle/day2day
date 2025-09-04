# 🍎🥬 Food Company Management System - Complete Setup Guide

## 📋 Table of Contents
1. [System Requirements](#system-requirements)
2. [Installation Steps](#installation-steps)
3. [Database Configuration](#database-configuration)
4. [Enhanced Modules Setup](#enhanced-modules-setup)
5. [User Account Setup](#user-account-setup)
6. [Branch and Product Setup](#branch-and-product-setup)
7. [Testing the System](#testing-the-system)
8. [Production Deployment](#production-deployment)
9. [Troubleshooting](#troubleshooting)

## 🔧 System Requirements

### Minimum Requirements
- **PHP**: 8.2 or higher
- **Database**: MySQL 8.0+ / PostgreSQL 13+ / SQLite 3.8+
- **Web Server**: Apache 2.4+ / Nginx 1.18+
- **Node.js**: 18+ (for frontend assets)
- **Composer**: Latest version
- **Memory**: 512MB RAM minimum, 2GB recommended
- **Storage**: 1GB free space

### Recommended for Production
- **PHP**: 8.3 with OPcache enabled
- **Database**: MySQL 8.0+ with proper indexing
- **Memory**: 4GB+ RAM
- **Storage**: SSD with 10GB+ free space
- **SSL Certificate**: For HTTPS
- **Backup Solution**: Daily automated backups

## 🚀 Installation Steps

### Step 1: Clone or Download Project
```bash
# If using Git
git clone <your-repository-url>
cd food-company-management

# Or extract from ZIP file
unzip food-company-management.zip
cd food-company-management
```

### Step 2: Install PHP Dependencies
```bash
# Install Composer dependencies
composer install

# For production (optimized)
composer install --optimize-autoloader --no-dev
```

### Step 3: Install Node.js Dependencies
```bash
# Install Node.js dependencies
npm install

# Build frontend assets
npm run build

# For development (with hot reload)
npm run dev
```

### Step 4: Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 5: Configure Environment Variables
Edit the `.env` file with your settings:

```env
# Application Settings
APP_NAME="Food Company Management"
APP_ENV=local
APP_KEY=base64:your-generated-key
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=food_company_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Mail Configuration (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls

# Queue Configuration (for background jobs)
QUEUE_CONNECTION=database

# Session Configuration
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Cache Configuration
CACHE_DRIVER=file

# File Storage
FILESYSTEM_DISK=local
```

## 🗄️ Database Configuration

### Step 1: Create Database
```sql
-- For MySQL
CREATE DATABASE food_company_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user (optional)
CREATE USER 'food_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON food_company_db.* TO 'food_user'@'localhost';
FLUSH PRIVILEGES;
```

### Step 2: Run Database Setup
```bash
# Run all migrations (creates tables)
php artisan migrate

# Seed basic system data
php artisan db:seed

# Seed enhanced modules data
php artisan db:seed --class=EnhancedSystemSeeder
```

## 🆕 Enhanced Modules Setup

### Step 1: Run Enhanced Setup Script
```bash
# Make script executable
chmod +x setup_enhanced_modules.sh

# Run the enhanced setup
./setup_enhanced_modules.sh
```

### Step 2: Verify Module Installation
```bash
# Check if all tables are created
php artisan tinker
>>> DB::select("SHOW TABLES");
>>> exit
```

Expected tables should include:
- `expense_allocations`
- `wholesale_pricing`
- Enhanced columns in existing tables

### Step 3: Configure Automated Tasks (Optional)
```bash
# Add to crontab for automated processing
crontab -e

# Add this line for daily automated tasks at 2 AM
0 2 * * * cd /path/to/your/project && php artisan system:process-automated-tasks

# Add this line for queue processing
* * * * * cd /path/to/your/project && php artisan queue:work --stop-when-empty
```

## 👥 User Account Setup

### Step 1: Create Admin Account
```bash
php artisan tinker
```

```php
// Create admin user
$admin = App\Models\User::create([
    'name' => 'System Administrator',
    'email' => 'admin@foodcompany.com',
    'password' => Hash::make('admin123'),
    'role' => 'admin',
    'is_active' => true
]);

// Create admin role if not exists
$adminRole = App\Models\Role::firstOrCreate([
    'name' => 'admin',
    'display_name' => 'Administrator',
    'description' => 'Full system access'
]);

exit
```

### Step 2: Create Branch Manager
```php
php artisan tinker

// Create branch manager
$manager = App\Models\User::create([
    'name' => 'Branch Manager',
    'email' => 'manager@foodcompany.com',
    'password' => Hash::make('manager123'),
    'role' => 'branch_manager',
    'branch_id' => 1, // Assign to main branch
    'is_active' => true
]);

exit
```

### Step 3: Create Cashier Account
```php
php artisan tinker

// Create cashier
$cashier = App\Models\User::create([
    'name' => 'Shop Cashier',
    'email' => 'cashier@foodcompany.com',
    'password' => Hash::make('cashier123'),
    'role' => 'cashier',
    'branch_id' => 1,
    'is_active' => true
]);

exit
```

### Step 4: Create Delivery Boy Account
```php
php artisan tinker

// Create delivery boy
$deliveryBoy = App\Models\User::create([
    'name' => 'Delivery Boy',
    'email' => 'delivery@foodcompany.com',
    'password' => Hash::make('delivery123'),
    'role' => 'delivery_boy',
    'branch_id' => 1,
    'phone' => '9876543210',
    'address' => 'Delivery Staff Address',
    'is_active' => true
]);

exit
```

## 🏢 Branch and Product Setup

### Step 1: Create Branches
```bash
php artisan tinker
```

```php
// Create main branch
$mainBranch = App\Models\Branch::create([
    'name' => 'Main Store',
    'code' => 'MAIN',
    'address' => '123 Market Street, City',
    'phone' => '0123456789',
    'email' => 'main@foodcompany.com',
    'manager_name' => 'Branch Manager',
    'is_active' => true
]);

// Create second branch
$branch2 = App\Models\Branch::create([
    'name' => 'Mall Outlet',
    'code' => 'MALL',
    'address' => 'Shopping Mall, Floor 2',
    'phone' => '0123456790',
    'email' => 'mall@foodcompany.com',
    'manager_name' => 'Mall Manager',
    'is_active' => true
]);

exit
```

### Step 2: Create Sample Products
```php
php artisan tinker

// Create fruits
$apple = App\Models\Product::create([
    'name' => 'Fresh Red Apple',
    'code' => 'APPLE-RED',
    'description' => 'Premium quality red apples',
    'category' => 'fruit',
    'subcategory' => 'seasonal',
    'weight_unit' => 'kg',
    'purchase_price' => 80.00,
    'mrp' => 120.00,
    'selling_price' => 100.00,
    'stock_threshold' => 5,
    'shelf_life_days' => 10,
    'storage_temperature' => '2-8°C',
    'is_perishable' => true,
    'hsn_code' => '08081000',
    'is_active' => true
]);

$tomato = App\Models\Product::create([
    'name' => 'Fresh Tomatoes',
    'code' => 'TOMATO-FRESH',
    'description' => 'Farm fresh tomatoes',
    'category' => 'vegetable',
    'subcategory' => 'seasonal',
    'weight_unit' => 'kg',
    'purchase_price' => 30.00,
    'mrp' => 50.00,
    'selling_price' => 40.00,
    'stock_threshold' => 10,
    'shelf_life_days' => 5,
    'storage_temperature' => '0-4°C',
    'is_perishable' => true,
    'hsn_code' => '07020000',
    'is_active' => true
]);

exit
```

### Step 3: Assign Products to Branches
```php
php artisan tinker

// Get products and branches
$apple = App\Models\Product::where('code', 'APPLE-RED')->first();
$tomato = App\Models\Product::where('code', 'TOMATO-FRESH')->first();
$mainBranch = App\Models\Branch::where('code', 'MAIN')->first();
$mallBranch = App\Models\Branch::where('code', 'MALL')->first();

// Assign apple to both branches with different pricing
$apple->branches()->attach($mainBranch->id, [
    'selling_price' => 100.00,
    'current_stock' => 50.0,
    'is_available_online' => true
]);

$apple->branches()->attach($mallBranch->id, [
    'selling_price' => 110.00, // Higher price at mall
    'current_stock' => 30.0,
    'is_available_online' => true
]);

// Assign tomato to both branches
$tomato->branches()->attach($mainBranch->id, [
    'selling_price' => 40.00,
    'current_stock' => 100.0,
    'is_available_online' => true
]);

$tomato->branches()->attach($mallBranch->id, [
    'selling_price' => 45.00,
    'current_stock' => 75.0,
    'is_available_online' => true
]);

exit
```

### Step 4: Create Vendors
```php
php artisan tinker

$vendor1 = App\Models\Vendor::create([
    'name' => 'Fresh Farm Suppliers',
    'contact_person' => 'Farmer John',
    'phone' => '9876543200',
    'email' => 'orders@freshfarm.com',
    'address' => 'Farm House, Village Road',
    'vendor_type' => 'farmer',
    'payment_terms' => 'Net 15',
    'is_active' => true
]);

$vendor2 = App\Models\Vendor::create([
    'name' => 'Wholesale Market Co.',
    'contact_person' => 'Market Manager',
    'phone' => '9876543201',
    'email' => 'supply@wholesalemarket.com',
    'address' => 'Wholesale Market, Sector 10',
    'vendor_type' => 'wholesaler',
    'payment_terms' => 'Net 30',
    'is_active' => true
]);

// Assign vendors to products
$apple->vendors()->attach($vendor1->id, [
    'vendor_price' => 75.00,
    'is_primary' => true
]);

$tomato->vendors()->attach($vendor2->id, [
    'vendor_price' => 28.00,
    'is_primary' => true
]);

exit
```

## ✅ Testing the System

### Step 1: Start Development Server
```bash
# Start Laravel server
php artisan serve

# In another terminal, start queue worker
php artisan queue:work

# In another terminal, start frontend dev server (if using)
npm run dev
```

### Step 2: Access Web Interface
Open your browser and go to: `http://localhost:8000`

**Default Login Credentials:**
- **Admin**: admin@foodcompany.com / admin123
- **Manager**: manager@foodcompany.com / manager123
- **Cashier**: cashier@foodcompany.com / cashier123
- **Delivery Boy**: delivery@foodcompany.com / delivery123

### Step 3: Test API Endpoints
```bash
# Test inventory alerts
curl -H "Authorization: Bearer YOUR_TOKEN" \
     http://localhost:8000/api/inventory/alerts

# Test product categories
curl -H "Authorization: Bearer YOUR_TOKEN" \
     http://localhost:8000/api/products/categories

# Test wholesale pricing
curl -H "Authorization: Bearer YOUR_TOKEN" \
     http://localhost:8000/api/wholesale/pricing-tiers
```

### Step 4: Test Automated Features

#### Test Auto Stock Update
```bash
# Create a test order via API and verify stock reduces automatically
curl -X POST -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{
       "customer_name": "Test Customer",
       "customer_phone": "9999999999",
       "order_type": "on_shop",
       "payment_method": "cash",
       "items": [
         {
           "product_id": 1,
           "quantity": 2,
           "unit_price": 100
         }
       ]
     }' \
     http://localhost:8000/api/orders
```

#### Test Threshold-Based Online Availability
```bash
# Reduce stock below threshold and verify online status changes
curl -X POST -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{
       "product_id": 1,
       "branch_id": 1,
       "quantity": 3.0,
       "reason": "Wastage test"
     }' \
     http://localhost:8000/api/inventory/wastage-loss
```

#### Test Automated Tasks
```bash
# Run automated tasks manually
php artisan system:process-automated-tasks

# Check logs
tail -f storage/logs/laravel.log
```

## 🌐 Production Deployment

### Step 1: Server Setup
```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-gd php8.2-curl php8.2-zip php8.2-mbstring

# Install MySQL
sudo apt install mysql-server

# Install Nginx
sudo apt install nginx

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Step 2: Project Deployment
```bash
# Clone project to server
cd /var/www
sudo git clone <your-repository-url> food-company
cd food-company

# Set permissions
sudo chown -R www-data:www-data /var/www/food-company
sudo chmod -R 755 /var/www/food-company
sudo chmod -R 775 /var/www/food-company/storage
sudo chmod -R 775 /var/www/food-company/bootstrap/cache

# Install dependencies
composer install --optimize-autoloader --no-dev
npm ci && npm run build
```

### Step 3: Production Environment
```bash
# Copy and configure environment
cp .env.example .env
nano .env
```

Production `.env` settings:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Use production database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=food_company_prod
DB_USERNAME=food_user
DB_PASSWORD=secure_production_password

# Configure mail for production
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourdomain.com

# Use Redis for better performance (optional)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Step 4: Production Optimization
```bash
# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Set up queue worker as service
sudo nano /etc/systemd/system/food-company-worker.service
```

Queue worker service file:
```ini
[Unit]
Description=Food Company Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/food-company/artisan queue:work --sleep=3 --tries=3 --max-time=3600
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

```bash
# Enable and start the service
sudo systemctl enable food-company-worker
sudo systemctl start food-company-worker
```

### Step 5: Web Server Configuration

#### Nginx Configuration
```bash
sudo nano /etc/nginx/sites-available/food-company
```

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/food-company/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/food-company /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Step 6: SSL Certificate (Let's Encrypt)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

## 🔐 Security Configuration

### Step 1: Application Security
```bash
# Generate secure app key
php artisan key:generate

# Set proper file permissions
find /var/www/food-company -type f -exec chmod 644 {} \;
find /var/www/food-company -type d -exec chmod 755 {} \;
chmod -R 775 /var/www/food-company/storage
chmod -R 775 /var/www/food-company/bootstrap/cache
chmod 644 /var/www/food-company/.env
```

### Step 2: Database Security
```sql
-- Remove test databases
DROP DATABASE IF EXISTS test;

-- Create dedicated database user
CREATE USER 'food_app'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT SELECT, INSERT, UPDATE, DELETE ON food_company_prod.* TO 'food_app'@'localhost';
FLUSH PRIVILEGES;
```

### Step 3: Firewall Configuration
```bash
# Configure UFW firewall
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw --force enable
```

## 📊 Monitoring and Maintenance

### Step 1: Log Management
```bash
# Set up log rotation
sudo nano /etc/logrotate.d/food-company
```

```
/var/www/food-company/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

### Step 2: Database Backup
```bash
# Create backup script
sudo nano /usr/local/bin/food-company-backup.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/food-company"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="food_company_prod"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u food_user -p$DB_PASSWORD $DB_NAME > $BACKUP_DIR/db_backup_$DATE.sql

# File backup
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz /var/www/food-company

# Keep only last 30 days of backups
find $BACKUP_DIR -type f -mtime +30 -delete

echo "Backup completed: $DATE"
```

```bash
# Make executable and add to cron
sudo chmod +x /usr/local/bin/food-company-backup.sh
sudo crontab -e
# Add: 0 3 * * * /usr/local/bin/food-company-backup.sh
```

## 🔍 Troubleshooting

### Common Issues and Solutions

#### 1. Migration Errors
```bash
# Reset migrations (CAUTION: This will delete all data)
php artisan migrate:reset
php artisan migrate
php artisan db:seed

# Or rollback specific migration
php artisan migrate:rollback --step=1
```

#### 2. Permission Issues
```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage/
sudo chmod -R 775 storage/
```

#### 3. Queue Not Processing
```bash
# Check queue worker status
sudo systemctl status food-company-worker

# Restart queue worker
sudo systemctl restart food-company-worker

# Check queue jobs
php artisan queue:work --once
```

#### 4. Cache Issues
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

#### 5. Database Connection Issues
```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

### Performance Optimization

#### 1. Enable OPcache
```bash
# Edit PHP configuration
sudo nano /etc/php/8.2/fpm/php.ini
```

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

#### 2. Database Optimization
```sql
-- Add indexes for better performance
ALTER TABLE expense_allocations ADD INDEX idx_product_allocation (product_id, allocation_date);
ALTER TABLE loss_tracking ADD INDEX idx_product_loss_type (product_id, loss_type);
ALTER TABLE wholesale_pricing ADD INDEX idx_customer_quantity (customer_type, min_quantity);
```

#### 3. Application Optimization
```bash
# Use Redis for sessions and cache (if available)
composer require predis/predis

# Update .env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

## 📞 Support and Maintenance

### Regular Maintenance Tasks
1. **Daily**: Run automated tasks command
2. **Weekly**: Review loss tracking reports
3. **Monthly**: Analyze expense allocation and profit margins
4. **Quarterly**: Review and update wholesale pricing tiers

### Monitoring Commands
```bash
# Check system health
php artisan system:process-automated-tasks --stock-alerts

# View recent logs
tail -f storage/logs/laravel.log

# Check queue status
php artisan queue:monitor

# Database status
php artisan db:show
```

### Getting Help
1. Check the logs: `storage/logs/laravel.log`
2. Review API documentation: `ENHANCED_MODULES_API.md`
3. Check user guide: `USER_GUIDE.md`
4. Run diagnostic commands above

## 🎉 Setup Complete!

Your enhanced food company management system is now ready with:
- ✅ Multi-branch inventory management
- ✅ Automated stock updates and threshold management
- ✅ Comprehensive loss tracking
- ✅ Advanced expense allocation
- ✅ Delivery boy adjustment features
- ✅ Wholesale billing with tiered pricing
- ✅ Enhanced product and pricing management

**Next Step**: Check the `USER_GUIDE.md` for detailed instructions on how to use all the features!