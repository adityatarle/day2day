# Local Purchase Feature Implementation Guide

## Overview

The Local Purchase feature has been successfully implemented for the Day2Day Fruits and Vegetables Laravel project. This feature allows branch managers to record local purchases from vendors when ordering from the main branch is not feasible due to distance or urgency.

## What Has Been Implemented

### 1. Database Structure

#### New Tables Created:
- **local_purchases** - Main table for local purchase records
- **local_purchase_items** - Items within each local purchase
- **local_purchase_notifications** - Notification tracking for admins

#### Migration Files:
- `/database/migrations/2025_09_19_100000_create_local_purchases_table.php`
- `/database/migrations/2025_09_19_100001_add_local_purchase_to_stock_movements_enum.php`

### 2. Models and Relationships

#### New Models:
- `app/Models/LocalPurchase.php` - Main model with auto-generated purchase numbers
- `app/Models/LocalPurchaseItem.php` - Purchase item model with automatic calculations
- `app/Models/LocalPurchaseNotification.php` - Notification model

#### Updated Models:
- `User.php` - Added relationships for local purchases
- `Branch.php` - Added local purchases relationship
- `Vendor.php` - Added local purchases relationship
- `Product.php` - Added local purchase items relationship

### 3. Controller

- `app/Http/Controllers/LocalPurchaseController.php` - Comprehensive controller handling:
  - CRUD operations for local purchases
  - Approval/rejection workflow
  - Stock synchronization
  - Expense record creation
  - Export functionality (CSV/PDF)
  - Notification management

### 4. Routes

#### Branch Manager Routes:
```php
/branch/local-purchases - List local purchases
/branch/local-purchases/create - Create new local purchase
/branch/local-purchases/{id} - View local purchase
/branch/local-purchases/{id}/edit - Edit pending purchase
/branch/local-purchases-export - Export data
```

#### Admin Routes:
```php
/admin/local-purchases - List all local purchases
/admin/local-purchases/{id} - View local purchase
/admin/local-purchases/{id}/approve - Approve purchase
/admin/local-purchases/{id}/reject - Reject purchase
/admin/local-purchases-export - Export data
```

### 5. Views

#### Created Views:
- `resources/views/local-purchases/index.blade.php` - List view with filters
- `resources/views/local-purchases/create.blade.php` - Create form with dynamic items
- `resources/views/local-purchases/show.blade.php` - Detailed view with timeline
- `resources/views/local-purchases/edit.blade.php` - Edit form for pending purchases
- `resources/views/local-purchases/export-pdf.blade.php` - PDF export template

### 6. Navigation Updates

- Added "Local Purchases" menu item to Branch Manager dashboard
- Added "Local Purchases" menu item to Admin/Super Admin dashboard

### 7. Email Notifications

#### Email Components:
- `app/Mail/LocalPurchaseNotification.php` - Mailable class
- `app/Jobs/SendLocalPurchaseNotificationEmail.php` - Background job for sending emails
- `resources/views/emails/local-purchase-notification.blade.php` - Email template

## Key Features Implemented

### 1. Local Purchase Management

- **Create Purchase**: Branch managers can create local purchases with:
  - Multiple items with quantity, unit price, tax, and discount
  - Existing or new vendor information
  - Payment method and reference
  - Receipt/invoice upload
  - Link to pending purchase orders

- **Auto-calculations**: System automatically calculates:
  - Item subtotals, tax amounts, and discount amounts
  - Purchase subtotal, total tax, total discount, and grand total

- **Purchase Number Generation**: Automatic generation in format: `LP-BRX-YYYYMM-XXXX`

### 2. Approval Workflow

- **Status Flow**: Draft → Pending → Approved/Rejected → Completed
- **Permissions**:
  - Branch managers can create, edit, and delete their pending purchases
  - Admins can approve or reject with mandatory reason for rejection
  - Status changes are tracked with timestamps and user information

### 3. Inventory Integration

- **Stock Updates**: Upon approval, the system:
  - Creates stock movement records with type 'local_purchase'
  - Updates branch product stock levels
  - Updates linked purchase order items if applicable
  - Marks purchase orders as partially/completely fulfilled

### 4. Financial Tracking

- **Expense Creation**: Approved purchases automatically:
  - Create expense records under "Local Purchase" category
  - Link to the branch and manager
  - Track in financial reports

### 5. Notifications

- **In-app Notifications**: Created for:
  - Admins when new purchase is created
  - Managers when purchase is approved/rejected
  - Real-time notification badge updates

- **Email Notifications**: Automated emails sent for:
  - New purchase creation (to admins)
  - Approval/rejection (to manager)
  - Beautiful HTML email templates with purchase details

### 6. Reporting & Export

- **Filtering**: By branch, status, vendor, date range
- **Export Options**:
  - CSV export with all purchase data
  - PDF export with summary statistics
  - Maintains filter parameters during export

### 7. Audit Trail

- **Comprehensive Logging**:
  - Creation timestamp and user
  - Approval/rejection timestamp and user
  - All updates tracked
  - Visual timeline in purchase details view

## How to Use

### For Branch Managers:

1. Navigate to "Local Purchases" in the sidebar
2. Click "Create Local Purchase"
3. Fill in the form:
   - Select purchase date
   - Choose existing vendor or enter new vendor details
   - Add items with quantities and prices
   - Select payment method
   - Upload receipt (optional)
   - Add notes (optional)
4. Submit for approval
5. Track status and receive notifications

### For Admins:

1. Navigate to "Local Purchases" in the sidebar
2. Review pending purchases
3. Click on a purchase to view details
4. Approve or reject with reason
5. Monitor branch spending through filters and exports

## Installation Steps

1. **Run Migrations**:
   ```bash
   php artisan migrate
   ```

2. **Clear Caches**:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   ```

3. **Set Up Queue Worker** (for email notifications):
   ```bash
   php artisan queue:work
   ```

4. **Configure Email** (in .env file):
   ```
   MAIL_MAILER=smtp
   MAIL_HOST=your-smtp-host
   MAIL_PORT=587
   MAIL_USERNAME=your-username
   MAIL_PASSWORD=your-password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=noreply@day2day.com
   MAIL_FROM_NAME="Day2Day System"
   ```

## Security Considerations

- Role-based access control enforced at controller level
- Branch managers can only see/edit their branch's purchases
- Pending purchases can only be edited by the creator
- File uploads restricted to images and PDFs (5MB max)
- All monetary calculations done server-side
- CSRF protection on all forms

## Future Enhancements

1. **Barcode Scanning**: Integrate barcode scanner for product selection
2. **Vendor Portal**: Allow vendors to submit invoices directly
3. **Budget Limits**: Set monthly local purchase limits per branch
4. **Mobile App**: Create mobile interface for on-the-go purchases
5. **Analytics Dashboard**: Advanced analytics for local purchase trends
6. **Integration**: Connect with accounting software
7. **Recurring Purchases**: Template system for frequent purchases

## Troubleshooting

### Common Issues:

1. **"Class not found" errors**:
   - Run `composer dump-autoload`

2. **Email not sending**:
   - Check queue worker is running
   - Verify email configuration in .env
   - Check Laravel logs

3. **Stock not updating**:
   - Ensure purchase is approved
   - Check stock_movements table
   - Verify product exists in branch

4. **Permission errors**:
   - Verify user role assignments
   - Clear cache: `php artisan cache:clear`

## Support

For any issues or questions regarding the Local Purchase feature, please contact the development team or refer to the system documentation.