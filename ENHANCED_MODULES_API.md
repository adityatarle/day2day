# Enhanced Food Company Management System - API Documentation

## Overview

This document covers the enhanced modules added to the food company management system, including comprehensive inventory management, loss tracking, expense allocation, delivery adjustments, wholesale billing, and advanced product management.

## 🔧 Enhanced Modules

### 1. Inventory & Stock Management

#### Auto-Update Stock Features
- ✅ **Auto stock reduction** after every sale (online + shop)
- ✅ **Threshold-based auto "Sold Out"** when stock < threshold
- ✅ **Batch-wise stock tracking** with FIFO (First In, First Out)
- ✅ **Multi-branch stock management**

#### API Endpoints

```http
GET    /api/inventory/alerts                    # Get stock alerts (low stock, out of stock, expiring)
POST   /api/inventory/weight-loss               # Record weight loss
POST   /api/inventory/water-loss                # Record water loss  
POST   /api/inventory/wastage-loss              # Record wastage loss
POST   /api/inventory/transfer                  # Transfer stock between branches
PUT    /api/inventory/thresholds/bulk           # Bulk update stock thresholds
GET    /api/inventory/valuation-with-costs      # Get inventory valuation with cost allocation
POST   /api/inventory/process-expired-batches   # Process expired batches automatically
```

#### Example: Record Weight Loss
```json
POST /api/inventory/weight-loss
{
    "product_id": 1,
    "branch_id": 1,
    "initial_weight": 10.0,
    "current_weight": 9.5,
    "reason": "Storage moisture loss"
}
```

### 2. Loss Tracking Submodule

#### Enhanced Loss Types
- ✅ **Weight Loss** (e.g., 1kg apple → 950g after storage)
- ✅ **Water Loss** (fresh vegetables losing moisture)
- ✅ **Wastage Loss** (damaged/spoiled items)
- ✅ **Complimentary Loss** (customer adjustments)
- ✅ **Damage, Theft, Expiry** tracking

#### API Endpoints

```http
GET    /api/loss-tracking                       # List all loss records
POST   /api/loss-tracking                       # Create new loss record
GET    /api/loss-tracking/{id}                  # Get specific loss record
PUT    /api/loss-tracking/{id}                  # Update loss record
DELETE /api/loss-tracking/{id}                  # Delete loss record
GET    /api/loss-tracking/analytics             # Get loss analytics
GET    /api/loss-tracking/trends                # Get loss trends over time
GET    /api/loss-tracking/critical-alerts       # Get critical loss alerts
POST   /api/loss-tracking/bulk                  # Bulk record losses
GET    /api/loss-tracking/prevention-recommendations  # Get loss prevention recommendations
GET    /api/loss-tracking/export                # Export loss data
```

#### Example: Loss Analytics
```json
GET /api/loss-tracking/analytics?start_date=2024-01-01&end_date=2024-01-31&branch_id=1

Response:
{
    "status": "success",
    "data": {
        "by_type": {
            "weight_loss": {
                "total_quantity": 15.5,
                "total_financial_loss": 1250.00,
                "count": 8
            },
            "wastage": {
                "total_quantity": 5.2,
                "total_financial_loss": 890.00,
                "count": 3
            }
        },
        "summary": {
            "total_financial_loss": 2140.00,
            "total_quantity_lost": 20.7
        }
    }
}
```

### 3. Extra Quantity Tracking (Complimentary/Adjustment)

#### Features
- ✅ **Customer order variations** (520g ordered → 500g billed → 20g complimentary)
- ✅ **Automatic recording** of complimentary quantities
- ✅ **Financial impact tracking**

### 4. Product & Pricing Management

#### Enhanced Features
- ✅ **Advanced categorization** (Fruit/Vegetable/Leafy/Exotic/Herbs/Dry Fruits/Organic)
- ✅ **Subcategory management**
- ✅ **Vendor-specific pricing**
- ✅ **Branch-wise selling prices**
- ✅ **Shelf life and storage management**

#### API Endpoints

```http
GET    /api/products/categories                 # Get all categories and subcategories
PUT    /api/products/{id}/branch-pricing        # Update branch-specific pricing
PUT    /api/products/{id}/vendor-pricing        # Update vendor-specific pricing
PUT    /api/products/categories/bulk            # Bulk update product categories
GET    /api/products/category/{category}        # Get products by category with analysis
```

#### Example: Update Branch Pricing
```json
PUT /api/products/1/branch-pricing
{
    "branch_pricing": [
        {
            "branch_id": 1,
            "selling_price": 120.00,
            "is_available_online": true
        },
        {
            "branch_id": 2,
            "selling_price": 115.00,
            "is_available_online": false
        }
    ]
}
```

### 5. Sales & Billing Management

#### Enhanced Features
- ✅ **Quick on-shop billing** with instant invoice generation
- ✅ **Online payment processing** (UPI/Card/COD)
- ✅ **Bulk invoice generation**
- ✅ **Partial payment handling**
- ✅ **Tax calculation and breakdown**

