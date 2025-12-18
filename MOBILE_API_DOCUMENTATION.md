# Mobile API Documentation for Flutter Development

This document provides comprehensive API documentation for developing a Flutter mobile application for the Day2Day POS system.

## Table of Contents
1. [Base Configuration](#base-configuration)
2. [Authentication](#authentication)
3. [API Endpoints](#api-endpoints)
4. [Response Formats](#response-formats)
5. [Error Handling](#error-handling)
6. [Code Examples](#code-examples)

---

## Base Configuration

### Base URL
```
Development: http://localhost:8000/api
Production: https://your-domain.com/api
```

### Headers
All API requests must include:
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}  // For protected routes
```

---

## Authentication

### 1. Login
**Endpoint:** `POST /api/v1/login`

**Request Body:**
```json
{
  "email": "cashier@foodcompany.com",
  "password": "cashier123"
}
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Cashier",
      "email": "cashier@foodcompany.com",
      "role": "Cashier",
      "role_name": "cashier",
      "branch_id": 1
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "permissions": []
  }
}
```

**Error Response (422):**
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password field is required."]
  }
}
```

### 2. Get Profile
**Endpoint:** `GET /api/profile`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "name": "John Cashier",
    "email": "cashier@foodcompany.com",
    "role": "Cashier",
    "branch": {
      "id": 1,
      "name": "Main Branch",
      "code": "FDC001"
    }
  }
}
```

### 3. Logout
**Endpoint:** `POST /api/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Logged out successfully"
}
```

---

## API Endpoints

### Store Location (Public - No Authentication Required)

#### Find Nearest Stores
**Endpoint:** `GET /api/stores/nearest`

**Query Parameters:**
- `latitude` (required): User's latitude
- `longitude` (required): User's longitude
- `radius` (optional): Search radius in kilometers (default: 10km)

**Response:**
```json
{
  "success": true,
  "data": {
    "nearest_store": {
      "id": 1,
      "name": "Main Branch",
      "code": "FDC001",
      "address": "123 Main St",
      "phone": "1234567890",
      "latitude": 19.0760,
      "longitude": 72.8777,
      "distance": 2.5,
      "city": {
        "id": 1,
        "name": "Mumbai"
      }
    },
    "nearby_stores": [...],
    "all_stores": [...]
  }
}
```

#### Get All Stores
**Endpoint:** `GET /api/stores`

**Query Parameters:**
- `city_id` (optional): Filter by city ID

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Main Branch",
      "code": "FDC001",
      "address": "123 Main St",
      "phone": "1234567890",
      "latitude": 19.0760,
      "longitude": 72.8777,
      "city": {
        "id": 1,
        "name": "Mumbai"
      }
    },
    ...
  ]
}
```

#### Get Store by ID
**Endpoint:** `GET /api/stores/{id}`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Main Branch",
    "code": "FDC001",
    "address": "123 Main St",
    "phone": "1234567890",
    "latitude": 19.0760,
    "longitude": 72.8777,
    "city": {
      "id": 1,
      "name": "Mumbai"
    },
    "operating_hours": {...}
  }
}
```

### Customer Orders (Public - No Authentication Required)

#### Create Customer Order
**Endpoint:** `POST /api/customer/orders`

**Request Body:**
```json
{
  "branch_id": 1,
  "customer_id": 1,
  "customer_name": "John Doe",
  "customer_phone": "1234567890",
  "customer_email": "john@example.com",
  "customer_address": "123 Main St",
  "delivery_address": "456 Delivery St",
  "delivery_phone": "1234567890",
  "items": [
    {
      "product_id": 1,
      "quantity": 2.5,
      "unit_price": 120.00
    }
  ],
  "payment_method": "cod",
  "notes": "Please deliver in the morning",
  "delivery_instructions": "Ring the doorbell"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Order placed successfully",
  "data": {
    "order_id": 1,
    "order_number": "ORD-20240115-ABC123",
    "total_amount": 300.00,
    "status": "pending",
    "payment_status": "pending"
  }
}
```

#### Get Customer Orders
**Endpoint:** `GET /api/customer/orders`

**Query Parameters:**
- `customer_id` (optional): Customer ID
- `phone` (optional): Customer phone number
- `page` (optional): Page number
- `per_page` (optional): Items per page

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "order_number": "ORD-20240115-ABC123",
      "branch": {
        "id": 1,
        "name": "Main Branch",
        "address": "123 Main St"
      },
      "total_amount": 300.00,
      "status": "pending",
      "payment_status": "pending",
      "payment_method": "cod",
      "order_date": "2024-01-15T10:00:00.000000Z",
      "delivery_address": "456 Delivery St"
    },
    ...
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 1
  }
}
```

