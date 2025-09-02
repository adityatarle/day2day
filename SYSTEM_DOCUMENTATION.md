# Fruit & Vegetable Business Management System

## System Overview

This is a comprehensive business management system designed specifically for fruit and vegetable businesses with multiple branches. The system handles all aspects of the business including user management, inventory, sales, purchases, accounting, and reporting.

## System Architecture

### Technology Stack
- **Backend**: Laravel 12 (PHP 8.2+)
- **Database**: MySQL/PostgreSQL/SQLite
- **Authentication**: Laravel Sanctum (API tokens)
- **Frontend**: Blade templates with modern CSS/JavaScript
- **API**: RESTful API with role-based access control

### Core Components
1. **Authentication & Authorization System**
2. **User & Role Management**
3. **Branch Management**
4. **Product & Inventory Management**
5. **Sales & Order Management**
6. **Purchase & Vendor Management**
7. **Customer Management**
8. **Financial Management**
9. **Reporting & Analytics**

## User Roles & Permissions

### 1. Admin (Owner/Manager)
- **Full system access**
- User management
- Branch management
- System configuration
- All reports and analytics

### 2. Branch Manager
- **Branch-specific operations**
- Product management
- Inventory management
- Customer management
- Vendor management
- Branch reports

### 3. Cashier (On-Shop Sales)
- **Sales operations**
- Create/edit orders
- Process payments
- Customer billing
- Quick sales
- Wholesale billing

### 4. Delivery Boy
- **Delivery operations**
- Order pickup/delivery
- Return management
- Customer adjustments
- Mobile app integration

## Database Schema

### Core Tables

#### Users & Authentication
- `users` - User accounts with role and branch assignments
- `roles` - User roles (admin, branch_manager, cashier, delivery_boy)
- `permissions` - System permissions
- `role_permissions` - Role-permission relationships
- `branches` - Business branches/locations

#### Products & Inventory
- `products` - Product master data
- `product_branches` - Branch-specific product pricing and stock
- `vendors` - Product suppliers
- `product_vendors` - Product-vendor relationships
- `batches` - Product batches for tracking
- `stock_movements` - All stock changes
- `loss_tracking` - Loss tracking (weight, water, wastage)

#### Sales & Orders
- `customers` - Customer information
- `orders` - Sales orders (online, on-shop, wholesale)
- `order_items` - Individual items in orders
- `deliveries` - Delivery tracking
- `returns` - Customer returns
- `return_items` - Returned items

#### Purchases & Vendors
- `purchase_orders` - Vendor purchase orders
- `purchase_order_items` - Items in purchase orders

#### Financial
- `expense_categories` - Expense categories
- `expenses` - Business expenses
- `payments` - All payment transactions
- `credit_transactions` - Credit with customers/vendors
- `gst_rates` - GST tax rates
- `product_gst_rates` - Product-GST relationships

## API Endpoints

### Authentication
```
POST /api/login - User login
POST /api/logout - User logout
GET /api/profile - Get user profile
POST /api/change-password - Change password
```

### User Management (Admin Only)
```
GET /api/users - List users
POST /api/users - Create user
GET /api/users/{id} - Get user details
PUT /api/users/{id} - Update user
DELETE /api/users/{id} - Delete user
GET /api/roles - Get roles
GET /api/branches - Get branches
```

### Product Management
```
GET /api/products - List products
POST /api/products - Create product
GET /api/products/{id} - Get product details
PUT /api/products/{id} - Update product
DELETE /api/products/{id} - Delete product
POST /api/products/{id}/branch-pricing - Update branch pricing
GET /api/products/{id}/stock-info - Get stock information
GET /api/products/category/{category} - Get products by category
GET /api/products/search - Search products
```

### Inventory Management
```
GET /api/inventory - Get inventory status
POST /api/inventory/add-stock - Add stock
POST /api/inventory/record-loss - Record loss
GET /api/inventory/{product}/batches - Get product batches
PUT /api/inventory/batches/{id}/status - Update batch status
GET /api/inventory/{product}/stock-movements - Get stock movements
GET /api/inventory/loss-summary - Get loss summary
GET /api/inventory/low-stock-alerts - Get low stock alerts
GET /api/inventory/valuation - Get inventory valuation
```

