# Outlet & POS System Guide

This guide covers the new outlet management and POS (Point of Sale) system features added to your food company management system.

## Features Overview

### 1. City-Based Outlet Management
- **Multi-City Support**: Manage outlets across different cities
- **City-Specific Pricing**: Set different product prices for different cities
- **Outlet Types**: Support for retail, wholesale, and kiosk outlets
- **Location Tracking**: GPS coordinates and operating hours for each outlet

### 2. Point of Sale (POS) System
- **Session Management**: Start/close POS sessions with cash tracking
- **Real-time Sales**: Process sales with automatic inventory updates
- **Multiple Payment Methods**: Cash, Card, UPI, and Credit support
- **Receipt Generation**: Automatic invoice generation for each sale

### 3. City-Based Pricing
- **Dynamic Pricing**: Different prices for the same product in different cities
- **Time-Based Pricing**: Set effective dates for pricing changes
- **Discount Management**: City-specific discount percentages
- **Availability Control**: Control product availability per city

## Database Schema

### Cities Table
```sql
- id: Primary key
- name: City name
- state: State/province
- country: Country (default: India)
- code: Unique city code (e.g., 'MUM', 'DEL')
- delivery_charge: Base delivery charge for the city
- tax_rate: City-specific tax rate
- is_active: Boolean status
```

### Enhanced Branches Table
```sql
- city_id: Foreign key to cities table
- latitude/longitude: GPS coordinates
- outlet_type: retail/wholesale/kiosk
- operating_hours: JSON field for store hours
- pos_enabled: Boolean for POS availability
- pos_terminal_id: Unique terminal identifier
```

### City Product Pricing Table
```sql
- city_id: Foreign key to cities
- product_id: Foreign key to products
- selling_price: City-specific price
- mrp: Maximum retail price
- discount_percentage: Discount amount
- effective_from/until: Price validity period
- is_available: Product availability in city
```

### POS Sessions Table
```sql
- user_id: Cashier/user running the session
- branch_id: Outlet where session is running
- terminal_id: POS terminal identifier
- opening_cash: Cash amount at session start
- closing_cash: Cash amount at session end
- total_transactions: Number of sales processed
- total_sales: Total sales amount
- started_at/ended_at: Session timestamps
- status: active/closed/suspended
```

## API Endpoints

### City Management
```
GET    /api/cities                          - List all cities
POST   /api/cities                          - Create new city
GET    /api/cities/{id}                     - Get city details
PUT    /api/cities/{id}                     - Update city
DELETE /api/cities/{id}                     - Delete city
POST   /api/cities/{id}/product-pricing     - Set product pricing for city
GET    /api/cities/{id}/product-pricing     - Get city product pricing
```

### Outlet Management
```
GET    /api/outlets                         - List all outlets
POST   /api/outlets                         - Create new outlet
GET    /api/outlets/{id}                    - Get outlet details
PUT    /api/outlets/{id}                    - Update outlet
DELETE /api/outlets/{id}                    - Delete outlet
GET    /api/cities/{id}/outlets             - Get outlets by city
POST   /api/outlets/{id}/staff              - Create outlet staff
GET    /api/outlets/{id}/performance        - Get outlet metrics
```

### POS System
```
POST   /api/pos/start-session               - Start POS session
GET    /api/pos/current-session             - Get current active session
POST   /api/pos/process-sale                - Process a sale
POST   /api/pos/close-session               - Close POS session
GET    /api/pos/products                    - Get products with city pricing
GET    /api/pos/session-history             - Get session history
GET    /api/pos/session-summary             - Get current session summary
```

## Web Interface Routes

### Outlet Management
```
GET    /outlets                             - Outlet listing page
GET    /outlets/create                      - Create outlet form
POST   /outlets                             - Store new outlet
GET    /outlets/{id}                        - Outlet details page
GET    /outlets/{id}/edit                   - Edit outlet form
PUT    /outlets/{id}                        - Update outlet
DELETE /outlets/{id}                        - Delete outlet
GET    /outlets/{id}/staff                  - Manage outlet staff
```

### POS System
```
GET    /pos                                 - POS main interface
GET    /pos/start-session                   - Start session form
POST   /pos/start-session                   - Process session start
GET    /pos/close-session                   - Close session form
POST   /pos/close-session                   - Process session close
GET    /pos/sales                           - Sales interface
GET    /pos/history                         - Session history
```