#### Get Order Details
**Endpoint:** `GET /api/customer/orders/{id}`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "order_number": "ORD-20240115-ABC123",
    "customer": {
      "id": 1,
      "name": "John Doe",
      "phone": "1234567890"
    },
    "branch": {
      "id": 1,
      "name": "Main Branch",
      "address": "123 Main St",
      "phone": "1234567890"
    },
    "items": [
      {
        "id": 1,
        "product": {
          "id": 1,
          "name": "Apple",
          "code": "APP001"
        },
        "quantity": 2.5,
        "unit_price": 120.00,
        "total_price": 300.00
      }
    ],
    "subtotal": 300.00,
    "total_amount": 300.00,
    "status": "pending",
    "payment_method": "cod",
    "delivery_address": "456 Delivery St"
  }
}
```

### Dashboard

#### Get Dashboard Data
**Endpoint:** `GET /api/dashboard`

**Query Parameters:**
- `start_date` (optional): Start date for statistics (Y-m-d)
- `end_date` (optional): End date for statistics (Y-m-d)

**Response:**
```json
{
  "success": true,
  "data": {
    "branch": {
      "id": 1,
      "name": "Main Branch",
      "code": "FDC001"
    },
    "today_summary": {
      "orders": {
        "total": 45,
        "completed": 42,
        "pending": 3
      },
      "sales": {
        "total": 125000.50,
        "cash": 80000.00,
        "card": 25000.00,
        "upi": 20000.50
      }
    },
    "active_pos_session": {
      "id": 1,
      "terminal_id": "POS001",
      "total_sales": 125000.50,
      "total_transactions": 42,
      "started_at": "2024-01-15T08:00:00.000000Z"
    },
    "recent_orders": [...],
    "low_stock_products": [...],
    "top_products": [...]
  }
}
```

#### Get Sales Chart Data
**Endpoint:** `GET /api/dashboard/sales-chart`

**Query Parameters:**
- `period` (optional): `week`, `month`, or `year` (default: `week`)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "date": "2024-01-15",
      "label": "Mon",
      "sales": 15000.50
    },
    ...
  ]
}
```

---

### Products

#### Get Products List
**Endpoint:** `GET /api/products`

**Query Parameters:**
- `search` (optional): Search by name or code
- `category` (optional): Filter by category
- `branch_id` (optional): Filter by branch
- `page` (optional): Page number
- `per_page` (optional): Items per page (default: 20)

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Apple",
      "code": "APP001",
      "category": "fruit",
      "weight_unit": "kg",
      "selling_price": 120.00,
      "current_stock": 50.5,
      "is_active": true,
      "is_sold_out": false
    },
    ...
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100
  }
}
```

#### Get Product by ID
**Endpoint:** `GET /api/products/{id}`

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "name": "Apple",
    "code": "APP001",
    "description": "Fresh red apples",
    "category": "fruit",
    "weight_unit": "kg",
    "selling_price": 120.00,
    "current_stock": 50.5,
    "stock_threshold": 10.0,
    "is_active": true
  }
}
```

#### Search Products
**Endpoint:** `GET /api/products/search`

