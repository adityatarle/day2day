# 🚀 Quick Start Guide - Food Company Management System

## ⚡ 5-Minute Setup

### 1. 🔧 Basic Setup
```bash
# Install dependencies
composer install
npm install && npm run build

# Configure environment
cp .env.example .env
php artisan key:generate

# Setup database (edit .env first with your DB details)
php artisan migrate
php artisan db:seed
```

### 2. 🆕 Enhanced Modules Setup
```bash
# Run enhanced modules setup
./setup_enhanced_modules.sh
```

### 3. 🚀 Start Application
```bash
# Start development server
php artisan serve

# Start queue worker (in another terminal)
php artisan queue:work
```

### 4. 🔑 Default Login Credentials
- **Admin**: admin@foodcompany.com / admin123
- **Manager**: manager@foodcompany.com / manager123  
- **Cashier**: cashier@foodcompany.com / cashier123
- **Delivery Boy**: delivery@foodcompany.com / delivery123

## 📱 Quick Feature Test

### Test Auto Stock Update
1. Login as **Cashier**
2. Go to **Sales** → **Quick Billing**
3. Add a product with quantity
4. Complete sale
5. Check **Inventory** → stock should be reduced automatically

### Test Threshold-Based "Sold Out"
1. Login as **Admin**
2. Go to **Products** → find a product
3. Set **Stock Threshold** to current stock level
4. Make a sale to reduce stock below threshold
5. Check **Online Availability** → should show "Sold Out"

### Test Loss Tracking
1. Go to **Inventory** → **Record Loss**
2. Select **Weight Loss**
3. Enter initial and final weights
4. Save → stock reduces automatically

### Test Wholesale Pricing
1. Login as **Admin**
2. Go to **Wholesale** → **Pricing Tiers**
3. Create pricing for bulk quantities
4. Go to **Wholesale** → **Calculate Pricing**
5. Enter large quantity → see discount applied

## 🎯 Essential First Steps

### 1. Configure Your Business
- [ ] Update **Branch Information**
- [ ] Add your **Products** with categories
- [ ] Set **Stock Thresholds** for each product
- [ ] Configure **Vendor Information**
- [ ] Set up **Expense Categories**

### 2. Set Up Users
- [ ] Create **Branch Managers**
- [ ] Add **Cashiers** for each branch
- [ ] Register **Delivery Boys**
- [ ] Assign **Proper Roles** and **Branch Access**

### 3. Configure Pricing
- [ ] Set **Branch-specific Prices**
- [ ] Create **Wholesale Pricing Tiers**
- [ ] Configure **Vendor Pricing**
- [ ] Set up **Tax Rates** (GST)

### 4. Test Core Workflows
- [ ] Create and process an **On-shop Sale**
- [ ] Create and deliver an **Online Order**
- [ ] Process a **Wholesale Order**
- [ ] Record different types of **Losses**
- [ ] Add and allocate **Expenses**

## 📋 Daily Operations Checklist

### Morning (Start of Day)
- [ ] Check **Stock Alerts**
- [ ] Review **Pending Deliveries**
- [ ] Process any **Expired Batches**
- [ ] Check **Low Stock Items**

### During Operations
- [ ] Record **Losses** as they occur
- [ ] Process **Customer Orders**
- [ ] Handle **Delivery Adjustments**
- [ ] Update **Stock** when receiving goods

### End of Day
- [ ] Review **Daily Sales Report**
- [ ] Check **Delivery Completion Status**
- [ ] Record **Daily Expenses**
- [ ] Verify **Stock Levels**

## 🔍 Quick Troubleshooting

### Stock Not Updating After Sale
**Check**: Is InventoryService properly integrated in OrderController?
**Solution**: Verify order creation includes `$inventoryService->updateStockAfterSale($orderItem)`

### Product Not Going "Sold Out" Online
**Check**: Stock threshold settings
**Solution**: Ensure stock threshold is set correctly and stock is actually below threshold

### Expense Allocation Not Working
**Check**: Allocation method and product selection
**Solution**: Verify allocation_method is not 'none' and products are selected

### Delivery Adjustments Not Saving
**Check**: User role permissions
**Solution**: Ensure user has 'delivery_boy' role and is assigned to the order

## 📞 Need Help?

1. **Setup Issues**: Check `SETUP_GUIDE.md`
2. **Feature Usage**: Check `USER_GUIDE.md`
3. **API Integration**: Check `ENHANCED_MODULES_API.md`
4. **System Logs**: `storage/logs/laravel.log`

## 🎉 You're Ready!

Your enhanced food company management system is now set up with:
- ✅ **Automated stock management**
- ✅ **Comprehensive loss tracking**
- ✅ **Advanced expense allocation**
- ✅ **Wholesale billing system**
- ✅ **Delivery boy adjustments**
- ✅ **Multi-branch operations**

**Happy selling!** 🍎🥬🚀