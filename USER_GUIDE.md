# 🍎🥬 Food Company Management System - User Guide

## 📋 Table of Contents
1. [Getting Started](#getting-started)
2. [User Roles and Access](#user-roles-and-access)
3. [Inventory Management](#inventory-management)
4. [Loss Tracking](#loss-tracking)
5. [Product and Pricing Management](#product-and-pricing-management)
6. [Sales and Billing](#sales-and-billing)
7. [Delivery Management](#delivery-management)
8. [Wholesale Operations](#wholesale-operations)
9. [Expense Management](#expense-management)
10. [Reports and Analytics](#reports-and-analytics)
11. [Mobile App Usage](#mobile-app-usage)
12. [Best Practices](#best-practices)

## 🚀 Getting Started

### First Time Login
1. Open your browser and go to the system URL
2. Use your provided credentials to login
3. You'll be redirected to the dashboard based on your role

### Dashboard Overview
The dashboard shows:
- **Today's Sales Summary**
- **Low Stock Alerts**
- **Recent Orders**
- **Expense Summary**
- **Quick Action Buttons**

## 👥 User Roles and Access

### 🔑 Admin (Owner/Manager)
**Full system access including:**
- User management and role assignment
- Branch management and configuration
- System-wide reports and analytics
- All module access

### 🏢 Branch Manager
**Branch-specific operations:**
- Product management for their branch
- Inventory management and stock control
- Customer and vendor management
- Branch-specific reports
- Expense approval and management

### 💰 Cashier (On-Shop Sales)
**Sales operations:**
- Create and process orders
- Handle payments (cash, UPI, card)
- Generate invoices and receipts
- Manage customer information
- Process returns and adjustments

### 🚚 Delivery Boy
**Delivery operations:**
- View assigned delivery orders
- Update delivery status and location
- Process customer returns during delivery
- Handle quantity adjustments
- Record delivery completion

## 📦 Inventory Management

### 🔄 Auto Stock Updates

#### How It Works
- **After every sale**: Stock automatically reduces
- **Threshold check**: When stock ≤ threshold → product goes "Sold Out" online
- **Batch tracking**: FIFO (First In, First Out) system
- **Multi-branch**: Independent stock per branch

#### Managing Stock

##### Add New Stock
1. Go to **Inventory** → **Add Stock**
2. Select **Product** and **Branch**
3. Enter **Quantity** and **Purchase Price**
4. Set **Batch Number** (auto-generated if empty)
5. Set **Expiry Date** (if applicable)
6. Click **Add Stock**

```
Example:
Product: Fresh Apples
Branch: Main Store
Quantity: 50 kg
Purchase Price: ₹80/kg
Batch: BATCH-20240115-ABC123
Expiry: 2024-01-25
```

##### Check Stock Levels
1. Go to **Inventory** → **Stock Status**
2. Filter by **Branch**, **Category**, or **Stock Status**
3. View current stock, threshold, and online availability

##### Stock Alerts
The system automatically shows:
- 🔴 **Out of Stock**: 0 quantity
- 🟡 **Low Stock**: Below threshold
- 🟠 **Expiring Soon**: Batches expiring within 7 days

##### Transfer Stock Between Branches
1. Go to **Inventory** → **Transfer Stock**
2. Select **Product** and **From Branch**
3. Select **To Branch** and **Quantity**
4. Add **Reason** and click **Transfer**

### 📊 Inventory Valuation
- View **Stock Value** with purchase price
- See **Cost Per Unit** including allocated expenses
- Check **Profit Margins** with true costs
- Export valuation reports

## 📉 Loss Tracking

### Types of Losses

#### 1. 🏋️ Weight Loss
**When to use**: Storage-related weight reduction
**Example**: 1kg apples → 950g after 3 days storage

**How to record**:
1. Go to **Inventory** → **Record Loss** → **Weight Loss**
2. Select **Product** and **Branch**
3. Enter **Initial Weight**: 1.0 kg
4. Enter **Current Weight**: 0.95 kg
5. Add **Reason**: "Storage moisture loss"
6. Click **Record Loss**

#### 2. 💧 Water Loss
**When to use**: Moisture loss in vegetables
**Example**: Leafy vegetables losing water content

**How to record**:
1. Go to **Inventory** → **Record Loss** → **Water Loss**
2. Select **Product** and **Branch**
3. Enter **Quantity Lost**: 0.2 kg
4. Add **Reason**: "Moisture evaporation"
5. Click **Record Loss**

#### 3. 🗑️ Wastage Loss
**When to use**: Damaged, spoiled, or expired items
**Example**: Rotten tomatoes that can't be sold

**How to record**:
1. Go to **Inventory** → **Record Loss** → **Wastage**
2. Select **Product** and **Branch**
3. Enter **Quantity Lost**: 2.5 kg
4. Add **Reason**: "Overripe and damaged"
5. Click **Record Loss**

#### 4. 🎁 Complimentary Loss
**When to use**: Customer gets more than billed amount
**Example**: Customer orders 520g, billed for 500g, gets 20g free

**Automatic tracking**: This happens automatically during sales when:
- **Actual Weight** > **Billed Weight**
- System records the difference as complimentary loss

### 📈 Loss Analytics

#### View Loss Reports
1. Go to **Reports** → **Loss Analytics**
2. Select **Date Range** and **Branch** (optional)
3. View breakdown by:
   - **Loss Type** (weight, water, wastage, complimentary)
   - **Product Category**
   - **Branch Performance**
   - **Time Trends**

#### Loss Prevention Recommendations
The system automatically suggests:
- Products with high wastage rates
- Items with frequent weight loss
- Products needing better storage conditions
- Pricing adjustments for frequent complimentary items

## 🏷️ Product and Pricing Management

### 📂 Product Categories

#### Available Categories
- **🍎 Fruits**: Citrus, Tropical, Seasonal, Berries, Stone Fruits
- **🥕 Vegetables**: Root, Gourd, Pod, Bulb, Stem
- **🥬 Leafy**: Greens, Herbs, Salads
- **🥥 Exotic**: Imported, Specialty, Rare
- **🌿 Herbs**: Fresh, Dried, Medicinal
- **🥜 Dry Fruits**: Nuts, Dried Fruits, Seeds
- **🌱 Organic**: Certified, Natural, Pesticide Free

#### Managing Products

##### Add New Product
1. Go to **Products** → **Add Product**
2. Fill in basic information:
   - **Name**: Fresh Red Apples
   - **Code**: APPLE-RED (unique identifier)
   - **Category**: Fruit
   - **Subcategory**: Seasonal
3. Set pricing:
   - **Purchase Price**: ₹80/kg
   - **MRP**: ₹120/kg
   - **Selling Price**: ₹100/kg
4. Configure storage:
   - **Shelf Life**: 10 days
   - **Storage Temperature**: 2-8°C
   - **Is Perishable**: Yes
5. Set **Stock Threshold**: 5 kg
6. Click **Save Product**

##### Branch-Specific Pricing
1. Go to **Products** → **Manage Pricing**
2. Select a **Product**
3. Set different prices for each branch:
   - **Main Store**: ₹100/kg
   - **Mall Outlet**: ₹110/kg (premium location)
4. Enable/disable **Online Availability** per branch
5. Click **Update Pricing**

##### Vendor Management
1. Go to **Products** → **Vendor Pricing**
2. Select a **Product**
3. Add vendor pricing:
   - **Fresh Farm Suppliers**: ₹75/kg (Primary)
   - **Wholesale Market**: ₹78/kg (Secondary)
4. Click **Update Vendor Pricing**

### 🏪 Branch Management

#### Configure Branch Settings
1. Go to **Branches** → **Branch Settings**
2. Update branch information:
   - **Name**: Main Store
   - **Address**: Complete address
   - **Contact**: Phone and email
   - **Manager**: Assigned manager
3. Set branch-specific settings:
   - **Default markup percentage**
   - **Stock threshold multiplier**
   - **Online availability rules**

## 💰 Sales and Billing

### 🛒 On-Shop Sales (Quick Billing)

#### Process Walk-in Customer Sale
1. **Login as Cashier** or **Branch Manager**
2. Go to **Sales** → **Quick Billing**
3. Add customer info (optional):
   - **Name**: John Doe
   - **Phone**: 9876543210
4. Add products to cart:
   - Search and select **Product**
   - Enter **Quantity**: 2.5 kg
   - System shows **Unit Price** and **Total**
   - If actual weight differs, enter **Actual Weight**: 2.6 kg
5. Apply **Discount** if needed: ₹10
6. Select **Payment Method**: Cash/UPI/Card
7. Click **Complete Sale**
8. **Invoice generated automatically**

#### Handle Customer Returns
1. Go to **Sales** → **Returns**
2. Enter **Order Number** or search by customer
3. Select items to return:
   - **Product**: Fresh Apples
   - **Return Quantity**: 0.5 kg
   - **Reason**: Customer not satisfied
4. Process **Refund**:
   - **Refund Amount**: ₹50
   - **Refund Method**: Cash
5. **Stock automatically added back**

### 🌐 Online Sales Management

#### Process Online Orders
1. **Orders come from website/app** automatically
2. Go to **Orders** → **Online Orders**
3. View order details:
   - **Customer Information**
   - **Delivery Address**
   - **Payment Status**
4. **Confirm Order** (stock reduces automatically)
5. **Assign Delivery Boy**
6. **Track Delivery Status**

#### Handle Online Payments
1. Go to **Orders** → **Payment Processing**
2. For **COD Orders**:
   - Mark as **Payment Received** when delivered
3. For **Failed Payments**:
   - Contact customer for retry
   - Or cancel order (stock restored automatically)

## 🚚 Delivery Management

### 📱 For Delivery Boys (Mobile App)

#### View Assigned Deliveries
1. **Login to mobile app**
2. See **Today's Deliveries** list
3. View order details:
   - **Customer Info** and **Address**
   - **Items Ordered** with quantities
   - **Payment Method** (COD/Prepaid)
   - **Special Instructions**

#### Start Delivery
1. Select **Order** from list
2. Click **Start Delivery**
3. **GPS tracking begins automatically**
4. System shows **Optimized Route** to customer

#### Handle Customer Adjustments
**Scenario**: Customer wants to return some items or change quantities

1. At customer location, open **Order Details**
2. Click **Customer Adjustments**
3. For each item:
   - **Return**: Customer doesn't want item
     - Select **Product**: Fresh Apples
     - Enter **Return Quantity**: 0.5 kg
     - Add **Reason**: "Customer changed mind"
   - **Reduce Quantity**: Customer wants less
     - Select **Product**: Tomatoes
     - **New Quantity**: 1.5 kg (was 2.0 kg)
     - Add **Reason**: "Customer needs less"
4. Click **Process Adjustments**
5. **New invoice generated automatically**
6. **Refund calculated** and processed
7. **Stock updated** in real-time

#### Complete Delivery
1. Get **Customer Signature** (if required)
2. Click **Mark as Delivered**
3. Add **Delivery Notes**
4. **GPS location recorded** automatically

### 🏢 For Branch Managers

#### Assign Delivery Boys
1. Go to **Deliveries** → **Assign Deliveries**
2. Select **Orders** for delivery
3. Choose **Delivery Boy** from list
4. Set **Expected Delivery Time**
5. Click **Assign**

#### Track Deliveries
1. Go to **Deliveries** → **Live Tracking**
2. View **Real-time GPS locations** of delivery boys
3. See **Delivery Status** updates
4. Monitor **Customer Adjustments** and **Returns**

## 🏭 Wholesale Operations

### 💼 Wholesale Customer Management

#### Add Wholesale Customer
1. Go to **Customers** → **Add Customer**
2. Fill customer details:
   - **Name**: Fresh Mart Wholesale
   - **Customer Type**: Regular Wholesale
   - **Credit Limit**: ₹50,000
   - **Credit Days**: 15 days
   - **Contact Information**
3. Click **Save Customer**

#### Set Wholesale Pricing
1. Go to **Wholesale** → **Pricing Tiers**
2. Select **Product**: Fresh Apples
3. Add pricing tiers:
   - **10-49 kg**: ₹95/kg (5% discount)
   - **50-99 kg**: ₹90/kg (10% discount)
   - **100+ kg**: ₹85/kg (15% discount)
4. Set **Customer Type**: Distributor
5. Click **Save Pricing**

### 🛒 Process Wholesale Orders

#### Calculate Wholesale Pricing
1. Go to **Wholesale** → **New Order**
2. Select **Customer**: Fresh Mart Wholesale
3. Add products:
   - **Product**: Fresh Apples
   - **Quantity**: 75 kg
   - System shows:
     - **Regular Price**: ₹7,500 (75 × ₹100)
     - **Wholesale Price**: ₹6,750 (75 × ₹90)
     - **Savings**: ₹750 (10% discount)
4. Review **Total Savings** and **Final Amount**

#### Create Wholesale Order
1. After price calculation, click **Create Order**
2. Set **Payment Terms**:
   - **Payment Method**: Credit
   - **Credit Days**: 15 days
   - **Payment Terms**: Net 15
3. Add **Notes** if needed
4. Click **Confirm Order**
5. **Stock reduces automatically**
6. **Invoice generated** with wholesale pricing

#### Manage Credit Customers
1. Go to **Customers** → **Credit Management**
2. View customer credit status:
   - **Credit Limit**: ₹50,000
   - **Used Credit**: ₹25,000
   - **Available Credit**: ₹25,000
   - **Overdue Amount**: ₹0
3. Process **Credit Payments** when received
4. **Extend Credit Limits** if needed

## 💸 Expense Management

### 📝 Record Business Expenses

#### Transport Expenses
1. Go to **Expenses** → **Add Expense**
2. Select **Category**: Transport
3. Fill details:
   - **Title**: Daily CNG Refill
   - **Amount**: ₹500
   - **Expense Type**: Transport
   - **Payment Method**: Cash
4. **Allocate to Products**:
   - **Allocation Method**: Equal (splits among all products)
   - Or **Weighted** (based on sales volume)
   - Or **Manual** (specify per product)
5. Click **Save Expense**

#### Labour Expenses
1. **Category**: Labour
2. **Examples**:
   - Loading/Unloading charges: ₹800
   - Daily helper wages: ₹600
   - Overtime charges: ₹300
3. **Allocation**: Usually weighted by product volume

#### Operational Expenses
1. **Category**: Operational
2. **Examples**:
   - Electricity bill: ₹2,000
   - Rent: ₹15,000
   - Maintenance: ₹1,200
3. **Allocation**: Usually equal across all products

### 💡 Cost Allocation Impact

#### View True Product Costs
1. Go to **Products** → **Cost Analysis**
2. Select **Product**: Fresh Apples
3. View cost breakdown:
   - **Purchase Price**: ₹80/kg
   - **Allocated Expenses**: ₹5/kg
   - **True Cost**: ₹85/kg
   - **Selling Price**: ₹100/kg
   - **True Profit**: ₹15/kg (15%)

#### Pricing Decisions
- Use **True Cost** for pricing decisions
- Monitor **Profit Margins** with allocated costs
- Adjust **Selling Prices** based on cost analysis

## 📱 Mobile App Usage

### 📲 Delivery Boy App Functions

#### Daily Workflow
1. **Login** to mobile app
2. **View Today's Deliveries**
3. **Optimize Route** (system suggests best order)
4. **Start First Delivery**

#### At Customer Location
1. **Verify Items** with customer
2. **Handle Adjustments** if needed:
   - Customer wants to **return** some items
   - Customer wants **different quantity**
   - Customer **rejects** some items
3. **Process Payment** (for COD orders)
4. **Get Signature** and **Complete Delivery**

#### Handle Returns During Delivery
**Scenario**: Customer ordered 2kg tomatoes, only wants 1.5kg

1. Open **Order** in app
2. Click **Customer Adjustments**
3. Select **Tomatoes**
4. Choose **Reduce Quantity**
5. Enter **New Quantity**: 1.5 kg
6. Add **Reason**: "Customer needs less"
7. App calculates **Refund**: ₹20 (0.5 kg × ₹40)
8. **Process Refund** in cash
9. **New invoice** generated automatically
10. **Stock updated** in real-time

### 📊 Manager Mobile Functions

#### Stock Monitoring
1. **View Stock Alerts** on mobile
2. **Record Losses** on the go
3. **Check Inventory** across branches
4. **Approve Expenses** remotely

#### Quick Actions
- **Add Stock** when receiving goods
- **Record Wastage** during quality checks
- **Transfer Stock** between branches
- **View Sales Reports** in real-time

## 📊 Reports and Analytics

### 📈 Inventory Reports

#### Stock Valuation Report
1. Go to **Reports** → **Inventory Valuation**
2. Select **Branch** (or all branches)
3. View:
   - **Stock Value** at selling price
   - **Cost Value** including allocated expenses
   - **Profit Margin** analysis
   - **Category-wise** breakdown

#### Stock Movement Report
1. Go to **Reports** → **Stock Movements**
2. Filter by **Date Range**, **Product**, or **Branch**
3. View all stock changes:
   - **Sales** (outgoing)
   - **Purchases** (incoming)
   - **Transfers** (between branches)
   - **Losses** (wastage, weight loss, etc.)

### 📉 Loss Analysis Reports

#### Loss Summary
1. Go to **Reports** → **Loss Analytics**
2. Select **Time Period**
3. View:
   - **Total Financial Loss**: ₹15,430
   - **Loss by Type**: Weight (40%), Wastage (35%), Water (25%)
   - **Worst Performing Products**
   - **Branch Comparison**

#### Loss Trends
1. View **Daily/Weekly/Monthly** loss trends
2. Identify **Seasonal Patterns**
3. Compare **Branch Performance**
4. Get **Prevention Recommendations**

### 💰 Financial Reports

#### Profit Analysis with True Costs
1. Go to **Reports** → **Profit Analysis**
2. View product profitability:
   - **Revenue**: ₹50,000
   - **Purchase Cost**: ₹30,000
   - **Allocated Expenses**: ₹5,000
   - **True Profit**: ₹15,000 (30%)

#### Expense Allocation Report
1. Go to **Reports** → **Expense Allocation**
2. See how expenses are distributed:
   - **Transport**: ₹2,000 → allocated to all products
   - **Labour**: ₹1,500 → weighted by volume
   - **Impact on Cost**: ₹3.50/kg average increase

### 🏭 Wholesale Reports

#### Customer Analysis
1. Go to **Reports** → **Wholesale Analysis**
2. View customer performance:
   - **Top Customers** by revenue
   - **Average Order Value**
   - **Credit Utilization**
   - **Purchase Patterns**

#### Wholesale Performance
1. Track **Wholesale vs Retail** sales
2. Monitor **Discount Impact**
3. Analyze **Customer Loyalty**
4. **Pricing Optimization** suggestions

## 🎯 Best Practices

### 📦 Inventory Management
1. **Set appropriate thresholds**:
   - **Perishables**: Higher thresholds (7-10 kg)
   - **Non-perishables**: Lower thresholds (2-5 kg)
2. **Regular stock audits**:
   - Weekly physical stock verification
   - Compare with system stock
   - Record discrepancies as losses
3. **Batch management**:
   - Always use **FIFO** (First In, First Out)
   - Monitor **expiry dates** closely
   - Process **expired batches** daily

### 📉 Loss Control
1. **Daily loss recording**:
   - Record losses as they occur
   - Don't batch record at end of day
   - Take photos for reference
2. **Prevention measures**:
   - Monitor **high-loss products**
   - Improve **storage conditions**
   - Train staff on **proper handling**
3. **Regular analysis**:
   - Weekly loss review meetings
   - Monthly trend analysis
   - Quarterly prevention strategy updates

### 💰 Pricing Strategy
1. **Regular cost review**:
   - Monthly cost analysis with allocated expenses
   - Adjust prices based on **true costs**
   - Monitor **competitor pricing**
2. **Wholesale optimization**:
   - Review **pricing tiers** quarterly
   - Analyze **customer purchase patterns**
   - Adjust **discount percentages** based on volume

### 🚚 Delivery Optimization
1. **Route planning**:
   - Use **system route optimization**
   - Group deliveries by **area**
   - Consider **traffic patterns**
2. **Customer adjustments**:
   - Train delivery boys on **adjustment process**
   - Set **adjustment limits**
   - Monitor **frequent adjustment customers**

## 🔧 System Maintenance

### 📅 Daily Tasks
1. **Review stock alerts** (morning)
2. **Process expired batches** (automated or manual)
3. **Check delivery status** (throughout day)
4. **Record any losses** (as they occur)

### 📅 Weekly Tasks
1. **Stock audit** and reconciliation
2. **Loss analysis** review
3. **Expense allocation** review
4. **Customer credit** follow-up

### 📅 Monthly Tasks
1. **Profit margin analysis** with true costs
2. **Wholesale pricing** review
3. **Vendor pricing** negotiation
4. **System performance** review

## 🆘 Common Scenarios

### Scenario 1: Customer Orders 520g, Gets Billed for 500g
**What happens automatically**:
1. Cashier enters **Actual Weight**: 520g
2. Cashier enters **Billed Weight**: 500g
3. System calculates **Complimentary**: 20g
4. **Stock reduces by 520g** (actual weight)
5. **Customer pays for 500g** only
6. **20g recorded as complimentary loss**
7. **Financial impact tracked**: 20g × selling price

### Scenario 2: Delivery Boy Needs to Return Items
**Customer rejects 1kg apples during delivery**:
1. Delivery boy opens **Order** in mobile app
2. Clicks **Customer Adjustments**
3. Selects **Apples** → **Return** → **1kg**
4. Adds **Reason**: "Customer rejected due to quality"
5. App calculates **Refund**: ₹100
6. **Processes cash refund**
7. **New invoice** generated automatically
8. **Stock added back** to branch inventory
9. **Order total updated**: Original ₹500 → New ₹400

### Scenario 3: Stock Goes Below Threshold
**Apple stock drops to 4kg (threshold: 5kg)**:
1. **System automatically**:
   - Marks apples as **"Sold Out" online**
   - Sends **low stock alert** to manager
   - **Prevents online orders** for apples
2. **Manager receives notification**
3. **Manager orders new stock** from vendor
4. When **new stock added** → automatically **goes online** again

### Scenario 4: Monthly Expense Allocation
**Monthly transport expense: ₹15,000**:
1. Manager records **Transport Expense**
2. Selects **Allocation Method**: Weighted
3. System allocates based on **sales volume**:
   - **Apples** (40% of sales): ₹6,000
   - **Tomatoes** (35% of sales): ₹5,250
   - **Onions** (25% of sales): ₹3,750
4. **Cost per unit** updated for each product
5. **True profit margins** recalculated
6. **Pricing decisions** made based on true costs

## 🎓 Training Recommendations

### For Cashiers
1. **Quick billing process** (30 minutes)
2. **Handling returns** and adjustments (20 minutes)
3. **Payment processing** (15 minutes)
4. **Customer service** best practices (30 minutes)

### For Delivery Boys
1. **Mobile app usage** (45 minutes)
2. **Customer adjustment process** (30 minutes)
3. **GPS tracking** and route optimization (15 minutes)
4. **Professional delivery** practices (30 minutes)

### For Branch Managers
1. **Complete system overview** (2 hours)
2. **Inventory management** (1 hour)
3. **Loss tracking and prevention** (1 hour)
4. **Expense management** and allocation (45 minutes)
5. **Reports and analytics** (45 minutes)

### For Admins
1. **System administration** (3 hours)
2. **User and role management** (1 hour)
3. **Advanced configuration** (2 hours)
4. **Backup and maintenance** (1 hour)

## 📞 Support and Help

### Getting Help
1. **Check this User Guide** first
2. **Review API Documentation**: `ENHANCED_MODULES_API.md`
3. **Check Setup Guide**: `SETUP_GUIDE.md`
4. **View System Logs**: Go to **System** → **Logs**

### Common Questions

**Q: Why is my product showing as "Sold Out" online?**
A: Stock is below the set threshold. Add more stock or reduce the threshold.

**Q: How do I handle customer complaints about billing?**
A: Use the adjustment feature during delivery or create a return in the system.

**Q: Can I change prices for different branches?**
A: Yes, go to **Products** → **Branch Pricing** to set different prices per branch.

**Q: How do I track which expenses are allocated to which products?**
A: Go to **Reports** → **Expense Allocation** to see detailed allocation reports.

**Q: Can delivery boys process returns without manager approval?**
A: Yes, delivery boys can process returns up to a certain limit (configurable by admin).

## 🎉 Success Tips

1. **Train your team** on the new automated features
2. **Set realistic thresholds** for stock alerts
3. **Review loss reports** weekly to identify patterns
4. **Use expense allocation** for accurate pricing decisions
5. **Monitor wholesale customers** for credit management
6. **Leverage automation** to reduce manual work

Your enhanced food company management system is designed to streamline operations, reduce manual work, and provide accurate business insights. Follow this guide to make the most of all the powerful features! 🚀