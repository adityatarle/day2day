# API Quick Reference Guide

A quick reference guide for the most commonly used API endpoints in the mobile application.

## Base URL
```
http://localhost:8000/api
```

## Authentication

### Login
```http
POST /api/v1/login
Content-Type: application/json

{
  "email": "cashier@foodcompany.com",
  "password": "cashier123"
}
```

### Get Profile
```http
GET /api/profile
Authorization: Bearer {token}
```

### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

---

## Dashboard

### Get Dashboard Data
```http
GET /api/dashboard
Authorization: Bearer {token}
```

### Get Sales Chart
```http
GET /api/dashboard/sales-chart?period=week
Authorization: Bearer {token}
```

---

## Products

### List Products
```http
GET /api/products?search=apple&category=fruit&page=1
Authorization: Bearer {token}
```

### Get Product
```http
GET /api/products/{id}
Authorization: Bearer {token}
```

### Search Products
```http
GET /api/products/search?query=apple
Authorization: Bearer {token}
```

---

## POS System

### Start Session
```http
POST /api/pos/start-session
Authorization: Bearer {token}
Content-Type: application/json

{
  "branch_id": 1,
  "terminal_id": "POS001",
  "opening_cash": 5000.00
}
```

### Get Current Session
```http
GET /api/pos/current-session
Authorization: Bearer {token}
```

### Get POS Products
```http
GET /api/pos/products?search=apple
Authorization: Bearer {token}
```

### Process Sale
```http
POST /api/pos/process-sale
Authorization: Bearer {token}
Content-Type: application/json

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
  "amount_received": 300.00
}
```

### Close Session
```http
POST /api/pos/close-session
Authorization: Bearer {token}
Content-Type: application/json

{
  "closing_cash": 130000.00,
  "notes": "Session closed"
}
```

---

## Orders

### List Orders
```http
GET /api/orders?status=completed&page=1
Authorization: Bearer {token}
```

### Get Order
```http
GET /api/orders/{id}
Authorization: Bearer {token}
```

### Create Order
```http
POST /api/orders
Authorization: Bearer {token}
Content-Type: application/json

{
  "customer_id": 1,
  "order_type": "on_shop",
  "payment_method": "cash",
  "items": [
    {
      "product_id": 1,
      "quantity": 2.5,
      "unit_price": 120.00
    }
  ]
}
```

---

## Customers

### List Customers
```http
GET /api/customers?search=john&page=1
Authorization: Bearer {token}
```

### Get Customer
```http
GET /api/customers/{id}
Authorization: Bearer {token}
```

### Create Customer
```http
POST /api/customers
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "John Doe",
  "phone": "1234567890",
  "email": "john@example.com",
  "customer_type": "regular"
}
```

### Search Customers
```http
GET /api/customers/search?query=john
Authorization: Bearer {token}
```

### Get Purchase History
```http
GET /api/customers/{id}/purchase-history
Authorization: Bearer {token}
```

---

## Branch

### Get Current Branch
```http
GET /api/branch/current
Authorization: Bearer {token}
```

### Get Branch Statistics
```http
GET /api/branch/statistics?start_date=2024-01-01&end_date=2024-01-31
Authorization: Bearer {token}
```

---

## Common Response Formats

### Success
```json
{
  "success": true,
  "data": { ... }
}
```

### Error
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... }
}
```

### Pagination
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

## HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

## Headers Required

All requests must include:
```
Accept: application/json
Content-Type: application/json (for POST/PUT requests)
Authorization: Bearer {token} (for protected routes)
```