#### API Endpoints

```http
GET    /api/billing/invoice/{order}             # Generate invoice for order
POST   /api/billing/quick-billing               # Quick billing for on-shop sales
POST   /api/billing/online-payment/{order}      # Process online payment
POST   /api/billing/bulk-invoice                # Generate bulk invoice
POST   /api/billing/partial-payment/{order}     # Process partial payment
GET    /api/billing/summary                     # Get billing summary
GET    /api/billing/pending-payments            # Get pending payments report
```

#### Example: Quick Billing
```json
POST /api/billing/quick-billing
{
    "customer_name": "John Doe",
    "customer_phone": "9876543210",
    "items": [
        {
            "product_id": 1,
            "quantity": 2.5,
            "actual_weight": 2.6,
            "custom_price": 80.00
        }
    ],
    "payment_method": "upi",
    "discount_amount": 10.00,
    "print_invoice": true
}
```

### 6. Delivery Boy Adjustment Module

#### Features
- ✅ **Real-time delivery tracking** with GPS
- ✅ **Customer return processing** during delivery
- ✅ **Quantity adjustments** on delivery
- ✅ **Automatic invoice regeneration**
- ✅ **Mobile app ready endpoints**

#### API Endpoints (Delivery Boy Role Required)

```http
GET    /api/delivery/orders                     # Get assigned delivery orders
PUT    /api/delivery/orders/{order}/start       # Start delivery (mark as out for delivery)
PUT    /api/delivery/orders/{order}/process     # Process delivery with adjustments
PUT    /api/delivery/orders/{order}/location    # Update GPS location
POST   /api/delivery/orders/{order}/quick-return # Quick return items
GET    /api/delivery/history                    # Get delivery history
GET    /api/delivery/stats                      # Get delivery statistics
GET    /api/delivery/optimized-route            # Get optimized delivery route
```

#### Example: Process Delivery with Adjustments
```json
PUT /api/delivery/orders/123/process
{
    "delivery_status": "delivered",
    "customer_adjustments": [
        {
            "order_item_id": 456,
            "action": "return",
            "quantity": 0.5,
            "reason": "Customer didn't want this quantity"
        },
        {
            "order_item_id": 457,
            "action": "reduce_quantity",
            "quantity": 2.0,
            "reason": "Partial acceptance"
        }
    ],
    "delivery_notes": "Customer was satisfied with quality"
}
```

### 7. Wholesaler Billing

#### Features
- ✅ **Tiered pricing** based on quantity
- ✅ **Customer-specific pricing**
- ✅ **Bulk purchase discounts**
- ✅ **Credit management**
- ✅ **Customer type-based pricing** (Distributor/Retailer/Wholesale)

#### API Endpoints

```http
GET    /api/wholesale/pricing-tiers             # Get wholesale pricing tiers
POST   /api/wholesale/pricing-tiers             # Create pricing tier
PUT    /api/wholesale/pricing-tiers/{id}        # Update pricing tier
DELETE /api/wholesale/pricing-tiers/{id}        # Delete pricing tier
POST   /api/wholesale/calculate-pricing         # Calculate wholesale pricing for cart
POST   /api/wholesale/orders                    # Create wholesale order
GET    /api/wholesale/orders                    # Get wholesale orders
GET    /api/wholesale/orders/{order}/invoice    # Generate wholesale invoice
GET    /api/wholesale/customer-analysis         # Get customer purchase analysis
GET    /api/wholesale/performance-metrics       # Get wholesale performance metrics
```

#### Example: Calculate Wholesale Pricing
```json
POST /api/wholesale/calculate-pricing
{
    "customer_id": 123,
    "items": [
        {
            "product_id": 1,
            "quantity": 50
        },
        {
            "product_id": 2,
            "quantity": 100
        }
    ]
}

Response:
{
    "status": "success",
    "data": {
        "items": [
            {
                "product_name": "Apple",
                "quantity": 50,
                "regular_total": 5000.00,
                "wholesale_total": 4250.00,
                "final_total": 4037.50,
                "savings": 962.50,
                "pricing_tier": {
                    "customer_type": "distributor",
                    "min_quantity": 50,
                    "discount_percentage": 15
                }
            }
        ],
        "summary": {
            "total_savings": 1425.50,
            "discount_percentage": 12.5
        }
    }
}
```

### 8. Expense & Cost Allocation

#### Features
- ✅ **Transport costs** (CNG, Diesel, Delivery Vehicle)
- ✅ **Labour costs** (Loading/Unloading)
- ✅ **Operational costs** distribution
- ✅ **True profit margin calculation** with allocated costs
- ✅ **Multiple allocation methods** (Equal, Weighted, Manual)

#### API Endpoints

