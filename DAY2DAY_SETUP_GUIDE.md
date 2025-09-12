# 🏢 Day2Day Multi-Branch Management System

## Overview

Day2Day is a comprehensive multi-branch inventory and POS management system designed specifically for companies that operate across multiple cities with centralized supply management. The system provides separate interfaces for main branch administrators and individual branch operations.

## 🎯 Key Features

### Main Branch Admin Features
- **Material Supply Management**: Supply materials to all branches with tracking
- **Branch Performance Monitoring**: Real-time dashboard with branch analytics
- **City-Specific Pricing**: Manage different pricing for each city
- **Stock Transfer Tracking**: Monitor all material transfers between branches
- **Comprehensive Reporting**: Branch performance, financial overview, and analytics

### Branch Features
- **Purchase Entry System**: Record materials received from main branch
- **Damage/Wastage Tracking**: Record and track damaged or wasted inventory
- **Branch-Specific POS**: Point of sale system for individual branch sales
- **Inventory Management**: Branch-specific stock tracking and management
- **Sales & Purchase Reports**: Detailed reporting for branch operations

## 🚀 Getting Started

### System Requirements
- PHP 8.4+
- Composer
- SQLite/MySQL/PostgreSQL
- Node.js & NPM (for frontend assets)

### Installation

1. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup**
   ```bash
   # For SQLite (default)
   touch database/database.sqlite
   
   # Run migrations and seeders
   php artisan migrate:fresh --seed
   ```

4. **Start the Application**
   ```bash
   php artisan serve
   ```

## 🔐 Login Credentials

### Main Branch Admin
- **Email**: `admin@day2day.com`
- **Password**: `admin123`
- **Role**: Complete system access, material supply management

### Branch Managers
| City | Email | Password |
|------|-------|----------|
| Mumbai | `manager.mumbai@day2day.com` | `manager123` |
| Delhi | `manager.delhi@day2day.com` | `manager123` |
| Bangalore | `manager.bangalore@day2day.com` | `manager123` |
| Pune | `manager.pune@day2day.com` | `manager123` |
| Chennai | `manager.chennai@day2day.com` | `manager123` |
| Hyderabad | `manager.hyderabad@day2day.com` | `manager123` |
| Kolkata | `manager.kolkata@day2day.com` | `manager123` |
| Ahmedabad | `manager.ahmedabad@day2day.com` | `manager123` |

### Sample Cashiers
| Branch | Email | Password |
|--------|-------|----------|
| Mumbai | `cashier.mumbai@day2day.com` | `cashier123` |
| Delhi | `cashier.delhi@day2day.com` | `cashier123` |

## 🏗️ System Architecture

### Branch Structure
- **Main Branch (D2D-MAIN)**: Head office for material supply and management
- **City Branches**: Individual retail branches in different cities

### User Roles
1. **Admin**: Main branch administrator with complete system access
2. **Branch Manager**: Manages individual branch operations
3. **Cashier**: Handles POS operations and sales

### Database Structure
- **Multi-tenant architecture** with branch-specific data isolation
- **City-specific pricing** for products across different markets
- **Comprehensive tracking** for inventory, sales, and transfers

## 📊 Dashboard Features

### Admin Dashboard (`/day2day/admin/dashboard`)
- Branch performance overview
- Material supply management
- Stock transfer monitoring
- Financial analytics
- Low stock alerts across all branches

### Branch Dashboard (`/day2day/branch/dashboard`)
- Branch-specific inventory overview
- Purchase entry management
- POS session tracking
- Sales and purchase reports
- Damage/wastage recording

## 🔄 Key Workflows

### 1. Material Supply Process
1. **Admin** logs in to main branch dashboard
2. Selects target branch and products to supply
3. Creates stock transfer with expected delivery date
4. **Branch** receives notification of incoming materials
5. **Branch** confirms receipt and creates purchase entry
6. System updates inventory automatically

### 2. Purchase Entry Process
1. **Branch Manager/Cashier** receives materials
2. Creates purchase entry with:
   - Vendor information
   - Quantities ordered vs received
   - Unit prices
   - Damage information (if any)
3. System automatically updates branch inventory
4. Records any damages in loss tracking