**Query Parameters:**
- `query` (required): Search term (min 2 characters)
- `category` (optional): Filter by category
- `branch_id` (optional): Filter by branch

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Apple",
      "code": "APP001",
      "category": "fruit",
      "selling_price": 120.00,
      "current_stock": 50.5
    },
    ...
  ]
}
```

---

### POS System

#### Start POS Session
**Endpoint:** `POST /api/pos/start-session`

**Request Body:**
```json
{
  "branch_id": 1,
  "terminal_id": "POS001",
  "opening_cash": 5000.00
}
```

**Response:**
```json
{
  "success": true,
  "message": "POS session started successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "branch_id": 1,
    "terminal_id": "POS001",
    "opening_cash": 5000.00,
    "status": "active",
    "started_at": "2024-01-15T08:00:00.000000Z"
  }
}
```

#### Get Current POS Session
**Endpoint:** `GET /api/pos/current-session`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "terminal_id": "POS001",
    "opening_cash": 5000.00,
    "total_sales": 125000.50,
    "total_transactions": 42,
    "status": "active",
    "started_at": "2024-01-15T08:00:00.000000Z"
  }
}
```

#### Get POS Products
**Endpoint:** `GET /api/pos/products`

**Query Parameters:**
- `search` (optional): Search term
- `category` (optional): Filter by category

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Apple",
      "code": "APP001",
      "category": "fruit",
      "selling_price": 120.00,
      "current_stock": 50.5,
      "weight_unit": "kg",
      "city_price": 120.00,
      "is_available_in_city": true
    },
    ...
  ]
}
```

#### Process Sale
**Endpoint:** `POST /api/pos/process-sale`

**Request Body:**
```json
{
  "customer_id": 1,
  "items": [
    {
      "product_id": 1,
      "quantity": 2.5,
      "price": 120.00,
      "actual_weight": 2.5,
      "billed_weight": 2.5
    }
  ],
  "payment_method": "cash",
  "discount_amount": 0,
  "amount_received": 300.00,
  "cash_denominations": {
    "denomination_500": 0,
    "denomination_200": 1,
    "denomination_100": 1
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Sale processed successfully",
  "data": {
    "order": {
      "id": 1,
      "order_number": "POS-20240115120000-1",
      "total_amount": 300.00,
      "payment_method": "cash",
      "status": "completed"
    },
    "session": {
      "total_sales": 125300.50,
      "total_transactions": 43
    },
    "payment": {
      "id": 1,
      "amount": 300.00,
      "payment_method": "cash"
    },
    "invoice_url": "https://your-domain.com/orders/1/invoice"
  }
}
```

#### Close POS Session
**Endpoint:** `POST /api/pos/close-session`

**Request Body:**
```json
{
  "closing_cash": 130000.00,
  "notes": "Session closed successfully",
  "cash_breakdown": {
    "denomination_2000": 50,
    "denomination_500": 20,
    "denomination_200": 10,
    "denomination_100": 5
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "POS session closed successfully",
  "data": {
    "id": 1,
    "status": "closed",
    "closing_cash": 130000.00,
    "ended_at": "2024-01-15T20:00:00.000000Z"
  }
}
```

---

### Orders

#### Get Orders List
**Endpoint:** `GET /api/orders`

**Query Parameters:**
- `status` (optional): Filter by status
- `order_type` (optional): Filter by order type
- `payment_status` (optional): Filter by payment status
- `branch_id` (optional): Filter by branch
- `start_date` (optional): Start date filter
- `end_date` (optional): End date filter
- `page` (optional): Page number

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "order_number": "ORD-20240115-ABC123",
      "customer": {
        "id": 1,
        "name": "John Doe",
        "phone": "1234567890"
      },
      "total_amount": 500.00,
      "status": "completed",
      "payment_method": "cash",
      "payment_status": "paid",
      "order_date": "2024-01-15T10:00:00.000000Z"
    },
    ...
  ]
}
```

#### Get Order by ID
**Endpoint:** `GET /api/orders/{id}`

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "order_number": "ORD-20240115-ABC123",
    "customer": {
      "id": 1,
      "name": "John Doe",
      "phone": "1234567890"
    },
    "order_items": [
      {
        "id": 1,
        "product": {
          "id": 1,
          "name": "Apple",
          "code": "APP001"
        },
        "quantity": 2.5,
        "unit_price": 120.00,
        "total_price": 300.00
      }
    ],
    "subtotal": 500.00,
    "discount_amount": 0,
    "tax_amount": 0,
    "total_amount": 500.00,
    "status": "completed",
    "payment_method": "cash"
  }
}
```

#### Create Order
**Endpoint:** `POST /api/orders`

**Request Body:**
```json
{
  "customer_id": 1,
  "order_type": "on_shop",
  "payment_method": "cash",
  "items": [
    {
      "product_id": 1,
      "quantity": 2.5,
      "unit_price": 120.00,
      "actual_weight": 2.5,
      "billed_weight": 2.5
    }
  ],
  "discount_amount": 0,
  "notes": "Customer order"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Order created successfully",
  "data": {
    "id": 1,
    "order_number": "ORD-20240115-ABC123",
    "total_amount": 300.00,
    "status": "pending"
  }
}
```

---

### Customers

#### Get Customers List
**Endpoint:** `GET /api/customers`

**Query Parameters:**
- `search` (optional): Search by name, email, or phone
- `type` (optional): Filter by type (retail/wholesale)
- `customer_type` (optional): Filter by customer type
- `is_active` (optional): Filter by active status
- `page` (optional): Page number
- `per_page` (optional): Items per page

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "phone": "1234567890",
      "email": "john@example.com",
      "address": "123 Main St",
      "type": "retail",
      "customer_type": "regular",
      "credit_limit": 5000.00,
      "credit_days": 30,
      "credit_balance": 0.00,
      "is_active": true,
      "total_orders": 10
    },
    ...
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100
  }
}
```

