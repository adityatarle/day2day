# 🍽️ FoodCo Login System - Comprehensive Guide

## Overview
Your food company management system now features a modern, role-based login system with three distinct access levels: **Admin**, **Branch Manager**, and **Outlet Staff**. Each role has specific permissions and access to different parts of the system.

---

## 🚀 Quick Start

1. **Navigate to Login Page**: Visit your application URL (e.g., `http://localhost:8000`)
2. **Choose Your Role**: Click on one of the three role cards:
   - 🔴 **Admin Login** - Full system access
   - 🔵 **Branch Manager** - Branch operations
   - 🟢 **Outlet Staff** - POS and daily operations
3. **Enter Credentials**: Fill in the appropriate login form
4. **Access Dashboard**: Get redirected to your role-specific dashboard

---

## 🎭 Login Types & Access Levels

### 👑 Admin Login
**Access Level**: Super Administrator
**Icon**: Crown (👑)
**Color**: Red

**Features:**
- Full system access and control
- User management across all branches
- Branch creation and management
- System settings and configuration
- Security monitoring and logs
- Financial reports and analytics

**Login Process:**
1. Click the **Admin Login** card
2. Enter admin email address
3. Enter admin password
4. Click "Sign In as Admin"
5. Redirected to `/admin/dashboard`

**Default Credentials:**
- **Email**: `admin@foodcompany.com`
- **Password**: `admin123`

---

### 🏢 Branch Manager Login
**Access Level**: Branch Management
**Icon**: Building (🏢)
**Color**: Blue

**Features:**
- Branch-specific operations management
- Staff management for assigned branch
- Inventory control and stock management
- Sales reports and analytics
- Product pricing and availability
- Customer management

**Login Process:**
1. Click the **Branch Manager** card
2. Enter manager email address
3. Enter password
4. Click "Sign In as Branch Manager"
5. Redirected to `/dashboard`

**Sample Credentials:**
- **Email**: `manager@foodcompany.com`
- **Password**: `manager123`

---

### 🏪 Outlet Staff Login
**Access Level**: Point of Sale Operations
**Icon**: Store (🏪)
**Color**: Green

**Features:**
- POS system access
- Daily sales operations
- Order processing and billing
- Customer service
- Basic inventory viewing
- Cash register management

**Login Process:**
1. Click the **Outlet Staff** card
2. Enter **Outlet Code** (e.g., `FDC001`)
3. Enter staff email address
4. Enter password
5. Click "Sign In to Outlet"
6. Redirected to `/pos` (cashiers) or `/dashboard` (managers)

**Sample Credentials:**
- **Outlet Code**: `FDC001`
- **Email**: `cashier@foodcompany.com`
- **Password**: `cashier123`

---

## 🔐 Default Login Credentials

### Admin Users
```
Email: admin@foodcompany.com
Password: admin123
Role: Super Administrator
Access: Full System
```

### Branch Managers
```
Email: manager@foodcompany.com
Password: manager123
Role: Branch Manager
Branch: Main Branch
Access: Branch Operations
```

```
Email: manager2@foodcompany.com
Password: manager123
Role: Branch Manager
Branch: Downtown Branch
Access: Branch Operations
```

### Outlet Staff (Cashiers)
```
Outlet Code: FDC001
Email: cashier@foodcompany.com
Password: cashier123
Role: Cashier
Access: POS System
```

```
Outlet Code: FDC002
Email: cashier2@foodcompany.com
Password: cashier123
Role: Cashier
Access: POS System
```

### Test Users
```
Email: test@foodcompany.com
Password: password123
Role: General User
Access: Basic Dashboard
```

---

## 🛡️ Security Features

### Enhanced Admin Security
- **Role Verification**: Only users with admin role can access admin login
- **Activity Logging**: All admin logins/logouts are logged for security
- **IP Tracking**: Login attempts tracked with IP addresses
- **Account Status Check**: Inactive accounts cannot login

### Branch Manager Security
- **Branch Verification**: Managers can only access their assigned branch
- **Active Branch Check**: Inactive branches block login attempts
- **Role-based Access**: Only branch managers can use branch login

### Outlet Staff Security
- **Outlet Code Validation**: Must provide valid outlet code
- **Staff Assignment Check**: Staff must be assigned to the specific outlet
- **Operating Hours Awareness**: System logs attempts outside business hours
- **POS Session Management**: Automatic session handling for cashiers

---

## 📱 User Interface Features

### Modern Design
- **Responsive Layout**: Works on desktop, tablet, and mobile
- **Interactive Cards**: Hover effects and visual feedback
- **Color-coded Roles**: Each login type has distinct colors
- **Smooth Animations**: Floating logo and smooth transitions