### 3. Damage Recording
1. **Branch Staff** identifies damaged/wasted inventory
2. Records damage entry with:
   - Product and quantity
   - Loss type (damage, wastage, expiry, theft)
   - Reason for loss
   - Unit cost for financial impact
3. System reduces inventory and tracks financial impact

### 4. POS Operations
1. **Cashier** starts POS session
2. Processes sales transactions
3. System updates inventory in real-time
4. Generates sales reports and analytics

## 📈 Reporting & Analytics

### Available Reports
- **Sales Reports**: Daily, monthly, and custom date ranges
- **Purchase Reports**: Track all incoming materials and costs
- **Inventory Reports**: Stock levels, valuations, and movements
- **Loss Reports**: Damage, wastage, and theft tracking
- **Financial Reports**: Revenue, costs, and profit analysis

### Real-time Analytics
- Branch performance comparisons
- Top-selling products by branch
- Low stock alerts
- Overdue stock transfers
- Financial impact of losses

## 🔧 API Endpoints

### Admin Endpoints
- `GET /day2day/admin/dashboard` - Admin dashboard
- `POST /day2day/admin/supply-materials` - Create stock transfer
- `GET /day2day/admin/branches` - Get all branches
- `GET /day2day/admin/products` - Get all products
- `POST /day2day/admin/update-city-pricing` - Update city-specific pricing

### Branch Endpoints
- `GET /day2day/branch/dashboard` - Branch dashboard
- `POST /day2day/branch/purchase-entry` - Create purchase entry
- `POST /day2day/branch/record-damage` - Record damage/wastage
- `GET /day2day/branch/sales-report` - Get sales report
- `GET /day2day/branch/purchase-report` - Get purchase report

## 🎨 User Interface

### Modern Design Features
- **Responsive Design**: Works on desktop, tablet, and mobile
- **Role-based Navigation**: Different interfaces for different roles
- **Real-time Updates**: Live data updates without page refresh
- **Interactive Dashboards**: Charts, graphs, and analytics
- **Modal-based Forms**: Streamlined data entry workflows

### Technology Stack
- **Backend**: Laravel 12 (PHP 8.4)
- **Frontend**: Blade templates with Bootstrap 5
- **Database**: SQLite (development) / MySQL (production)
- **Authentication**: Laravel Sanctum with role-based access
- **Real-time Features**: AJAX-powered interfaces

## 🔒 Security Features

- **Role-based Access Control**: Strict permission management
- **Branch Data Isolation**: Users can only access their branch data
- **Secure Authentication**: Password hashing and session management
- **Input Validation**: Server-side validation for all forms
- **CSRF Protection**: Protection against cross-site request forgery

## 🚀 Deployment

### Production Setup
1. **Server Requirements**
   - PHP 8.4+ with required extensions
   - Web server (Apache/Nginx)
   - Database server (MySQL/PostgreSQL)

2. **Environment Configuration**
   ```bash
   # Update .env for production
   APP_ENV=production
   APP_DEBUG=false
   DB_CONNECTION=mysql
   DB_HOST=your-database-host
   DB_DATABASE=day2day_production
   ```

3. **Optimization**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   composer install --optimize-autoloader --no-dev
   ```

## 📞 Support & Maintenance

### Regular Maintenance Tasks
- **Database Backups**: Daily automated backups
- **Log Monitoring**: Check application and error logs
- **Performance Monitoring**: Monitor response times and usage
- **Security Updates**: Keep framework and dependencies updated

### Troubleshooting
- Check logs in `storage/logs/laravel.log`
- Verify database connections and permissions
- Ensure proper file permissions for storage directories
- Monitor server resources (CPU, memory, disk space)

## 📋 Feature Roadmap

### Planned Enhancements
- [ ] Mobile app for branch managers
- [ ] Advanced analytics and forecasting
- [ ] Automated reorder points
- [ ] Integration with accounting systems
- [ ] Barcode scanning support
- [ ] Multi-language support
- [ ] Advanced reporting with exports

---

## 🎉 Success!

Your Day2Day multi-branch management system is now ready! 

**Next Steps:**
1. Log in with the provided credentials
2. Explore the admin dashboard
3. Test the material supply workflow
4. Set up additional users as needed
5. Customize city-specific pricing
6. Start managing your multi-branch operations!

For support or questions, refer to the comprehensive documentation and API guides included in the system.