#### Get Customer by ID
**Endpoint:** `GET /api/customers/{id}`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "phone": "1234567890",
    "email": "john@example.com",
    "address": "123 Main St",
    "type": "retail",
    "customer_type": "regular",
    "credit_limit": 5000.00,
    "credit_days": 30,
    "credit_balance": 0.00,
    "is_active": true,
    "total_orders": 10,
    "total_spent": 5000.00
  }
}
```

#### Create Customer
**Endpoint:** `POST /api/customers`

**Request Body:**
```json
{
  "name": "John Doe",
  "phone": "1234567890",
  "email": "john@example.com",
  "address": "123 Main St",
  "customer_type": "regular",
  "credit_limit": 5000.00,
  "credit_days": 30,
  "is_active": true
}
```

**Response:**
```json
{
  "success": true,
  "message": "Customer created successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "phone": "1234567890",
    ...
  }
}
```

#### Search Customers
**Endpoint:** `GET /api/customers/search`

**Query Parameters:**
- `query` (required): Search term (min 1 character)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "phone": "1234567890",
      "email": "john@example.com"
    },
    ...
  ]
}
```

#### Get Customer Purchase History
**Endpoint:** `GET /api/customers/{id}/purchase-history`

**Query Parameters:**
- `start_date` (optional): Start date filter
- `end_date` (optional): End date filter
- `page` (optional): Page number

**Response:**
```json
{
  "success": true,
  "data": {
    "customer": {
      "id": 1,
      "name": "John Doe",
      ...
    },
    "orders": [
      {
        "id": 1,
        "order_number": "ORD-20240115-ABC123",
        "total_amount": 500.00,
        "status": "completed",
        "order_date": "2024-01-15T10:00:00.000000Z"
      },
      ...
    ],
    "summary": {
      "total_orders": 10,
      "total_spent": 5000.00,
      "average_order_value": 500.00
    }
  }
}
```

---

### Branch

#### Get Current Branch
**Endpoint:** `GET /api/branch/current`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Main Branch",
    "code": "FDC001",
    "address": "123 Main St",
    "phone": "1234567890",
    "email": "branch@example.com",
    "city": {
      "id": 1,
      "name": "Mumbai"
    },
    "pos_enabled": true,
    "is_active": true,
    "opening_hours": "08:00",
    "closing_hours": "20:00"
  }
}
```

#### Get Branch Statistics
**Endpoint:** `GET /api/branch/statistics`

**Query Parameters:**
- `start_date` (optional): Start date for statistics
- `end_date` (optional): End date for statistics

**Response:**
```json
{
  "success": true,
  "data": {
    "branch": {
      "id": 1,
      "name": "Main Branch",
      "code": "FDC001"
    },
    "today": {
      "total_orders": 45,
      "total_sales": 125000.50,
      "pending_orders": 3,
      "completed_orders": 42
    },
    "date_range": {
      "total_orders": 500,
      "total_sales": 1500000.00,
      "cash_sales": 1000000.00,
      "card_sales": 300000.00,
      "upi_sales": 200000.00
    },
    "inventory": {
      "total_products": 100,
      "low_stock_products": 5,
      "out_of_stock_products": 2
    }
  }
}
```

---

## Response Formats

### Success Response
All successful responses follow this format:
```json
{
  "success": true,
  "data": { ... },
  "message": "Optional success message"
}
```

### Error Response
All error responses follow this format:
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": ["Error message for field"]
  }
}
```

