## API User Guide (Detailed)

This guide explains how to call every API endpoint in this Laravel application, how authentication works (Laravel Sanctum bearer tokens), how to configure Postman, and how to troubleshoot common issues like HTTP 405 Method Not Allowed.

### 1) Base URL and Headers
- For local development: `http://localhost:8000`
- All API routes are prefixed with `/api` (for example, `POST /api/login`).
- Always send these headers for JSON APIs:
  - `Accept: application/json`
  - `Content-Type: application/json`

### 2) Authentication Flow (Sanctum)

2.1) Login (issue bearer token)
```http
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "secret"
}
```

Successful response:
```json
{
  "status": "success",
  "message": "Login successful",
  "data": {
    "user": { "id": 1, "name": "Admin", "email": "admin@example.com", "role": "Admin", "role_name": "admin" },
    "token": "<bearer_token_here>",
    "permissions": ["..."]
  }
}
```

2.2) Use the token
- Send `Authorization: Bearer <token>` on every protected request.

2.3) Logout
```http
POST /api/logout
Authorization: Bearer <token>
```

2.4) Profile (current user)
```http
GET /api/profile
Authorization: Bearer <token>
```

2.5) Change password
```http
POST /api/change-password
Authorization: Bearer <token>
Content-Type: application/json

{
  "current_password": "old",
  "new_password": "new_password_here",
  "new_password_confirmation": "new_password_here"
}
```

### 3) Outlet Authentication (Outlet-specific login)

3.1) Login to an outlet
```http
POST /api/outlet/login
Content-Type: application/json

{
  "email": "staff@outlet.com",
  "password": "password",
  "outlet_code": "BR-001"
}
```

3.2) Get outlet info (public)
```http
GET /api/outlet/{outletCode}/info
```

3.3) Outlet logout
```http
POST /api/outlet/logout
Authorization: Bearer <token>
```

3.4) Outlet change password
```http
POST /api/outlet/change-password
Authorization: Bearer <token>
Content-Type: application/json

{
  "current_password": "old",
  "new_password": "new_password_here",
  "new_password_confirmation": "new_password_here"
}
```

### 4) Role Model and Access Control
- Roles are enforced with `role:` middleware alongside `auth:sanctum`.
- Available roles: `super_admin`, `admin`, `branch_manager`, `cashier`, `delivery_boy`.
- Endpoints below are annotated by role grouping (as implemented in `routes/api.php`).

### 5) Endpoint Catalog (as implemented)

5.1) Admin only
- Users
  - `GET /api/users`
  - `POST /api/users`
  - `GET /api/users/{user}`
  - `PUT /api/users/{user}`
  - `DELETE /api/users/{user}`
  - `GET /api/roles`
  - `GET /api/branches`
- Expense Categories
  - `GET /api/expense-categories`
  - `POST /api/expense-categories`
  - `PUT /api/expense-categories/{category}`
  - `DELETE /api/expense-categories/{category}`

5.2) Admin + Branch Manager
- Products (resource)
  - `GET /api/products`
  - `POST /api/products`
  - `GET /api/products/{product}`
  - `PUT /api/products/{product}`
  - `DELETE /api/products/{product}`
- Products (additional)
  - `GET /api/products/categories`
  - `PUT /api/products/categories/bulk`
  - `GET /api/products/category/{category}`
  - `GET /api/products/{product}/stock-info`
  - `POST /api/products/{product}/branch-pricing`
  - `PUT /api/products/{product}/branch-pricing`
  - `PUT /api/products/{product}/vendor-pricing`
  - `GET /api/products/search`  ← See routing note in section 8
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
- Outlet Management
  - `GET /api/outlets`
  - `POST /api/outlets`
  - `GET /api/outlets/{outlet}`
  - `PUT /api/outlets/{outlet}`
  - `DELETE /api/outlets/{outlet}`
  - `GET /api/cities/{city}/outlets`
  - `POST /api/outlets/{outlet}/staff`
  - `GET /api/outlets/{outlet}/performance`

