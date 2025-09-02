# Food Company Web Interface

This document describes the web interface that has been added to the food company management system.

## Overview

The web interface provides a complete user interface for managing all aspects of the food company operations, including products, orders, inventory, customers, vendors, and reports.

## Features

### 🏠 Dashboard
- Overview of key metrics (total sales, orders, customers, products)
- Recent orders display
- Low stock alerts
- Top selling products

### 📦 Product Management
- **Products Index**: View all products with search and filtering by category and branch
- **Product Creation**: Add new products with detailed information
- **Product Details**: View comprehensive product information
- **Product Editing**: Modify existing product details
- **Category Management**: Organize products by fruit, vegetable, leafy, and exotic categories

### 🛒 Order Management
- **Orders Index**: View all orders with search and filtering by status and branch
- **Order Creation**: Create new orders with customer and product selection
- **Order Details**: View complete order information
- **Order Editing**: Modify existing orders
- **Invoice Generation**: Generate order invoices

### 📊 Inventory Management
- **Inventory Overview**: Current stock levels across all branches
- **Stock Addition**: Add new stock with batch tracking
- **Loss Recording**: Record inventory losses and damages
- **Batch Management**: Track inventory batches
- **Stock Movements**: Monitor all stock changes
- **Low Stock Alerts**: Identify products below threshold
- **Inventory Valuation**: Calculate total inventory value

### 👥 Customer Management
- **Customer Directory**: View all customers with search functionality
- **Customer Creation**: Add new customers
- **Customer Profiles**: Detailed customer information and history
- **Purchase History**: Track customer order history
- **Customer Editing**: Update customer information

### 🏪 Vendor Management
- **Vendor Directory**: View all vendors with search functionality
- **Vendor Creation**: Add new vendors
- **Vendor Profiles**: Detailed vendor information
- **Purchase Orders**: Manage vendor purchase orders
- **Vendor Performance**: Track vendor metrics

### 📈 Reports & Analytics
- **Sales Reports**: Detailed sales analysis with date filtering
- **Inventory Reports**: Stock levels and valuation reports
- **Customer Reports**: Customer analysis and top customers
- **Vendor Reports**: Vendor performance metrics
- **Financial Reports**: Profit & loss analysis
- **Analytics Dashboard**: Top products and category performance

### 💰 Billing System
- **Quick Sale**: Fast point-of-sale interface for cashiers
- **Wholesale Orders**: Bulk order processing with discount calculations
- **Payment Methods**: Support for cash, card, and UPI payments

## Technical Architecture

### Controllers
All web controllers are located in `app/Http/Controllers/Web/`:

- `DashboardController.php` - Dashboard and overview
- `ProductController.php` - Product management
- `OrderController.php` - Order management
- `InventoryController.php` - Inventory management
- `CustomerController.php` - Customer management
- `VendorController.php` - Vendor management
- `ReportController.php` - Reports and analytics
- `WebAuthController.php` - Web authentication

### Views
Views are organized by feature in `resources/views/`:

- `layouts/app.blade.php` - Main application layout
- `products/` - Product-related views
- `orders/` - Order-related views
- `inventory/` - Inventory-related views
- `customers/` - Customer-related views
- `vendors/` - Vendor-related views
- `reports/` - Report views
- `billing/` - Billing and sales views

### Routes
Web routes are defined in `routes/web.php` with proper middleware protection:

- Authentication required for all routes
- Role-based access control for different features
- RESTful routing for CRUD operations

### Styling
- **Tailwind CSS**: Modern utility-first CSS framework
- **Responsive Design**: Mobile-friendly interface
- **Custom Components**: Tailored UI components for food industry
- **Interactive Elements**: JavaScript-enhanced user experience

## User Roles & Permissions

### Admin
- Full access to all features
- User management
- System configuration

### Branch Manager
- Product management
- Inventory management
- Order management
- Customer management
- Vendor management
- Reports access

### Cashier
- Order creation and management
- Quick sales
- Customer service
- Basic inventory viewing

## Getting Started

### Prerequisites
- Laravel 10+ installed
- Database configured and migrated
- Authentication system set up
- Tailwind CSS configured

### Installation
1. Ensure all controllers are in place
2. Verify view files are in correct directories
3. Check routes are properly configured
4. Run `npm install` and `npm run dev` for assets

### Access
1. Navigate to the application URL
2. Login with valid credentials
3. Access dashboard and navigate through sidebar

## Key Features

### 🔍 Advanced Search
- Real-time search across all entities
- Filter by multiple criteria
- Category and branch filtering

### 📱 Responsive Design
- Mobile-first approach
- Tablet and desktop optimized
- Touch-friendly interface

### 🎨 Modern UI/UX
- Clean, professional design
- Intuitive navigation
- Consistent styling throughout

### ⚡ Performance
- Optimized database queries
- Efficient pagination
- Lazy loading for large datasets

## Customization

### Adding New Features
1. Create controller in `app/Http/Controllers/Web/`
2. Add routes to `routes/web.php`
3. Create views in appropriate directory
4. Update sidebar navigation

### Styling Changes
- Modify `resources/css/app.css`
- Update Tailwind classes in views
- Customize component styles

### JavaScript Functionality
- Extend `resources/js/app.js`
- Add new utility functions
- Implement interactive features

## Security Features

- CSRF protection on all forms
- Authentication middleware
- Role-based access control
- Input validation and sanitization
- SQL injection prevention

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Troubleshooting

### Common Issues
1. **Views not loading**: Check file paths and Blade syntax
2. **Routes not working**: Verify route definitions and middleware
3. **Styling issues**: Ensure Tailwind CSS is compiled
4. **JavaScript errors**: Check browser console for errors

### Debug Mode
Enable debug mode in `.env` for detailed error messages:
```
APP_DEBUG=true
```

## Support

For technical support or feature requests, please refer to the main project documentation or contact the development team.

---

**Note**: This web interface is designed to work alongside the existing API controllers, providing both programmatic and user interface access to the system.