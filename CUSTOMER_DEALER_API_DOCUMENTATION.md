# Customer/Dealer Authentication API Documentation

This document provides comprehensive documentation for the Customer and Dealer authentication API endpoints. Customers and Dealers can log in using their mobile number and password.

## Base URL

- **Local Development**: `http://127.0.0.1:8000/api`
- **Production**: `https://yourdomain.com/api`

## Authentication

All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

---

## Public Endpoints (No Authentication Required)

### 1. Customer/Dealer Login

Login using mobile number and password.

**Endpoint**: `POST /api/customer/login`

**Request Body**:
```json
{
  "mobile": "9876543210",
  "password": "password123"
}
```

**Validation Rules**:
- `mobile`: Required, must be exactly 10 digits
- `password`: Required, minimum 6 characters

**Success Response** (200 OK):
```json
{
  "status": "success",
  "message": "Login successful",
  "data": {
    "customer": {
      "id": 1,
      "name": "John Doe",
      "phone": "9876543210",
      "email": "john@example.com",
      "address": "123 Main Street",
      "customer_type": "distributor",
      "customer_type_display": "Distributor",
      "is_dealer": true,
      "credit_limit": 50000.00,
      "credit_days": 30,
      "credit_balance": 15000.00,
      "remaining_credit_limit": 35000.00
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz1234567890"
  }
}
```

**Error Responses**:

- **422 Validation Error**:
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "mobile": ["Mobile number must be 10 digits"],
    "password": ["Password must be at least 6 characters"]
  }
}
```

- **401 Invalid Credentials**:
```json
{
  "status": "error",
  "message": "Invalid mobile number or account not found"
}
```

- **401 Account Not Activated**:
```json
{
  "status": "error",
  "message": "Account not activated. Please set your password first."
}
```

- **401 Invalid Password**:
```json
{
  "status": "error",
  "message": "Invalid password"
}
```

---

### 2. Customer/Dealer Registration

Register a new customer or dealer account.

**Endpoint**: `POST /api/customer/register`

**Request Body**:
```json
{
  "name": "John Doe",
  "mobile": "9876543210",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "address": "123 Main Street",
  "customer_type": "distributor"
}
```

**Validation Rules**:
- `name`: Required, string, max 255 characters
- `mobile`: Required, must be exactly 10 digits, unique
- `email`: Optional, must be valid email format, unique if provided
- `password`: Required, minimum 6 characters, must match password_confirmation
- `password_confirmation`: Required when password is provided
- `address`: Optional, string, max 1000 characters
- `customer_type`: Optional, must be one of: `walk_in`, `regular`, `regular_wholesale`, `premium_wholesale`, `distributor`, `retailer`

**Success Response** (201 Created):
```json
{
  "status": "success",
  "message": "Registration successful",
  "data": {
    "customer": {
      "id": 1,
      "name": "John Doe",
      "phone": "9876543210",
      "email": "john@example.com",
      "address": "123 Main Street",
      "customer_type": "distributor",
      "customer_type_display": "Distributor",
      "is_dealer": true
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz1234567890"
  }
}
```

**Error Responses**:

- **422 Validation Error**:
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "mobile": ["This mobile number is already registered"],
    "email": ["This email is already registered"],
    "password": ["Password confirmation does not match"]
  }
}
```

---

### 3. Request Password Reset

Request a password reset OTP via SMS.

**Endpoint**: `POST /api/customer/password-reset/request`

**Request Body**:
```json
{
  "mobile": "9876543210"
}
```

**Validation Rules**:
- `mobile`: Required, must be exactly 10 digits

**Success Response** (200 OK):
```json
{
  "status": "success",
  "message": "Password reset OTP sent to your mobile number",
  "data": {
    "otp_sent": true
  }
}
```

**Note**: For security reasons, the actual OTP is not returned in the response. It will be sent via SMS to the registered mobile number.

---

### 4. Reset Password with OTP

Reset password using the OTP received via SMS.

**Endpoint**: `POST /api/customer/password-reset/verify`

**Request Body**:
```json
{
  "mobile": "9876543210",
  "otp": "123456",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}
```

**Validation Rules**:
- `mobile`: Required, must be exactly 10 digits
- `otp`: Required, must be exactly 6 digits
- `new_password`: Required, minimum 6 characters, must match new_password_confirmation
- `new_password_confirmation`: Required when new_password is provided

**Success Response** (200 OK):
```json
{
  "status": "success",
  "message": "Password reset successfully"
}
```

**Error Responses**:

- **422 Validation Error**:
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "otp": ["OTP must be exactly 6 digits"],
    "new_password": ["Password confirmation does not match"]
  }
}
```

- **404 Not Found**:
```json
{
  "status": "error",
  "message": "Invalid mobile number"
}
```

---

## Protected Endpoints (Authentication Required)

All protected endpoints require the `Authorization: Bearer {token}` header.

### 5. Get Customer/Dealer Profile

Get the authenticated customer's profile information.

**Endpoint**: `GET /api/customer/profile`

**Headers**:
```
Authorization: Bearer {token}
```

**Success Response** (200 OK):
```json
{
  "status": "success",
  "data": {
    "customer": {
      "id": 1,
      "name": "John Doe",
      "phone": "9876543210",
      "email": "john@example.com",
      "address": "123 Main Street",
      "customer_type": "distributor",
      "customer_type_display": "Distributor",
      "is_dealer": true,
      "credit_limit": 50000.00,
      "credit_days": 30,
      "credit_balance": 15000.00,
      "remaining_credit_limit": 35000.00,
      "total_purchase_amount": 250000.00
    }
  }
}
```

**Error Response** (401 Unauthorized):
```json
{
  "message": "Unauthenticated."
}
```

---

### 6. Change Password

Change the authenticated customer's password.

**Endpoint**: `POST /api/customer/change-password`

**Headers**:
```
Authorization: Bearer {token}
```

**Request Body**:
```json
{
  "current_password": "oldpassword123",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}