5.3) Admin + Branch Manager + Cashier
- Orders (resource)
  - `GET /api/orders`
  - `POST /api/orders`
  - `GET /api/orders/{order}`
  - `PUT /api/orders/{order}`
  - `DELETE /api/orders/{order}`
  - `POST /api/orders/{order}/cancel`
  - `GET /api/orders/{order}/invoice`
  - `GET /api/orders/statistics`
- POS
  - `POST /api/pos/start-session`
  - `GET /api/pos/current-session`
  - `POST /api/pos/process-sale`
  - `POST /api/pos/close-session`
  - `GET /api/pos/products`
  - `GET /api/pos/session-history`
  - `GET /api/pos/session-summary`

5.4) Expenses (Admin + Branch Manager)
- `GET /api/expenses`
- `POST /api/expenses`
- `GET /api/expenses/{expense}`
- `PUT /api/expenses/{expense}`
- `DELETE /api/expenses/{expense}`
- `PUT /api/expenses/{expense}/approve`
- `PUT /api/expenses/{expense}/reject`
- `PUT /api/expenses/{expense}/mark-paid`
- `GET /api/expenses/allocation/report`
- `GET /api/expenses/cost/analysis`
- `GET /api/expenses/summary`

5.5) Loss Tracking
- Resource: `loss-tracking` (CRUD)
  - `GET /api/loss-tracking`
  - `POST /api/loss-tracking`
  - `GET /api/loss-tracking/{loss}`
  - `PUT /api/loss-tracking/{loss}`
  - `DELETE /api/loss-tracking/{loss}`
- Analytics and utilities
  - `GET /api/loss-tracking/analytics`
  - `GET /api/loss-tracking/trends`
  - `GET /api/loss-tracking/critical-alerts`
  - `POST /api/loss-tracking/bulk`
  - `GET /api/loss-tracking/prevention-recommendations`
  - `GET /api/loss-tracking/export`

5.6) Wholesale
- Pricing tiers
  - `GET /api/wholesale/pricing-tiers`
  - `POST /api/wholesale/pricing-tiers`
  - `PUT /api/wholesale/pricing-tiers/{pricingTier}`
  - `DELETE /api/wholesale/pricing-tiers/{pricingTier}`
- Orders and pricing
  - `POST /api/wholesale/calculate-pricing`
  - `POST /api/wholesale/orders`
  - `GET /api/wholesale/orders`
  - `GET /api/wholesale/orders/{order}/invoice`
- Analytics
  - `GET /api/wholesale/customer-analysis`
  - `GET /api/wholesale/performance-metrics`

5.7) Billing
- `GET /api/billing/invoice/{order}`
- `POST /api/billing/quick-billing`
- `POST /api/billing/online-payment/{order}`
- `POST /api/billing/bulk-invoice`
- `POST /api/billing/partial-payment/{order}`
- `GET /api/billing/summary`
- `GET /api/billing/pending-payments`

5.8) Delivery (delivery_boy)
- `GET /api/delivery/orders`
- `PUT /api/delivery/orders/{order}/start`
- `PUT /api/delivery/orders/{order}/process`
- `PUT /api/delivery/orders/{order}/location`
- `POST /api/delivery/orders/{order}/quick-return`
- `GET /api/delivery/history`
- `GET /api/delivery/stats`
- `GET /api/delivery/optimized-route`

5.9) City Management (Admin)
- `GET /api/cities`
- `POST /api/cities`
- `GET /api/cities/{city}`
- `PUT /api/cities/{city}`
- `DELETE /api/cities/{city}`
- `POST /api/cities/{city}/product-pricing`
- `GET /api/cities/{city}/product-pricing`

5.10) System Monitoring (prefixed `/api/monitoring`)
- Super Admin
  - `GET /api/monitoring/system-status`
  - `GET /api/monitoring/branch-performance`
- Super Admin + Branch Manager
  - `GET /api/monitoring/branch-status`
  - `GET /api/monitoring/user-activity`