### Pagination
Paginated responses include a `meta` object:
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

---

## Error Handling

### HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

### Common Error Scenarios

#### Authentication Error (401)
```json
{
  "status": "error",
  "message": "Unauthenticated"
}
```

#### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

#### Not Found Error (404)
```json
{
  "success": false,
  "message": "Resource not found"
}
```

---

## Code Examples

### Flutter/Dart Example

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class ApiService {
  final String baseUrl = 'http://localhost:8000/api';
  String? token;

  // Login
  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/v1/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      token = data['data']['token'];
      return data;
    } else {
      throw Exception('Login failed');
    }
  }

  // Get Products
  Future<List<dynamic>> getProducts({String? search}) async {
    final uri = Uri.parse('$baseUrl/products').replace(
      queryParameters: search != null ? {'search': search} : null,
    );

    final response = await http.get(
      uri,
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return data['data'];
    } else {
      throw Exception('Failed to load products');
    }
  }

  // Process Sale
  Future<Map<String, dynamic>> processSale(Map<String, dynamic> saleData) async {
    final response = await http.post(
      Uri.parse('$baseUrl/pos/process-sale'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode(saleData),
    );

    if (response.statusCode == 200 || response.statusCode == 201) {
      return jsonDecode(response.body);
    } else {
      final error = jsonDecode(response.body);
      throw Exception(error['message'] ?? 'Failed to process sale');
    }
  }
}
```

### Usage Example

```dart
void main() async {
  final api = ApiService();
  
  // Login
  try {
    final loginResponse = await api.login('cashier@foodcompany.com', 'cashier123');
    print('Login successful: ${loginResponse['data']['user']['name']}');
  } catch (e) {
    print('Login error: $e');
  }
  
  // Get Products
  try {
    final products = await api.getProducts(search: 'apple');
    print('Found ${products.length} products');
  } catch (e) {
    print('Error loading products: $e');
  }
  
  // Process Sale
  try {
    final saleData = {
      'items': [
        {
          'product_id': 1,
          'quantity': 2.5,
          'price': 120.00,
          'actual_weight': 2.5,
          'billed_weight': 2.5,
        }
      ],
      'payment_method': 'cash',
      'amount_received': 300.00,
    };
    
    final result = await api.processSale(saleData);
    print('Sale processed: ${result['data']['order']['order_number']}');
  } catch (e) {
    print('Error processing sale: $e');
  }
}
```

---

## Testing Credentials

For testing purposes, use these credentials:

### Admin
- Email: `admin@foodcompany.com`
- Password: `admin123`

### Cashier
- Email: `cashier@foodcompany.com`
- Password: `cashier123`

### Branch Manager
- Email: `manager@foodcompany.com`
- Password: `manager123`

---

## Notes for Flutter Developers

1. **Token Storage**: Store the authentication token securely using `flutter_secure_storage` or similar packages.

2. **Error Handling**: Always check response status codes and handle errors appropriately.

3. **Pagination**: When fetching lists, implement pagination using the `meta` object in responses.

4. **Offline Support**: Consider implementing offline caching for products and customers.

5. **Image Handling**: Product images (if any) will be provided as URLs in the response.

6. **Date Formats**: All dates are in ISO 8601 format (e.g., `2024-01-15T10:00:00.000000Z`).

7. **Decimal Precision**: All monetary values are returned as floats with 2 decimal precision.

8. **Real-time Updates**: For real-time updates, consider implementing WebSocket connections or polling mechanisms.

---

## Support

For API support or questions, please contact the development team or refer to the main API documentation.