## Usage Examples

### 1. Setting Up a New City
```json
POST /api/cities
{
    "name": "Mumbai",
    "state": "Maharashtra",
    "country": "India",
    "code": "MUM",
    "delivery_charge": 50.00,
    "tax_rate": 18.00
}
```

### 2. Creating an Outlet
```json
POST /api/outlets
{
    "name": "Mumbai Central Store",
    "code": "MUM-CENTRAL",
    "address": "123 Main Street, Mumbai Central",
    "phone": "+91-9876543210",
    "email": "mumbai.central@foodcompany.com",
    "city_id": 1,
    "outlet_type": "retail",
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

### 3. Setting City-Specific Product Pricing
```json
POST /api/cities/1/product-pricing
{
    "product_id": 1,
    "selling_price": 150.00,
    "mrp": 180.00,
    "discount_percentage": 10,
    "effective_from": "2025-01-01",
    "is_available": true
}
```

### 4. Starting a POS Session
```json
POST /api/pos/start-session
{
    "branch_id": 1,
    "terminal_id": "POS-MUM-CENTRAL",
    "opening_cash": 5000.00
}
```

### 5. Processing a Sale
```json
POST /api/pos/process-sale
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
    "tax_amount": 81.00
}
```

## Key Features

### City-Based Pricing Benefits
1. **Regional Pricing Strategy**: Adjust prices based on local market conditions
2. **Competition Response**: Quick price adjustments for competitive markets
3. **Seasonal Pricing**: Different prices for different seasons/regions
4. **Supply Chain Costs**: Factor in transportation and local costs

### POS System Benefits
1. **Session Tracking**: Complete audit trail of all transactions
2. **Cash Management**: Track opening/closing cash with variance reports
3. **Real-time Inventory**: Automatic stock updates with each sale
4. **Multi-Payment Support**: Accept various payment methods
5. **Customer Management**: Link sales to customer profiles
6. **Performance Metrics**: Track sales performance per session/outlet

### Outlet Management Benefits
1. **Centralized Control**: Manage all outlets from a single dashboard
2. **Performance Monitoring**: Track sales and performance metrics
3. **Staff Management**: Assign and manage staff for each outlet
4. **Operating Hours**: Control when outlets are open/closed
5. **Location Services**: GPS tracking for delivery optimization

## Security & Access Control

### Role-Based Access
- **Admin**: Full access to all features
- **Branch Manager**: Manage assigned outlets and POS
- **Cashier**: Access to POS system only
- **Delivery Boy**: No access to outlet/POS management

### Session Security
- One active session per terminal
- User-specific session tracking
- Automatic session timeout (configurable)
- Cash variance tracking and alerts

## Integration Points

### Existing System Integration
- **Product Management**: Uses existing product catalog
- **Customer Management**: Integrates with existing customer database
- **Order Management**: Creates orders through existing system
- **Inventory Management**: Updates stock through existing mechanisms
- **User Management**: Uses existing role-based access control

### Future Enhancements
- **Mobile POS App**: React Native app for tablet-based POS
- **Loyalty Program**: Integration with customer loyalty points
- **Advanced Analytics**: AI-powered sales predictions
- **Multi-Currency**: Support for international operations
- **Franchise Management**: Tools for franchise outlet management

## Getting Started

1. **Setup Cities**: Create cities where you operate
2. **Create Outlets**: Add your physical outlets/branches
3. **Set Pricing**: Configure city-specific product pricing
4. **Train Staff**: Provide POS training to cashiers
5. **Start Selling**: Begin processing sales through POS

## Support & Troubleshooting

### Common Issues
1. **No Active Session**: Ensure POS session is started before processing sales
2. **City Pricing Not Found**: Verify product has pricing set for the outlet's city
3. **Stock Updates**: Check product-branch relationships are properly configured
4. **Access Denied**: Verify user has appropriate role permissions

### Technical Requirements
- PHP 8.4+
- Laravel 12+
- MySQL/PostgreSQL database
- Web browser with JavaScript enabled
- Stable internet connection for API calls

For technical support, refer to the main system documentation or contact the development team.