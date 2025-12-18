# Mobile API Setup Summary

This document summarizes the API setup completed for Flutter mobile application development.

## What Was Created

### 1. API Resources (Consistent JSON Responses)
Created in `app/Http/Resources/`:
- **ProductResource.php** - Standardized product data format
- **OrderResource.php** - Standardized order data format
- **OrderItemResource.php** - Standardized order item data format
- **CustomerResource.php** - Standardized customer data format
- **PosSessionResource.php** - Standardized POS session data format
- **DeliveryResource.php** - Standardized delivery data format

These resources ensure consistent JSON responses across all API endpoints.

### 2. API Controllers for Mobile App
Created in `app/Http/Controllers/Api/`:
- **CustomerApiController.php** - Customer management endpoints
  - List customers
  - Get customer by ID
  - Create customer
  - Update customer
  - Search customers
  - Get purchase history

- **BranchApiController.php** - Branch information endpoints
  - Get current branch
  - Get branch statistics
  - List all branches (admin only)

- **DashboardApiController.php** - Dashboard data endpoints
  - Get dashboard summary
  - Get sales chart data

### 3. Updated API Routes
Updated `routes/api.php` to include:
- Customer management routes (`/api/customers/*`)
- Dashboard routes (`/api/dashboard/*`)
- Branch routes (`/api/branch/*`)

All routes are protected with `auth:sanctum` middleware and role-based access control.

### 4. Documentation Files
Created comprehensive documentation:
- **MOBILE_API_DOCUMENTATION.md** - Complete API documentation with examples
- **API_QUICK_REFERENCE.md** - Quick reference guide for common endpoints
- **MOBILE_API_SETUP_SUMMARY.md** - This summary document

## Available API Endpoints

### Authentication
- `POST /api/v1/login` - User login
- `GET /api/profile` - Get user profile
- `POST /api/logout` - Logout

### Dashboard
- `GET /api/dashboard` - Get dashboard data
- `GET /api/dashboard/sales-chart` - Get sales chart data

### Products
- `GET /api/products` - List products
- `GET /api/products/{id}` - Get product by ID
- `GET /api/products/search` - Search products
- `POST /api/products` - Create product (admin/branch manager)
- `PUT /api/products/{id}` - Update product (admin/branch manager)

### POS System
- `POST /api/pos/start-session` - Start POS session
- `GET /api/pos/current-session` - Get current session
- `GET /api/pos/products` - Get POS products
- `POST /api/pos/process-sale` - Process sale
- `POST /api/pos/close-session` - Close session
- `GET /api/pos/session-history` - Get session history
- `GET /api/pos/session-summary` - Get session summary

### Orders
- `GET /api/orders` - List orders
- `GET /api/orders/{id}` - Get order by ID
- `POST /api/orders` - Create order
- `PUT /api/orders/{id}` - Update order
- `POST /api/orders/{id}/cancel` - Cancel order
- `GET /api/orders/statistics` - Get order statistics

### Customers
- `GET /api/customers` - List customers
- `GET /api/customers/{id}` - Get customer by ID
- `POST /api/customers` - Create customer
- `PUT /api/customers/{id}` - Update customer
- `GET /api/customers/search` - Search customers
- `GET /api/customers/{id}/purchase-history` - Get purchase history

### Branch
- `GET /api/branch/current` - Get current branch
- `GET /api/branch/statistics` - Get branch statistics
- `GET /api/branches/all` - List all branches (admin only)

## Authentication

The API uses **Laravel Sanctum** for authentication. After login, you'll receive a bearer token that must be included in all subsequent requests:

```
Authorization: Bearer {token}
```

## Response Format

All API responses follow a consistent format:

### Success Response
```json
{
  "success": true,
  "data": { ... },
  "message": "Optional message"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Error details"]
  }
}
```

### Paginated Response
```json
{
  "success": true,
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100
  }
}
```

## Testing

### Test Credentials
- **Admin**: `admin@foodcompany.com` / `admin123`
- **Cashier**: `cashier@foodcompany.com` / `cashier123`
- **Branch Manager**: `manager@foodcompany.com` / `manager123`