### Order Management
```
GET /api/orders - List orders
POST /api/orders - Create order
GET /api/orders/{id} - Get order details
PUT /api/orders/{id} - Update order
POST /api/orders/{id}/cancel - Cancel order
GET /api/orders/{id}/invoice - Generate invoice
GET /api/orders/statistics - Get order statistics
```

### Customer Management
```
GET /api/customers - List customers
POST /api/customers - Create customer
GET /api/customers/{id} - Get customer details
PUT /api/customers/{id} - Update customer
GET /api/customers/{id}/purchase-history - Get purchase history
GET /api/customers/{id}/credit-balance - Get credit balance
```

### Vendor Management
```
GET /api/vendors - List vendors
POST /api/vendors - Create vendor
GET /api/vendors/{id} - Get vendor details
PUT /api/vendors/{id} - Update vendor
```

### Purchase Orders
```
GET /api/purchase-orders - List purchase orders
POST /api/purchase-orders - Create purchase order
GET /api/purchase-orders/{id} - Get purchase order details
PUT /api/purchase-orders/{id} - Update purchase order
POST /api/purchase-orders/{id}/receive - Mark as received
```

### Reports
```
GET /api/reports/sales - Sales report
GET /api/reports/inventory - Inventory report
GET /api/reports/customers - Customer report
GET /api/reports/vendors - Vendor report
GET /api/reports/expenses - Expense report
GET /api/reports/profit-loss - Profit & Loss report
```

## Web Routes

### Dashboard
- `/dashboard` - Main dashboard

### Product Management
- `/products` - Product listing
- `/products/create` - Create product
- `/products/{id}` - View product
- `/products/{id}/edit` - Edit product
- `/products/category/{category}` - Products by category

### Inventory Management
- `/inventory` - Inventory overview
- `/inventory/add-stock` - Add stock form
- `/inventory/record-loss` - Record loss form
- `/inventory/batches` - Batch management
- `/inventory/stock-movements` - Stock movements
- `/inventory/loss-tracking` - Loss tracking
- `/inventory/valuation` - Inventory valuation
- `/inventory/low-stock-alerts` - Low stock alerts

### Order Management
- `/orders` - Order listing
- `/orders/create` - Create order
- `/orders/{id}` - View order
- `/orders/{id}/edit` - Edit order
- `/orders/{id}/invoice` - View invoice
- `/billing/quick-sale` - Quick sale form
- `/billing/wholesale` - Wholesale form

### Customer Management
- `/customers` - Customer listing
- `/customers/create` - Create customer
- `/customers/{id}` - View customer
- `/customers/{id}/edit` - Edit customer
- `/customers/{id}/purchase-history` - Purchase history

### Vendor Management
- `/vendors` - Vendor listing
- `/vendors/create` - Create vendor
- `/vendors/{id}` - View vendor
- `/vendors/{id}/edit` - Edit vendor
- `/purchase-orders` - Purchase orders
- `/purchase-orders/create` - Create purchase order
- `/purchase-orders/{id}` - View purchase order

### Reports
- `/reports` - Reports overview
- `/reports/sales` - Sales reports
- `/reports/inventory` - Inventory reports
- `/reports/customers` - Customer reports
- `/reports/vendors` - Vendor reports
- `/reports/expenses` - Expense reports
- `/reports/profit-loss` - Profit & Loss
- `/reports/analytics` - Business analytics

## Key Features

### 1. Multi-Branch Support
- Each branch can have independent inventory
- Branch-specific pricing
- Branch-specific stock thresholds
- Online availability per branch

### 2. Advanced Inventory Management
- Batch-wise tracking
- Weight loss tracking
- Water loss tracking
- Wastage tracking
- Complimentary/adjustment tracking
- Auto stock updates
- Low stock alerts

### 3. Flexible Pricing
- Base product pricing
- Branch-specific pricing
- Vendor supply pricing
- Wholesale pricing
- Dynamic pricing updates

### 4. Comprehensive Order Management
- Online orders
- On-shop orders
- Wholesale orders
- Delivery tracking
- Return management
- Customer adjustments

### 5. Financial Management
- Expense tracking
- Payment management
- Credit management
- GST integration
- Profit margin calculation

### 6. Business Intelligence
- Sales analytics
- Inventory analytics
- Customer analytics
- Vendor analytics
- Profit & Loss reports
- Growth suggestions

