## API User Guide

This document describes the REST API for this Laravel application. Authentication uses Laravel Sanctum bearer tokens. Role-based middleware controls access to most endpoints.

### Base URL
- For local dev: `http://localhost:8000`
- API prefix: `/api`

### Authentication
- Issue token: `POST /api/login`
  - Body: `{ "email": "user@example.com", "password": "secret" }`
  - Success: `{ status, message, data: { user, token, permissions } }`
- Auth header for subsequent requests: `Authorization: Bearer <token>`
- Logout: `POST /api/logout` (auth required)
- Me: `GET /api/profile` (auth required)
- Change password: `POST /api/change-password` with `{ current_password, new_password, new_password_confirmation }`

#### Outlet authentication
- `POST /api/outlet/login` with `{ email, password, outlet_code }`
- `GET /api/outlet/{outletCode}/info`
- `POST /api/outlet/logout`
- `POST /api/outlet/change-password`

### Roles
Roles are enforced via `role:` middleware on routes. Common roles:
- `super_admin`
- `admin`
- `branch_manager`
- `cashier`
- `delivery_boy`

When this guide states an endpoint is available to roles `[A,B]`, it means the route group is protected by `middleware('role:A,B')` in addition to `auth:sanctum`.

### Common endpoints (all authenticated users)
- `GET /api/dashboard/stats`
- `GET /api/dashboard/recent-orders`
- `GET /api/dashboard/low-stock`
- `GET /api/dashboard/today-sales`

### Admin only
- Users
  - `GET /api/users`
  - `POST /api/users`
  - `GET /api/users/{user}`
  - `PUT /api/users/{user}`
  - `DELETE /api/users/{user}`
  - `GET /api/roles`
  - `GET /api/branches`
- Branches (resource)
  - `GET /api/branches`
  - `POST /api/branches`
  - `GET /api/branches/{branch}`
  - `PUT /api/branches/{branch}`
  - `DELETE /api/branches/{branch}`
- Vendors (resource)
  - `GET /api/vendors` ... CRUD
- Expense categories
  - `GET /api/expense-categories`
  - `POST /api/expense-categories`
  - `PUT /api/expense-categories/{category}`
  - `DELETE /api/expense-categories/{category}`
- GST rates
  - `GET /api/gst-rates`
  - `POST /api/gst-rates`
  - `PUT /api/gst-rates/{gstRate}`
  - `DELETE /api/gst-rates/{gstRate}`

### Admin + Branch Manager
- Products (resource)
  - `GET /api/products`
  - `POST /api/products`
  - `GET /api/products/{product}`
  - `PUT /api/products/{product}`
  - `DELETE /api/products/{product}`
  - `POST /api/products/{product}/branch-pricing`
  - `PUT /api/products/{product}/vendor-pricing`
  - `GET /api/products/{product}/stock-info`
  - `GET /api/products/categories`
  - `PUT /api/products/categories/bulk`
  - `GET /api/products/category/{category}`
  - `GET /api/products/search`
- Inventory
  - `GET /api/inventory`
  - `POST /api/inventory/add-stock`
  - `POST /api/inventory/record-loss`
  - `GET /api/inventory/{product}/batches`
  - `PUT /api/inventory/batches/{batch}/status`
  - `GET /api/inventory/{product}/stock-movements`
  - `GET /api/inventory/loss-summary`
  - `GET /api/inventory/low-stock-alerts`
  - `GET /api/inventory/valuation`
  - `GET /api/inventory/alerts`
  - `POST /api/inventory/weight-loss`
  - `POST /api/inventory/water-loss`
  - `POST /api/inventory/wastage-loss`
  - `POST /api/inventory/transfer`
  - `PUT /api/inventory/thresholds/bulk`
  - `GET /api/inventory/valuation-with-costs`
  - `POST /api/inventory/process-expired-batches`
- Customers (resource)
  - `GET /api/customers` ... CRUD
  - `GET /api/customers/{customer}/purchase-history`
  - `GET /api/customers/{customer}/credit-balance`
- Purchase Orders (resource)
  - `GET /api/purchase-orders` ... CRUD
  - `POST /api/purchase-orders/{purchaseOrder}/receive`
- Reports
  - `GET /api/reports/sales`
  - `GET /api/reports/inventory`
  - `GET /api/reports/customers`
  - `GET /api/reports/vendors`
  - `GET /api/reports/expenses`
  - `GET /api/reports/profit-loss`
- Outlet Management
  - `GET /api/outlets` ... CRUD (Admin, Branch Manager)
  - `GET /api/cities/{city}/outlets`
  - `POST /api/outlets/{outlet}/staff`
  - `GET /api/outlets/{outlet}/performance`
- City Management (Admin only)
  - `GET /api/cities` ... CRUD
  - `POST /api/cities/{city}/product-pricing`
  - `GET /api/cities/{city}/product-pricing`