### Testing Tools
You can test the API using:
- Postman
- cURL
- Flutter HTTP packages (http, dio, etc.)

## Next Steps for Flutter Developer

1. **Read the Documentation**
   - Start with `MOBILE_API_DOCUMENTATION.md` for complete details
   - Use `API_QUICK_REFERENCE.md` for quick lookups

2. **Set Up HTTP Client**
   - Install HTTP package: `http` or `dio`
   - Set up base URL configuration
   - Implement authentication token storage

3. **Implement Core Features**
   - Authentication flow
   - Dashboard screen
   - Product listing
   - POS functionality
   - Order management
   - Customer management

4. **Error Handling**
   - Implement proper error handling for all API calls
   - Show user-friendly error messages
   - Handle network errors gracefully

5. **State Management**
   - Use state management solution (Provider, Riverpod, Bloc, etc.)
   - Cache API responses where appropriate
   - Implement offline support if needed

## Important Notes

1. **Base URL**: Update the base URL in your Flutter app based on environment (development/production)

2. **Token Storage**: Store authentication tokens securely using `flutter_secure_storage`

3. **Date Formats**: All dates are in ISO 8601 format

4. **Decimal Precision**: Monetary values are floats with 2 decimal precision

5. **Pagination**: Implement pagination for list endpoints to improve performance

6. **Role-Based Access**: Some endpoints are restricted based on user roles

## Support

For questions or issues:
1. Refer to `MOBILE_API_DOCUMENTATION.md` for detailed endpoint documentation
2. Check `API_QUICK_REFERENCE.md` for quick endpoint lookups
3. Review the existing `API_GUIDE.md` for additional API information

## Files Modified/Created

### Created Files
- `app/Http/Resources/ProductResource.php`
- `app/Http/Resources/OrderResource.php`
- `app/Http/Resources/OrderItemResource.php`
- `app/Http/Resources/CustomerResource.php`
- `app/Http/Resources/PosSessionResource.php`
- `app/Http/Resources/DeliveryResource.php`
- `app/Http/Controllers/Api/CustomerApiController.php`
- `app/Http/Controllers/Api/BranchApiController.php`
- `app/Http/Controllers/Api/DashboardApiController.php`
- `MOBILE_API_DOCUMENTATION.md`
- `API_QUICK_REFERENCE.md`
- `MOBILE_API_SETUP_SUMMARY.md`

### Modified Files
- `routes/api.php` - Added new API routes

## API Endpoints Summary

| Endpoint | Method | Description | Auth Required |
|----------|--------|-------------|---------------|
| `/api/v1/login` | POST | User login | No |
| `/api/profile` | GET | Get user profile | Yes |
| `/api/logout` | POST | Logout | Yes |
| `/api/dashboard` | GET | Dashboard data | Yes |
| `/api/dashboard/sales-chart` | GET | Sales chart data | Yes |
| `/api/products` | GET | List products | Yes |
| `/api/products/{id}` | GET | Get product | Yes |
| `/api/products/search` | GET | Search products | Yes |
| `/api/pos/start-session` | POST | Start POS session | Yes |
| `/api/pos/current-session` | GET | Get current session | Yes |
| `/api/pos/products` | GET | Get POS products | Yes |
| `/api/pos/process-sale` | POST | Process sale | Yes |
| `/api/pos/close-session` | POST | Close session | Yes |
| `/api/orders` | GET | List orders | Yes |
| `/api/orders/{id}` | GET | Get order | Yes |
| `/api/orders` | POST | Create order | Yes |
| `/api/customers` | GET | List customers | Yes |
| `/api/customers/{id}` | GET | Get customer | Yes |
| `/api/customers` | POST | Create customer | Yes |
| `/api/customers/search` | GET | Search customers | Yes |
| `/api/branch/current` | GET | Get current branch | Yes |
| `/api/branch/statistics` | GET | Get branch stats | Yes |

---

**All set! Your API is ready for Flutter mobile app development.** 🚀
