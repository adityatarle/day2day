# Food Company API Documentation

## Overview
This API provides comprehensive management for a food company with multi-city outlets, POS systems, and dynamic pricing.

## Authentication

### Standard Login
```http
POST /api/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

### Outlet-Specific Login
```http
POST /api/outlet/login
Content-Type: application/json

{
    "email": "staff@outlet.com",
    "password": "password",
    "outlet_code": "MUM-CENTRAL"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "user": { /* user object */ },
        "outlet": { /* outlet object */ },
        "token": "bearer_token_here",
        "permissions": ["permission1", "permission2"]
    },
    "message": "Login successful"
}
```

## City Management

### List Cities
```http
GET /api/cities
Authorization: Bearer {token}
```

### Create City
```http
POST /api/cities
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Mumbai",
    "state": "Maharashtra",
    "country": "India",
    "code": "MUM",
    "delivery_charge": 50.00,
    "tax_rate": 18.00
}
```

### Set Product Pricing for City
```http
POST /api/cities/{cityId}/product-pricing
Authorization: Bearer {token}
Content-Type: application/json

{
    "product_id": 1,
    "selling_price": 150.00,
    "mrp": 180.00,
    "discount_percentage": 10,
    "effective_from": "2025-01-01",
    "effective_until": null,
    "is_available": true
}
```

### Get City Product Pricing
```http
GET /api/cities/{cityId}/product-pricing?current_only=true&product_id=1
Authorization: Bearer {token}
```

## Outlet Management

### List Outlets
```http
GET /api/outlets?city_id=1&outlet_type=retail&pos_enabled=true
Authorization: Bearer {token}
```

### Create Outlet
```http
POST /api/outlets
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Mumbai Central Store",
    "code": "MUM-CENTRAL",
    "address": "123 Main Street, Mumbai",
    "phone": "+91-9876543210",
    "email": "mumbai@company.com",
    "city_id": 1,
    "outlet_type": "retail",
    "latitude": 19.0760,
    "longitude": 72.8777,
    "pos_enabled": true,
    "pos_terminal_id": "POS-MUM-CENTRAL",
    "operating_hours": {
        "monday": {"open": "09:00", "close": "22:00"},
        "tuesday": {"open": "09:00", "close": "22:00"},
        "wednesday": {"open": "09:00", "close": "22:00"},
        "thursday": {"open": "09:00", "close": "22:00"},
        "friday": {"open": "09:00", "close": "22:00"},
        "saturday": {"open": "09:00", "close": "23:00"},
        "sunday": {"open": "10:00", "close": "21:00"}
    }
}
```

### Get Outlet Details
```http
GET /api/outlets/{outletId}
Authorization: Bearer {token}
```

### Create Outlet Staff
```http
POST /api/outlets/{outletId}/staff
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@outlet.com",
    "phone": "+91-9876543210",
    "password": "password123",
    "role_id": 3
}
```

### Get Outlet Performance
```http
GET /api/outlets/{outletId}/performance
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "daily_sales": 15000.00,
        "monthly_sales": 450000.00,
        "daily_orders": 45,
        "monthly_orders": 1350,
        "active_staff": 5,
        "current_pos_session": { /* session object or null */ },
        "is_open": true
    }
}
```

## POS System

### Start POS Session
```http
POST /api/pos/start-session
Authorization: Bearer {token}
Content-Type: application/json

{
    "branch_id": 1,
    "terminal_id": "POS-MUM-CENTRAL",
    "opening_cash": 5000.00
}
```

### Get Current Session
```http
GET /api/pos/current-session
Authorization: Bearer {token}
```

### Get Products for POS
```http
GET /api/pos/products?search=apple&category=fruits
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Apple",
            "code": "APL001",
            "category": "fruits",
            "selling_price": 120.00,
            "city_price": 150.00,
            "is_available_in_city": true
        }
    ]
}
```

### Process Sale
```http
POST /api/pos/process-sale
Authorization: Bearer {token}
Content-Type: application/json