## Installation & Setup

### Prerequisites
- PHP 8.2+
- Composer
- MySQL/PostgreSQL/SQLite
- Node.js & NPM (for frontend assets)

### Installation Steps
1. Clone the repository
2. Install dependencies: `composer install`
3. Copy `.env.example` to `.env`
4. Configure database in `.env`
5. Generate application key: `php artisan key:generate`
6. Run migrations: `php artisan migrate`
7. Seed initial data: `php artisan db:seed`
8. Install frontend dependencies: `npm install`
9. Build assets: `npm run build`

### Initial Setup
1. Create admin user
2. Configure branches
3. Set up product categories
4. Configure GST rates
5. Set up expense categories
6. Configure vendor information

## Security Features

### Authentication
- Laravel Sanctum for API authentication
- Session-based web authentication
- Password hashing
- Remember me functionality

### Authorization
- Role-based access control (RBAC)
- Permission-based access control
- Route-level protection
- API endpoint protection

### Data Protection
- Input validation
- SQL injection prevention
- XSS protection
- CSRF protection
- Rate limiting

## Business Logic

### Stock Management
- Real-time stock updates
- Batch expiration tracking
- Automatic "Sold Out" status
- Stock movement history
- Loss tracking and analysis

### Pricing Strategy
- Cost-plus pricing
- Market-based pricing
- Branch-specific pricing
- Dynamic pricing updates
- Profit margin optimization

### Customer Management
- Customer segmentation
- Purchase history tracking
- Credit management
- Customer analytics
- Loyalty tracking

### Vendor Management
- Supplier performance tracking
- Supply cost analysis
- Payment terms management
- Credit balance tracking

## Reporting & Analytics

### Sales Reports
- Daily/weekly/monthly sales
- Product-wise sales
- Branch-wise sales
- Customer-wise sales
- Payment method analysis

### Inventory Reports
- Stock levels
- Stock movements
- Loss analysis
- Expiry tracking
- Valuation reports

### Financial Reports
- Profit & Loss statements
- Expense analysis
- Revenue analysis
- Cost analysis
- Cash flow statements

### Business Intelligence
- Top-selling products
- Slow-moving products
- Seasonal trends
- Customer behavior
- Growth opportunities

## Mobile App Integration

### Delivery Boy App
- Order assignments
- Delivery tracking
- Return management
- Customer adjustments
- Real-time updates

### Customer App (Future)
- Product browsing
- Order placement
- Payment processing
- Order tracking
- Return requests

## API Integration

### Third-Party Services
- Payment gateways (UPI, Card, etc.)
- SMS services
- Email services
- Push notifications
- Analytics services

### Data Export
- CSV export
- PDF reports
- Excel reports
- JSON API responses
- Webhook notifications

## Performance Optimization

### Database Optimization
- Indexed queries
- Eager loading
- Query optimization
- Database caching
- Connection pooling

### Application Optimization
- Route caching
- View caching
- Configuration caching
- Asset optimization
- CDN integration

## Monitoring & Maintenance

### System Monitoring
- Error logging
- Performance monitoring
- Database monitoring
- Server monitoring
- User activity tracking

### Backup & Recovery
- Database backups
- File backups
- Configuration backups
- Disaster recovery
- Data restoration

## Future Enhancements

### Planned Features
- Advanced analytics dashboard
- Machine learning insights
- Predictive analytics
- Customer loyalty program
- Advanced reporting
- Mobile applications
- API marketplace
- Multi-currency support
- Advanced inventory forecasting
- Supplier portal

## Support & Documentation

### Technical Support
- API documentation
- User guides
- Admin guides
- Troubleshooting guides
- FAQ section

### Training & Onboarding
- User training materials
- Video tutorials
- Best practices guide
- Implementation guide
- Customization guide

## Conclusion

This Fruit & Vegetable Business Management System provides a comprehensive solution for managing all aspects of a multi-branch fruit and vegetable business. With its robust architecture, role-based access control, and extensive feature set, it enables businesses to streamline operations, improve efficiency, and make data-driven decisions.

The system is designed to be scalable, secure, and user-friendly, making it suitable for businesses of all sizes. Whether you're running a single store or managing multiple branches, this system provides the tools and insights needed to grow and succeed in the competitive fruit and vegetable market.