### Admin + Branch Manager + Cashier
- Orders (resource)
  - `GET /api/orders` ... CRUD
  - `POST /api/orders/{order}/cancel`
  - `GET /api/orders/{order}/invoice`
  - `GET /api/orders/statistics`
- Billing
  - `POST /api/billing/quick-sale`
  - `POST /api/billing/wholesale`
- POS
  - `POST /api/pos/start-session`
  - `GET /api/pos/current-session`
  - `POST /api/pos/process-sale`
  - `POST /api/pos/close-session`
  - `GET /api/pos/products`
  - `GET /api/pos/session-history`
  - `GET /api/pos/session-summary`

### Delivery Boy (+ Admin, Branch Manager)
- Deliveries
  - `GET /api/deliveries/assigned`
  - `PUT /api/deliveries/{delivery}/pickup`
  - `PUT /api/deliveries/{delivery}/in-transit`
  - `PUT /api/deliveries/{delivery}/delivered`
  - `PUT /api/deliveries/{delivery}/returned`
- Returns
  - `POST /api/returns`
  - `PUT /api/returns/{return}/approve`
  - `PUT /api/returns/{return}/reject`
  - `PUT /api/returns/{return}/process`
- Adjustments
  - `POST /api/adjustments`

### Expenses (Admin + Branch Manager)
- `GET /api/expenses` ... CRUD
- `PUT /api/expenses/{expense}/approve`
- `PUT /api/expenses/{expense}/reject`
- `PUT /api/expenses/{expense}/mark-paid`
- `GET /api/expenses/allocation/report`
- `GET /api/expenses/cost/analysis`
- `GET /api/expenses/summary`

### Loss Tracking
- Resource: `loss-tracking` (CRUD)
- Analytics: `/api/loss-tracking/analytics`, `/api/loss-tracking/trends`, `/api/loss-tracking/critical-alerts`
- Bulk: `POST /api/loss-tracking/bulk`
- Recommendations: `GET /api/loss-tracking/prevention-recommendations`
- Export: `GET /api/loss-tracking/export`

### Wholesale
- Pricing tiers: `GET|POST|PUT|DELETE /api/wholesale/pricing-tiers[/{pricingTier}]`
- Pricing calc: `POST /api/wholesale/calculate-pricing`
- Orders: `POST /api/wholesale/orders`, `GET /api/wholesale/orders`, `GET /api/wholesale/orders/{order}/invoice`
- Analytics: `GET /api/wholesale/customer-analysis`, `GET /api/wholesale/performance-metrics`

### Billing
- `GET /api/billing/invoice/{order}`
- `POST /api/billing/quick-billing`
- `POST /api/billing/online-payment/{order}`
- `POST /api/billing/bulk-invoice`
- `POST /api/billing/partial-payment/{order}`
- `GET /api/billing/summary`
- `GET /api/billing/pending-payments`

### Delivery Boy Adjustment (role: delivery_boy)
- `GET /api/delivery/orders`
- `PUT /api/delivery/orders/{order}/start`
- `PUT /api/delivery/orders/{order}/process`
- `PUT /api/delivery/orders/{order}/location`
- `POST /api/delivery/orders/{order}/quick-return`
- `GET /api/delivery/history`
- `GET /api/delivery/stats`
- `GET /api/delivery/optimized-route`

### Payments and Credit Transactions
- Payments: `GET /api/payments`, `POST /api/payments`, `GET /api/payments/{payment}`
- Credit: `GET /api/credit-transactions`, `POST /api/credit-transactions`, `GET /api/credit-transactions/{transaction}`

### Monitoring (prefixed `/api/monitoring`)
- Super Admin: `GET /api/monitoring/system-status`, `GET /api/monitoring/branch-performance`
- Super Admin + Branch Manager: `GET /api/monitoring/branch-status`, `GET /api/monitoring/user-activity`
- Super Admin + Branch Manager + Cashier: `GET /api/monitoring/pos-status`, `GET /api/monitoring/sales-data`, `GET /api/monitoring/inventory-alerts`

### Example Usage

Authenticate and list products (Branch Manager/Admin):

```bash
curl -sX POST http://localhost:8000/api/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@example.com","password":"secret"}' | jq -r '.data.token'

TOKEN=... # paste token
curl -s http://localhost:8000/api/products \
  -H "Authorization: Bearer $TOKEN"
```

Outlet login and fetch outlet info:

```bash
curl -sX POST http://localhost:8000/api/outlet/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"cashier@example.com","password":"secret","outlet_code":"BR-001"}'

curl -s http://localhost:8000/api/outlet/BR-001/info
```

### Error format
- Validation error: HTTP 422 with `{ status|success, message, errors }`
- Auth error: HTTP 401 with `{ status|success, message }`
- Not found: HTTP 404 with `{ success:false, message }`

### Notes
- All endpoints listed under protected groups require `Authorization: Bearer <token>`.
- Certain endpoints are available only to specified roles as noted above.
- Request/response shapes for resources follow typical Laravel conventions; see controllers for exact payloads.