{
    "customer_id": null,
    "items": [
        {
            "product_id": 1,
            "quantity": 2,
            "price": 150.00
        },
        {
            "product_id": 2,
            "quantity": 1,
            "price": 200.00
        }
    ],
    "payment_method": "cash",
    "discount_amount": 25.00,
    "tax_amount": 81.00,
    "customer_name": "John Doe",
    "customer_phone": "+91-9876543210"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "order": {
            "id": 123,
            "order_number": "POS-20250904123456-1",
            "total_amount": 556.00,
            "payment_method": "cash",
            "status": "completed"
        },
        "session": {
            "total_transactions": 5,
            "total_sales": 2500.00
        }
    }
}
```

### Close Session
```http
POST /api/pos/close-session
Authorization: Bearer {token}
Content-Type: application/json

{
    "closing_cash": 7500.00,
    "notes": "Normal closing, no issues"
}
```

### Get Session Summary
```http
GET /api/pos/session-summary
Authorization: Bearer {token}
```

### Get Session History
```http
GET /api/pos/session-history?status=closed&date_from=2025-01-01&date_to=2025-01-31
Authorization: Bearer {token}
```

## Error Responses

### Standard Error Format
```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```

### Common HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## Web Interface URLs

### Outlet Management
- `/outlets` - Outlet listing
- `/outlets/create` - Create new outlet
- `/outlets/{id}` - Outlet details
- `/outlets/{id}/edit` - Edit outlet
- `/outlets/{id}/staff` - Manage staff

### POS System
- `/pos` - POS main interface
- `/pos/start-session` - Start new session
- `/pos/close-session` - Close current session
- `/pos/sales` - Sales interface
- `/pos/history` - Session history

### Outlet Login
- `/outlet/{outletCode}/login` - Outlet-specific staff login

## City-Based Pricing Logic

### Price Resolution Order
1. **City-Specific Pricing** - Check if product has specific pricing for the city
2. **Effective Date Range** - Ensure pricing is currently effective
3. **Availability Check** - Verify product is available in the city
4. **Fallback** - Use default product price if no city pricing found

### Example Price Calculation
```php
// Get city price for a product
$cityPrice = $product->getCityPrice($cityId);

// Check availability in city
$isAvailable = $product->isAvailableInCity($cityId);

// Apply discount if applicable
$finalPrice = $cityPricing->getFinalPrice(); // Applies discount percentage
```

## POS Session Workflow

### 1. Session Start
- Cashier starts session with opening cash amount
- System validates no other active session on terminal
- Session becomes active and ready for sales

### 2. Processing Sales
- Add products to cart with city-specific pricing
- Apply discounts and calculate taxes
- Process payment (cash/card/UPI/credit)
- Generate receipt and update inventory

### 3. Session Close
- Count closing cash amount
- Calculate expected vs actual cash
- Record any variance and notes
- Session marked as closed

## Integration Examples

### Mobile App Integration
```javascript
// Login to outlet
const loginResponse = await fetch('/api/outlet/login', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        email: 'staff@outlet.com',
        password: 'password',
        outlet_code: 'MUM-CENTRAL'
    })
});

// Start POS session
const sessionResponse = await fetch('/api/pos/start-session', {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        branch_id: 1,
        terminal_id: 'POS-MUM-CENTRAL',
        opening_cash: 5000.00
    })
});
```

### Web Interface Integration
```javascript
// Load products with city pricing
async function loadProducts() {
    const response = await fetch('/api/pos/products', {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });
    
    const data = await response.json();
    return data.data; // Products with city_price property
}
```

## Security Considerations

### Token Scoping
- Outlet tokens are scoped to specific outlet codes
- Users can only access their assigned outlet's data
- Admin tokens have global access

### Session Security
- One active session per terminal prevents conflicts
- Session suspension on logout prevents unauthorized access
- Cash tracking provides audit trail

### Role-Based Access
- **Admin**: All outlets and cities
- **Branch Manager**: Assigned outlets only
- **Cashier**: POS operations only
- **Delivery Boy**: No outlet management access

## Performance Optimizations

### Database Indexes
- City-product pricing indexed for fast lookups
- POS session queries optimized with composite indexes
- Branch queries include city relationships

### Caching Strategies
- City pricing can be cached for frequently accessed products
- Outlet information cached for login pages
- Session data cached during active POS operations

## Monitoring & Analytics

### Key Metrics
- Sales per outlet per day/month
- Average transaction value by outlet
- Cash variance tracking
- Product performance by city
- Staff productivity metrics

### Reports Available
- Outlet performance comparison
- City-wise sales analysis
- POS session audit reports
- Price variance impact analysis

This API provides a complete solution for managing a multi-outlet food company with dynamic pricing and comprehensive POS functionality.