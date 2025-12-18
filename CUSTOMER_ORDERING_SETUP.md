# Customer Ordering System Setup

This document explains the customer-facing ordering system that has been implemented for both mobile app and website.

## Overview

The system now includes:
1. **Customer Home Page** - Public-facing landing page
2. **Store Location API** - Find nearest stores based on GPS coordinates
3. **Customer Order API** - Place orders from mobile app or website
4. **Nearest Store Detection** - Automatically shows nearest store based on location

## Features

### 1. Customer Home Page (`/`)
- Public-facing landing page (no login required)
- Shows featured products
- Displays all available stores
- "Find Nearest Store" button using GPS
- Links to shop at specific stores

### 2. Store Location API

#### Find Nearest Stores
```
GET /api/stores/nearest?latitude=19.0760&longitude=72.8777&radius=10
```
- Returns stores sorted by distance
- Default radius: 10km
- Calculates distance using Haversine formula

#### Get All Stores
```
GET /api/stores?city_id=1
```
- Returns all active stores
- Optional city filter

#### Get Store Details
```
GET /api/stores/{id}
```
- Returns detailed store information

### 3. Customer Order API

#### Create Order
```
POST /api/customer/orders
```
- No authentication required
- Creates customer if doesn't exist
- Validates stock availability
- Updates inventory automatically

#### Get Customer Orders
```
GET /api/customer/orders?customer_id=1
GET /api/customer/orders?phone=1234567890
```
- Retrieve orders by customer ID or phone number

#### Get Order Details
```
GET /api/customer/orders/{id}
```
- Get full order details with items

## Mobile App Integration

### Flutter Implementation Example

```dart
// 1. Get user location and find nearest store
Future<Map<String, dynamic>> findNearestStore(double lat, double lng) async {
  final response = await http.get(
    Uri.parse('$baseUrl/api/stores/nearest?latitude=$lat&longitude=$lng'),
  );
  return jsonDecode(response.body);
}

// 2. Get products for a store
Future<List<dynamic>> getStoreProducts(int branchId) async {
  final response = await http.get(
    Uri.parse('$baseUrl/api/stores/$branchId/products'),
  );
  final data = jsonDecode(response.body);
  return data['data'];
}

// 3. Place an order
Future<Map<String, dynamic>> placeOrder(Map<String, dynamic> orderData) async {
  final response = await http.post(
    Uri.parse('$baseUrl/api/customer/orders'),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode(orderData),
  );
  return jsonDecode(response.body);
}

// 4. Get customer orders
Future<List<dynamic>> getCustomerOrders(String phone) async {
  final response = await http.get(
    Uri.parse('$baseUrl/api/customer/orders?phone=$phone'),
  );
  final data = jsonDecode(response.body);
  return data['data'];
}
```

## Website Integration

### Customer Home Page
- URL: `/` (root)
- Shows featured products
- Lists all stores
- "Find Nearest Store" button
- Links to shop at stores

### Store Products Page
- URL: `/store/{branch_id}/products`
- Shows products available at specific store
- Can be used for ordering

## Order Flow

1. **Customer visits home page** (`/`)
2. **Selects store** (nearest or manual selection)
3. **Views products** for that store
4. **Adds items to cart**
5. **Places order** via API
6. **Receives order confirmation**

## API Endpoints Summary

| Endpoint | Method | Auth | Description |
|----------|--------|------|-------------|
| `/api/stores` | GET | No | Get all stores |
| `/api/stores/nearest` | GET | No | Find nearest stores |
| `/api/stores/{id}` | GET | No | Get store details |
| `/api/customer/orders` | POST | No | Create order |
| `/api/customer/orders` | GET | No | Get customer orders |
| `/api/customer/orders/{id}` | GET | No | Get order details |

## Location Services

### GPS Location
- Uses browser's Geolocation API
- Calculates distance using Haversine formula
- Shows nearest store automatically

### Manual Store Selection
- Customer can browse all stores
- Filter by city
- Select store manually

## Order Status

Orders go through these statuses:
- `pending` - Order placed, awaiting confirmation
- `confirmed` - Order confirmed by store
- `processing` - Order being prepared
- `ready` - Order ready for delivery
- `delivered` - Order delivered
- `cancelled` - Order cancelled

## Payment Methods

Supported payment methods:
- `cash` - Cash on delivery
- `upi` - UPI payment
- `card` - Card payment
- `cod` - Cash on delivery (same as cash)

## Stock Management

- System checks stock availability before order creation
- Stock is automatically decremented when order is placed
- Returns error if insufficient stock

## Customer Management

- Customers are auto-created if they don't exist
- Identified by phone number
- Can link orders to existing customer account

## Files Created/Modified

### Controllers
- `app/Http/Controllers/Web/CustomerHomeController.php` - Customer home page
- `app/Http/Controllers/Api/CustomerOrderApiController.php` - Customer order API
- `app/Http/Controllers/Api/StoreLocationApiController.php` - Store location API

### Views
- `resources/views/customer/home.blade.php` - Customer home page

### Routes
- Updated `routes/web.php` - Added customer home route
- Updated `routes/api.php` - Added customer order and store location routes

## Testing

### Test Store Location
```bash
curl "http://localhost:8000/api/stores/nearest?latitude=19.0760&longitude=72.8777"
```

### Test Order Creation
```bash
curl -X POST "http://localhost:8000/api/customer/orders" \
  -H "Content-Type: application/json" \
  -d '{
    "branch_id": 1,
    "customer_name": "Test Customer",
    "customer_phone": "1234567890",
    "delivery_address": "123 Test St",
    "delivery_phone": "1234567890",
    "items": [
      {
        "product_id": 1,
        "quantity": 2.5,
        "unit_price": 120.00
      }
    ],
    "payment_method": "cod"
  }'
```

## Next Steps

1. **Mobile App Development**
   - Implement location services
   - Create product browsing interface
   - Build cart and checkout flow
   - Add order tracking

2. **Website Enhancement**
   - Create product detail pages
   - Build shopping cart
   - Add checkout page
   - Implement order tracking

3. **Features to Add**
   - Order tracking with real-time updates
   - Push notifications for order status
   - Customer account management
   - Order history
   - Favorite products
   - Delivery time estimation
