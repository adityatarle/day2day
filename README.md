# 🍎🥬 Fruit & Vegetable Business Management System

A comprehensive, enterprise-grade business management system designed specifically for fruit and vegetable businesses with multiple branches. Built with Laravel 12 and modern web technologies.

## ✨ Features

### 🔐 **Authentication & Authorization**
- Multi-role user management (Admin, Branch Manager, Cashier, Delivery Boy)
- Role-based access control (RBAC)
- Secure API authentication with Laravel Sanctum
- Session-based web authentication

### 🏢 **Multi-Branch Management**
- Independent inventory per branch
- Branch-specific pricing strategies
- Branch-specific stock thresholds
- Online availability control per branch

### 📦 **Advanced Inventory Management**
- Real-time stock tracking
- Batch-wise inventory management
- Weight loss tracking (fresh produce)
- Water loss tracking (vegetables)
- Wastage tracking and analysis
- Complimentary/adjustment tracking
- Automatic "Sold Out" status
- Low stock alerts

### 🛒 **Comprehensive Sales Management**
- **Online Orders** - Website/app integration
- **On-Shop Orders** - Walk-in customer billing
- **Wholesale Orders** - Bulk purchase management
- **Delivery Tracking** - Real-time delivery status
- **Return Management** - Customer returns and refunds
- **Customer Adjustments** - Weight/price adjustments

### 🚚 **Delivery & Returns**
- Delivery boy assignment and tracking
- Real-time delivery status updates
- Return processing and approval
- Customer adjustment handling
- Mobile app integration ready

### 💰 **Financial Management**
- Expense tracking and categorization
- Payment management (Cash, UPI, Card, Credit)
- Credit management with customers and vendors
- GST integration and tax calculation
- Profit margin analysis
- Financial reporting

### 📊 **Business Intelligence**
- Sales analytics and trends
- Inventory performance metrics
- Customer behavior analysis
- Vendor performance tracking
- Profit & Loss statements
- Growth opportunity insights

### 🔧 **System Features**
- RESTful API with comprehensive endpoints
- Modern web interface with responsive design
- Real-time notifications and alerts
- Data export (CSV, PDF, Excel)
- Backup and recovery systems
- Performance monitoring

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- MySQL/PostgreSQL/SQLite
- Node.js & NPM

### Installation
```bash
# Clone the repository
git clone <repository-url>
cd fruit-vegetable-business-system

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure database in .env file

# Run migrations and seed data
php artisan migrate
php artisan db:seed

# Build assets
npm run build

# Start the application
php artisan serve
```

### Default Login
- **Email**: admin@example.com
- **Password**: password

## 📚 Documentation

- **[System Documentation](SYSTEM_DOCUMENTATION.md)** - Comprehensive system overview
- **[Quick Setup Guide](QUICK_SETUP_GUIDE.md)** - Get up and running quickly
- **[API Documentation](routes/api.php)** - Complete API endpoint reference
- **[Web Routes](routes/web.php)** - Web interface routes

## 🏗️ System Architecture

### Technology Stack
- **Backend**: Laravel 12 (PHP 8.2+)
- **Database**: MySQL/PostgreSQL/SQLite
- **Authentication**: Laravel Sanctum
- **Frontend**: Blade templates + modern CSS/JS
- **API**: RESTful with role-based access control

### Core Components
```
app/
├── Models/          # Eloquent models with relationships
├── Http/
│   ├── Controllers/ # Business logic controllers
│   └── Middleware/  # Authentication & authorization
├── Database/
│   ├── Migrations/  # Database schema
│   └── Seeders/     # Initial data
└── Resources/
    └── Views/       # Web interface templates

routes/
├── api.php          # API endpoints
└── web.php          # Web interface routes
```

## 🔑 API Endpoints

### Authentication
- `POST /api/login` - User authentication
- `POST /api/logout` - User logout
- `GET /api/profile` - User profile

### Products & Inventory
- `GET /api/products` - List products
- `POST /api/products` - Create product
- `GET /api/inventory` - Inventory status
- `POST /api/inventory/add-stock` - Add stock

### Orders & Sales
- `GET /api/orders` - List orders
- `POST /api/orders` - Create order
- `GET /api/orders/statistics` - Sales statistics

### Customers & Vendors
- `GET /api/customers` - Customer management
- `GET /api/vendors` - Vendor management
- `GET /api/purchase-orders` - Purchase orders

### Reports & Analytics
- `GET /api/reports/sales` - Sales reports
- `GET /api/reports/inventory` - Inventory reports
- `GET /api/reports/profit-loss` - Financial reports

## 🎯 Use Cases

### For Business Owners
- **Multi-branch management** with centralized control
- **Real-time business insights** and performance metrics
- **Financial tracking** and profit analysis
- **Growth opportunities** identification

### For Branch Managers
- **Inventory optimization** and stock management
- **Customer relationship** management
- **Vendor performance** tracking
- **Branch-specific** reporting and analytics

### For Cashiers
- **Quick billing** and order processing
- **Customer service** and adjustments
- **Payment processing** (Cash, UPI, Card, Credit)
- **Sales tracking** and reporting

### For Delivery Personnel
- **Order delivery** tracking and management
- **Return processing** and customer adjustments
- **Mobile app** integration for field operations
- **Real-time updates** and notifications

## 🔒 Security Features

- **Role-based access control** (RBAC)
- **API authentication** with tokens
- **Input validation** and sanitization
- **SQL injection** prevention
- **XSS protection**
- **CSRF protection**
- **Rate limiting**

## 📱 Mobile Ready

The system is designed with mobile-first approach:
- **Responsive web interface** for all devices
- **API endpoints** ready for mobile app integration
- **Delivery boy mobile app** integration points
- **Customer mobile app** ready architecture

## 🌟 Key Benefits

1. **Streamlined Operations** - Automate manual processes
2. **Real-time Insights** - Make data-driven decisions
3. **Multi-branch Support** - Scale your business efficiently
4. **Inventory Optimization** - Reduce waste and improve margins
5. **Customer Satisfaction** - Better service and tracking
6. **Financial Control** - Monitor costs and profitability
7. **Compliance Ready** - GST integration and reporting

## 🤝 Contributing

We welcome contributions! Please see our contributing guidelines for details.

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

- **Documentation**: Check the documentation files
- **Issues**: Report bugs and feature requests
- **Community**: Join our community discussions

## 🔮 Roadmap

- [ ] Advanced analytics dashboard
- [ ] Machine learning insights
- [ ] Predictive analytics
- [ ] Customer loyalty program
- [ ] Advanced reporting
- [ ] Mobile applications
- [ ] API marketplace
- [ ] Multi-currency support
- [ ] Advanced inventory forecasting
- [ ] Supplier portal

---

**Built with ❤️ for the fruit and vegetable business community**

*Transform your fruit and vegetable business with enterprise-grade management tools designed specifically for your industry needs.*