```http
GET    /api/expenses                           # List expenses
POST   /api/expenses                           # Create expense with allocation
GET    /api/expenses/{id}                      # Get specific expense
PUT    /api/expenses/{id}                      # Update expense
DELETE /api/expenses/{id}                      # Delete expense
PUT    /api/expenses/{id}/approve              # Approve expense
PUT    /api/expenses/{id}/reject               # Reject expense
PUT    /api/expenses/{id}/mark-paid            # Mark as paid
GET    /api/expenses/allocation/report         # Get allocation report
GET    /api/expenses/cost/analysis             # Get cost analysis for products
GET    /api/expenses/summary                   # Get expense summary
```

#### Example: Create Expense with Allocation
```json
POST /api/expenses
{
    "expense_category_id": 1,
    "branch_id": 1,
    "title": "Daily CNG Refill",
    "description": "CNG refill for delivery vehicle",
    "amount": 500.00,
    "expense_date": "2024-01-15",
    "payment_method": "cash",
    "expense_type": "transport",
    "allocation_method": "equal",
    "allocation_products": [1, 2, 3, 4, 5],
    "notes": "Daily transport cost allocation"
}
```

## 🚀 Automated Features

### Stock Management Automation
1. **Auto stock reduction** after each sale
2. **Automatic "Sold Out" status** when stock ≤ threshold
3. **Batch expiry processing** with automatic wastage recording
4. **Online availability updates** based on stock levels

### Loss Tracking Automation
1. **Complimentary quantity tracking** during sales
2. **Automatic financial loss calculation**
3. **Batch quantity updates** after loss recording
4. **Stock adjustments** after loss events

### Cost Allocation Automation
1. **Automatic expense distribution** to products
2. **Real-time cost per unit calculation**
3. **Dynamic profit margin updates**
4. **True profitability analysis**

## 📱 Mobile App Integration Ready

### Delivery Boy Features
- Real-time GPS tracking
- Order status updates
- Customer adjustment processing
- Return management
- Route optimization

### Manager Features
- Stock alerts and notifications
- Loss tracking on-the-go
- Expense recording
- Quick billing

## 🔐 Role-Based Access Control

### Admin
- Full access to all modules
- System configuration
- User management
- All reports and analytics

### Branch Manager
- Branch-specific operations
- Inventory management
- Expense management
- Branch reports

### Cashier
- Sales operations
- Quick billing
- Customer management
- Payment processing

### Delivery Boy
- Delivery management
- Customer adjustments
- Return processing
- Location updates

## 🎯 Key Business Benefits

1. **Reduced Manual Work**: Auto stock updates and threshold management
2. **Better Loss Control**: Comprehensive loss tracking and prevention recommendations
3. **Accurate Costing**: True product costs with expense allocation
4. **Flexible Pricing**: Branch-wise and customer-type-based pricing
5. **Efficient Deliveries**: Real-time adjustments and return processing
6. **Wholesale Management**: Tiered pricing and bulk order handling

## 🔄 Automated Tasks

Run automated tasks using the console command:

```bash
# Process all automated tasks
php artisan system:process-automated-tasks

# Process specific tasks
php artisan system:process-automated-tasks --expired-batches
php artisan system:process-automated-tasks --stock-alerts
php artisan system:process-automated-tasks --online-availability
```

## 📊 Reports and Analytics

### Available Reports
1. **Inventory Valuation** with cost allocation
2. **Loss Analytics** by type, product, and branch
3. **Wholesale Customer Analysis**
4. **Expense Allocation Reports**
5. **Billing Summary Reports**
6. **Delivery Performance Metrics**

### Export Features
- Loss data export (CSV/JSON)
- Expense allocation reports
- Customer purchase analysis
- Inventory valuation reports

## 🔧 Setup Instructions

1. **Run Migrations**:
```bash
php artisan migrate
```

2. **Seed Enhanced Data**:
```bash
php artisan db:seed --class=EnhancedSystemSeeder
```

3. **Set Up Automated Tasks** (Optional - Add to crontab):
```bash
# Add to crontab for automated processing
0 2 * * * cd /path/to/project && php artisan system:process-automated-tasks
```

## 🎨 Frontend Integration

All endpoints return standardized JSON responses:

```json
{
    "status": "success|error",
    "message": "Human readable message",
    "data": { ... },
    "errors": { ... } // Only for validation errors
}
```

The system is designed to work seamlessly with:
- Web applications (Blade templates)
- Mobile applications (React Native, Flutter)
- Third-party integrations (APIs)
- POS systems integration

## 🔍 Advanced Features

### Intelligent Stock Management
- FIFO batch processing
- Automatic expiry handling
- Smart threshold management
- Multi-branch synchronization

### Cost Intelligence
- Dynamic cost allocation
- Real-time profit calculations
- Expense impact analysis
- True margin visibility

### Customer Intelligence
- Wholesale tier management
- Credit limit monitoring
- Purchase pattern analysis
- Loyalty program ready

This enhanced system provides a complete solution for modern fruit and vegetable businesses with multiple branches, comprehensive tracking, and intelligent automation.