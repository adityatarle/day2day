# 🍎🥬 Fruit & Vegetable Business Management System - Admin Setup Guide

## 📋 Table of Contents
- [Prerequisites](#prerequisites)
- [Installation Steps](#installation-steps)
- [Admin Module Features](#admin-module-features)
- [Default Login Credentials](#default-login-credentials)
- [Admin Dashboard Overview](#admin-dashboard-overview)
- [User Management](#user-management)
- [Branch Management](#branch-management)
- [Troubleshooting](#troubleshooting)
- [Next Steps](#next-steps)

## 🔧 Prerequisites

Before setting up the system, ensure you have the following installed:

### Required Software
- **PHP 8.2+** (We've installed PHP 8.4)
- **Composer** (PHP package manager)
- **Node.js 16+** and **NPM**
- **MySQL/PostgreSQL/SQLite** (SQLite configured by default)
- **Git** (for version control)

### System Requirements
- **Memory**: Minimum 2GB RAM
- **Storage**: At least 1GB free space
- **OS**: Linux, macOS, or Windows
- **Web Server**: Apache/Nginx (optional for production)

## 🚀 Installation Steps

### Step 1: Environment Setup
```bash
# Navigate to project directory
cd /workspace

# Copy environment configuration
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 2: Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### Step 3: Database Setup
```bash
# Create SQLite database (already done)
touch database/database.sqlite

# Run migrations to create tables
php artisan migrate

# Seed initial data (roles, admin user, sample data)
php artisan db:seed
```

### Step 4: Build Frontend Assets
```bash
# Build for production
npm run build

# OR for development (with hot reload)
npm run dev
```

### Step 5: Start the Application
```bash
# Start Laravel development server
php artisan serve --host=0.0.0.0 --port=8000

# Access the application at: http://localhost:8000
```

## 🔑 Default Login Credentials

After seeding, use these credentials to access the admin panel:

- **Email**: `admin@example.com`
- **Password**: `password`

⚠️ **Important**: Change the default password immediately after first login!

## 🎛️ Admin Module Features

### 📊 Admin Dashboard
- **Real-time Metrics**: Revenue, orders, customers, products
- **Inventory Alerts**: Low stock, out of stock, expiring items
- **Recent Activities**: Latest orders, purchases, user activities
- **Quick Actions**: Direct access to common tasks
- **Branch Performance**: Multi-branch analytics
- **Top Products**: Best-selling items tracking

### 👥 User Management
- **Complete CRUD**: Create, read, update, delete users
- **Role Assignment**: Admin, Branch Manager, Cashier, Delivery Boy
- **Branch Assignment**: Assign users to specific branches
- **Permission Control**: Role-based access control
- **User Analytics**: User distribution and activity tracking

### 🏢 Branch Management
- **Multi-branch Support**: Manage multiple business locations
- **Branch Analytics**: Performance metrics per branch
- **Staff Assignment**: Assign users to branches
- **Revenue Tracking**: Branch-wise revenue analysis
- **Status Management**: Active/inactive branch control

### 🔐 Role-Based Access Control
- **Admin**: Full system access, user management, all modules
- **Branch Manager**: Branch-specific operations, inventory, orders
- **Cashier**: Order processing, billing, customer service
- **Delivery Boy**: Delivery management, returns, adjustments

## 🎯 Admin Dashboard Overview

### Key Metrics Cards
1. **Total Revenue**: Complete business revenue with monthly breakdown
2. **Total Orders**: All-time order count and tracking
3. **Total Products**: Active product inventory
4. **Total Customers**: Registered customer base
5. **Branch Count**: Number of business locations
6. **User Count**: Total system users
7. **Pending Purchase Orders**: Orders awaiting processing
8. **Total Expenses**: Business expense tracking

### Quick Actions Panel
- **Create New Order**: Direct order creation
- **Add New Product**: Product catalog management
- **Add New Vendor**: Vendor relationship management
- **Create Purchase Order**: Procurement management
- **Add Stock**: Inventory replenishment

### Recent Activities Feed
- **Order Activities**: New orders, completions, cancellations
- **Purchase Activities**: Purchase orders, vendor interactions
- **User Activities**: New registrations, role changes
- **System Activities**: Important system events

## 👥 User Management

### Creating New Users
1. Navigate to **Admin → User Management → Add New User**
2. Fill in personal information (name, email, phone, address)
3. Select appropriate role and branch assignment
4. Set secure password
5. Submit to create user account

### User Roles Explained

#### 🔴 Admin (Owner/Manager)
- **Access Level**: Complete system control
- **Permissions**: 
  - User and role management
  - Branch management and creation
  - System configuration
  - All financial reports
  - Business intelligence access
  - Complete module access

#### 🔵 Branch Manager
- **Access Level**: Branch-specific management
- **Permissions**:
  - Inventory management for assigned branch
  - Order processing and fulfillment
  - Vendor relationship management
  - Branch-specific reporting
  - Staff supervision
  - Customer management

#### 🟢 Cashier (On-Shop Sales)
- **Access Level**: Customer service and billing
- **Permissions**:
  - Order creation and billing
  - Payment processing (Cash, UPI, Card, Credit)
  - Customer service and support
  - Basic sales reporting
  - Limited inventory viewing

#### 🟡 Delivery Boy (Online Delivery)
- **Access Level**: Delivery and returns
- **Permissions**:
  - Delivery management and tracking
  - Return processing and approvals
  - Customer adjustment handling
  - Mobile app integration
  - Delivery status updates

## 🏢 Branch Management

### Creating New Branches
1. Navigate to **Admin → Branch Management → Add New Branch**
2. Enter branch details:
   - **Name**: Unique branch identifier
   - **Address**: Complete physical address
   - **Contact**: Phone and email
   - **Manager**: Branch manager name
   - **Status**: Active/Inactive
3. Submit to create branch

### Branch Features
- **Independent Inventory**: Each branch maintains separate stock
- **Custom Pricing**: Branch-specific pricing strategies
- **Staff Assignment**: Dedicated staff per branch
- **Performance Analytics**: Individual branch metrics
- **Revenue Tracking**: Branch-wise financial performance

## 🔧 Troubleshooting

### Common Issues and Solutions

#### Database Connection Issues
```bash
# Check database file exists
ls -la database/database.sqlite

# If missing, create it
touch database/database.sqlite

# Re-run migrations
php artisan migrate:fresh --seed
```

#### Permission Errors
```bash
# Set proper permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### Assets Not Loading
```bash
# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Rebuild assets
npm run build
```

#### Role Middleware Issues
```bash
# Clear route cache
php artisan route:clear

# Check user roles in database
php artisan tinker
>>> App\Models\User::with('role')->get()
```

### Performance Optimization
```bash
# Enable OPcache (production)
# Add to php.ini:
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000

# Cache configuration (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📈 Next Steps

### 1. Complete Admin Setup
- [ ] Change default admin password
- [ ] Create additional admin users if needed
- [ ] Set up proper email configuration
- [ ] Configure backup strategies

### 2. Branch Configuration
- [ ] Create your business branches
- [ ] Assign branch managers
- [ ] Configure branch-specific settings
- [ ] Set up branch contact information

### 3. User Setup
- [ ] Create branch manager accounts
- [ ] Add cashier accounts for each branch
- [ ] Set up delivery boy accounts
- [ ] Configure role-based permissions

### 4. System Configuration
- [ ] Configure email settings for notifications
- [ ] Set up SMS integration (optional)
- [ ] Configure payment gateways
- [ ] Set up backup procedures

### 5. Data Migration (if applicable)
- [ ] Import existing customer data
- [ ] Import product catalog
- [ ] Import vendor information
- [ ] Import historical sales data

## 🔒 Security Recommendations

### Immediate Actions
1. **Change Default Passwords**: Update all default credentials
2. **Enable HTTPS**: Configure SSL certificates for production
3. **Database Security**: Use strong database passwords
4. **File Permissions**: Set appropriate file system permissions
5. **Regular Updates**: Keep dependencies updated

### Production Deployment
1. **Environment**: Set `APP_ENV=production` in `.env`
2. **Debug Mode**: Set `APP_DEBUG=false` in `.env`
3. **Database**: Use MySQL/PostgreSQL for production
4. **Web Server**: Configure Apache/Nginx with proper security headers
5. **Firewall**: Configure appropriate firewall rules
6. **Monitoring**: Set up error logging and monitoring

## 📱 Mobile Integration

The system is designed to support mobile applications:

- **API Endpoints**: RESTful API ready for mobile apps
- **Authentication**: Token-based authentication for mobile
- **Delivery App**: Ready for delivery boy mobile application
- **Customer App**: Architecture ready for customer mobile app

## 📞 Support and Documentation

### Additional Resources
- **System Documentation**: `SYSTEM_DOCUMENTATION.md`
- **API Documentation**: Check `routes/api.php`
- **Web Routes**: Review `routes/web.php`
- **Database Schema**: Explore `database/migrations/`

### Getting Help
- **Laravel Documentation**: https://laravel.com/docs
- **Tailwind CSS**: https://tailwindcss.com/docs
- **Chart.js**: https://www.chartjs.org/docs

## 🎉 Congratulations!

Your Fruit & Vegetable Business Management System is now set up with a comprehensive admin module! 

### What You've Accomplished:
✅ **Modern Admin Dashboard** with real-time metrics and analytics  
✅ **Complete User Management** with role-based access control  
✅ **Multi-branch Management** with performance tracking  
✅ **Responsive UI** with modern design and user experience  
✅ **Role-based Security** with proper permission controls  
✅ **Real-time Alerts** for inventory and business operations  

### Ready to Use:
- Login with admin credentials
- Create your business branches
- Add users with appropriate roles
- Start managing your fruit and vegetable business efficiently!

---

**🚀 Your business management system is ready to transform your operations!**