- Super Admin + Branch Manager + Cashier
  - `GET /api/monitoring/pos-status`
  - `GET /api/monitoring/sales-data`
  - `GET /api/monitoring/inventory-alerts`

### 6) Postman Setup (Step-by-step)

6.1) Create an environment
- Variables: `baseUrl = http://localhost:8000`, `token = <empty>`

6.2) Create a Login request
- Method: POST
- URL: `{{baseUrl}}/api/login`
- Headers: `Accept: application/json`, `Content-Type: application/json`
- Body (raw JSON): `{ "email": "admin@example.com", "password": "secret" }`
- Tests (save token automatically):
```javascript
const json = pm.response.json();
if (json && json.data && json.data.token) {
  pm.environment.set('token', json.data.token);
}
```

6.3) Authorize subsequent requests
- Add header to a Postman Collection (or per-request):
  - `Authorization: Bearer {{token}}`
  - `Accept: application/json`

6.4) Example protected request
- Method: GET
- URL: `{{baseUrl}}/api/profile`
- Headers: `Authorization: Bearer {{token}}`, `Accept: application/json`

6.5) Outlet login request
- Method: POST
- URL: `{{baseUrl}}/api/outlet/login`
- Body: `{ "email": "cashier@example.com", "password": "secret", "outlet_code": "BR-001" }`

### 7) cURL Quickstart

7.1) Login and capture token
```bash
TOKEN=$(curl -s -X POST "http://localhost:8000/api/login" \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@example.com","password":"secret"}' | jq -r '.data.token')
```

7.2) Call a protected endpoint
```bash
curl -i "http://localhost:8000/api/profile" \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Accept: application/json'
```

7.3) Outlet login
```bash
curl -i -X POST "http://localhost:8000/api/outlet/login" \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"email":"cashier@example.com","password":"secret","outlet_code":"BR-001"}'
```

### 8) Routing Notes and Known Caveats
- `GET /api/products/search` is defined alongside `Route::apiResource('products', ...)`. In Laravel, if `products/search` appears after the resource registration, it can be shadowed by `GET /api/products/{product}` (treating `search` as a product ID). If you get a 404 or unexpected response from `/api/products/search`, use a query on the index instead (e.g., `GET /api/products?search=...`) or move the `products/search` route above the resource in code.
- There are both `POST` and `PUT` variants for `/api/products/{product}/branch-pricing`. Follow controller docs for which one your workflow uses.

### 9) Error Responses (Standardized)
- Validation: HTTP 422
  - Shape: `{ status|success, message, errors }`
- Unauthorized: HTTP 401
- Forbidden: HTTP 403
- Not found: HTTP 404
- Server error: HTTP 500

### 10) Troubleshooting HTTP 405 Method Not Allowed
If you see 405 in Postman, check these first:
1. Verify HTTP method matches the route (e.g., `POST /api/login`, not GET).
2. Ensure you are calling the `/api/...` path (not the web routes like `/login`).
3. Add headers: `Accept: application/json` and for body requests `Content-Type: application/json`.
4. Watch for redirects (301/302). If your server redirects `http`→`https`, call the final `https://...` URL directly. Redirects can turn POST into GET and cause 405.
5. Reverse proxies/firewalls may block `PUT`, `PATCH`, or `DELETE`. Allow these methods on `/api/*`.
6. In browser apps, CORS preflight (`OPTIONS`) might be rejected. Configure CORS for your API. Postman is not subject to CORS, so a 405 there is almost always a method/URL mismatch or redirect.
7. Confirm the route is registered: run `php artisan route:list` and verify method/path/middleware.

### 11) Running the API locally
```bash
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve --host 0.0.0.0 --port 8000
```

### 12) Security Notes
- Treat bearer tokens like passwords; store them securely.
- Revoke tokens on logout. Tokens are per-device/session.
- Role checks (e.g., `role:admin`) restrict access beyond authentication.

This guide reflects the actual routes in `routes/api.php` and provides practical steps to authenticate and call each group of endpoints reliably.