### User Experience
- **Role Selection**: Clear visual distinction between login types
- **Form Validation**: Real-time validation with helpful error messages
- **Remember Me**: Option to stay logged in
- **Back Navigation**: Easy return to role selection

---

## 🔄 Login Flow & Redirects

### Admin Login Flow
```
Login Page → Admin Form → Authentication → Admin Dashboard
                                      ↓
                              Security Logging & Session Setup
```

### Branch Manager Flow
```
Login Page → Branch Form → Authentication → Branch Dashboard
                                        ↓
                              Branch Status Check & Context Setup
```

### Outlet Staff Flow
```
Login Page → Outlet Form → Authentication → POS System (Cashier)
                                        ↓     └→ Dashboard (Manager)
                              Outlet Validation & Session Context
```

---

## 🔧 Troubleshooting

### Common Issues

#### "Invalid credentials" Error
- **Check Role**: Ensure you're using the correct login type for your role
- **Verify Email**: Make sure email address is correct and assigned to the right role
- **Account Status**: Contact admin if your account might be inactive

#### "Invalid outlet code" Error
- **Check Code**: Verify the outlet code is correct (e.g., `FDC001`)
- **Outlet Status**: Ensure the outlet is active and operational
- **Staff Assignment**: Confirm you're assigned to that specific outlet

#### "Branch is inactive" Error
- **Contact Admin**: Your branch may have been temporarily deactivated
- **Check Status**: Admin can verify branch status in the system

#### Login Page Not Loading
- **Clear Cache**: Clear browser cache and cookies
- **Check Connection**: Ensure internet connection is stable
- **Server Status**: Verify the application server is running

### Getting Help

#### For Technical Issues:
- Check browser console for JavaScript errors
- Verify network connectivity
- Try incognito/private browsing mode

#### For Account Issues:
- **Admin Users**: Contact system administrator or IT department
- **Branch Staff**: Contact your branch manager or admin
- **Outlet Staff**: Contact branch manager or admin

#### For System Issues:
- Check Laravel logs in `storage/logs/`
- Verify database connectivity
- Ensure all migrations are up to date

---

## 🎯 Best Practices

### For Administrators
- **Regular Password Updates**: Change admin passwords regularly
- **Monitor Login Logs**: Review security logs for unusual activity
- **User Management**: Keep user accounts updated and remove inactive users
- **Branch Oversight**: Regularly check branch and outlet status

### For Branch Managers
- **Staff Training**: Ensure outlet staff know their login procedures
- **Monitor Access**: Keep track of who has access to your branch systems
- **Regular Updates**: Update staff credentials when necessary

### For Outlet Staff
- **Secure Logout**: Always log out when ending shifts
- **Password Security**: Don't share login credentials
- **Report Issues**: Immediately report any login problems to management
- **POS Sessions**: Properly close POS sessions at end of day

---

## 📊 System Requirements

### Browser Support
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### Device Compatibility
- Desktop computers
- Tablets (iPad, Android tablets)
- Mobile phones (responsive design)

### Network Requirements
- Stable internet connection
- Access to application server
- JavaScript enabled

---

## 🆘 Emergency Procedures

### Lost Admin Access
1. Check with other admin users
2. Access database directly to reset passwords
3. Use Laravel Tinker to create emergency admin user
4. Contact system developer if needed

### Outlet Cannot Access POS
1. Verify outlet code with branch manager
2. Check outlet status in admin panel
3. Confirm staff assignments
4. Use manual backup procedures if necessary

### System-Wide Login Issues
1. Check server status
2. Verify database connectivity
3. Review application logs
4. Restart application server if needed

---

## 📞 Support Contacts

### Technical Support
- **Email**: support@foodcompany.com
- **Phone**: (555) 123-4567
- **Hours**: 9 AM - 6 PM, Monday-Friday

### Emergency Support
- **After Hours**: (555) 987-6543
- **Email**: emergency@foodcompany.com
- **Response Time**: Within 2 hours

---

## 🔄 Updates & Maintenance

### Regular Maintenance
- **Weekly**: User access review
- **Monthly**: Password policy enforcement
- **Quarterly**: Security audit and log review

### System Updates
- Login system updates will be announced via email
- Maintenance windows scheduled during off-peak hours
- Backup procedures in place for all updates

---

## 📝 Change Log

### Version 2.0 (Current)
- ✅ Role-based login system
- ✅ Enhanced security features
- ✅ Modern UI/UX design
- ✅ Mobile responsiveness
- ✅ Activity logging

### Previous Versions
- v1.0: Basic login system with single form

---

**Last Updated**: December 2024
**Version**: 2.0
**Maintained By**: FoodCo IT Department

For additional help or feature requests, please contact your system administrator.