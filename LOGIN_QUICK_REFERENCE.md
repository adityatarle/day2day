# 🚀 FoodCo Login System - Quick Reference

## 🎯 Login URLs

| Login Type | URL | Description |
|------------|-----|-------------|
| **Main Login** | `/` or `/login` | Role selection interface |
| **Outlet Direct** | `/outlet/{code}/login` | Direct outlet staff login |
| **Admin Panel** | `/admin/dashboard` | After admin login |

## 🔐 Default Credentials

### 👑 Admin Access
```
Email: admin@foodcompany.com
Password: admin123
Role: Super Administrator
```

### 🏢 Branch Managers
```
Main Branch:
Email: manager@foodcompany.com
Password: manager123
Branch: FDC001

Downtown Branch:
Email: manager2@foodcompany.com
Password: manager123
Branch: FDC002

Uptown Branch:
Email: manager3@foodcompany.com
Password: manager123
Branch: FDC003
```

### 🏪 Outlet Staff (Cashiers)
```
Main Branch (FDC001):
Email: cashier@foodcompany.com
Password: cashier123

Downtown Branch (FDC002):
Email: cashier2@foodcompany.com
Password: cashier123

Uptown Branch (FDC003):
Email: cashier3@foodcompany.com
Password: cashier123
```

## 🏢 Outlet Codes
- **FDC001** - Main Branch (123 Main Street)
- **FDC002** - Downtown Branch (456 Business Ave)
- **FDC003** - Uptown Express (789 Uptown Plaza)

## 🔄 Login Flow

### Admin Login
1. Select "Admin Login" (red card)
2. Enter admin credentials
3. Redirected to `/admin/dashboard`

### Branch Manager Login
1. Select "Branch Manager" (blue card)
2. Enter manager credentials
3. Redirected to `/dashboard`

### Outlet Staff Login
1. Select "Outlet Staff" (green card)
2. Enter outlet code (e.g., FDC001)
3. Enter staff credentials
4. Redirected to `/pos` (cashiers) or `/dashboard` (managers)

## 🛠️ Setup Commands

```bash
# Quick setup (run from project root)
./setup_login_system.sh

# Manual setup
php artisan migrate
php artisan db:seed --class=LoginSystemSeeder
php artisan serve
```

## 🔧 Troubleshooting

| Issue | Solution |
|-------|----------|
| Invalid credentials | Check role and email combination |
| Outlet code not found | Verify outlet code (FDC001, FDC002, FDC003) |
| Branch inactive | Contact admin to activate branch |
| Account inactive | Contact admin to activate user account |

## 📱 Features

- ✅ Role-based login interface
- ✅ Outlet-specific authentication
- ✅ Mobile responsive design
- ✅ Security logging for admins
- ✅ Operating hours awareness
- ✅ POS session management
- ✅ Remember me functionality

## 🎨 UI Colors

- **Admin**: Red theme (Crown icon)
- **Branch Manager**: Blue theme (Building icon)
- **Outlet Staff**: Green theme (Store icon)

## 📞 Support

- **Technical**: Check `COMPREHENSIVE_LOGIN_GUIDE.md`
- **Issues**: Review Laravel logs in `storage/logs/`
- **Database**: Verify seeder ran successfully

---

**Last Updated**: December 2024 | **Version**: 2.0