```

**Validation Rules**:
- `current_password`: Required
- `new_password`: Required, minimum 6 characters, must match new_password_confirmation
- `new_password_confirmation`: Required when new_password is provided

**Success Response** (200 OK):
```json
{
  "status": "success",
  "message": "Password changed successfully"
}
```

**Error Responses**:

- **400 Bad Request** (Incorrect Current Password):
```json
{
  "status": "error",
  "message": "Current password is incorrect"
}
```

- **422 Validation Error**:
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "new_password": ["Password must be at least 6 characters"]
  }
}
```

---

### 7. Logout

Logout the authenticated customer and invalidate the token.

**Endpoint**: `POST /api/customer/logout`

**Headers**:
```
Authorization: Bearer {token}
```

**Success Response** (200 OK):
```json
{
  "status": "success",
  "message": "Logged out successfully"
}
```

---

## Customer Types

The following customer types are supported:

- `walk_in`: Walk-in Customer
- `regular`: Regular Customer
- `regular_wholesale`: Regular Wholesale
- `premium_wholesale`: Premium Wholesale
- `distributor`: Distributor (Dealer)
- `retailer`: Retailer (Dealer)

**Note**: Customers with type `distributor` or `retailer` are considered dealers (`is_dealer: true`).

---

## Example Usage

### cURL Examples

#### 1. Login
```bash
curl -X POST http://127.0.0.1:8000/api/customer/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "mobile": "9876543210",
    "password": "password123"
  }'
```

#### 2. Register
```bash
curl -X POST http://127.0.0.1:8000/api/customer/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "mobile": "9876543210",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "customer_type": "distributor"
  }'
```

#### 3. Get Profile
```bash
curl -X GET http://127.0.0.1:8000/api/customer/profile \
  -H "Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890" \
  -H "Accept: application/json"
```

#### 4. Change Password
```bash
curl -X POST http://127.0.0.1:8000/api/customer/change-password \
  -H "Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "current_password": "oldpassword123",
    "new_password": "newpassword123",
    "new_password_confirmation": "newpassword123"
  }'
```

#### 5. Logout
```bash
curl -X POST http://127.0.0.1:8000/api/customer/logout \
  -H "Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890" \
  -H "Accept: application/json"
```

---

## JavaScript/Fetch Examples

### Login
```javascript
const response = await fetch('http://127.0.0.1:8000/api/customer/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    mobile: '9876543210',
    password: 'password123'
  })
});

const data = await response.json();
if (data.status === 'success') {
  const token = data.data.token;
  // Store token for future requests
  localStorage.setItem('customer_token', token);
}
```

### Get Profile
```javascript
const token = localStorage.getItem('customer_token');
const response = await fetch('http://127.0.0.1:8000/api/customer/profile', {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
});

const data = await response.json();
console.log(data.data.customer);
```

---

## Error Handling

All endpoints return consistent error responses:

1. **Validation Errors (422)**: When request validation fails
2. **Unauthorized (401)**: When authentication fails or token is invalid
3. **Not Found (404)**: When resource is not found
4. **Server Error (500)**: When an unexpected error occurs

Always check the `status` field in the response:
- `"success"`: Request was successful
- `"error"`: Request failed, check `message` and `errors` fields

---

## Security Notes

1. **Password Requirements**: Minimum 6 characters (can be enhanced)
2. **Mobile Number Format**: Must be exactly 10 digits (Indian format)
3. **Token Expiration**: Tokens do not expire by default (can be configured)
4. **HTTPS**: Always use HTTPS in production
5. **OTP Implementation**: OTP verification is a placeholder - implement proper SMS gateway integration

---

## Database Migration

Before using these endpoints, run the migration to add password fields:

```bash
php artisan migrate
```

This will add the following fields to the `customers` table:
- `password` (nullable)
- `last_login_at` (nullable timestamp)
- `last_login_ip` (nullable string)
- `remember_token` (nullable string)

---

## Support

For issues or questions, please contact the development team or refer to the main API documentation.


