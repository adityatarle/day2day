#!/bin/bash

# Multi-Role Food Company Management System Setup Script
# This script sets up the complete hierarchical user management system

echo "🍽️  Setting up Multi-Role Food Company Management System..."
echo "=========================================================="

# Check if we're in a Laravel project
if [ ! -f "artisan" ]; then
    echo "❌ Error: This script must be run from the root of a Laravel project"
    exit 1
fi

echo "📋 Step 1: Installing/Updating Composer dependencies..."
composer install --no-dev --optimize-autoloader

echo "📋 Step 2: Generating application key (if not exists)..."
php artisan key:generate --ansi

echo "📋 Step 3: Running database migrations..."
php artisan migrate --force

echo "📋 Step 4: Setting up multi-role system with sample data..."
php artisan db:seed --class=MultiRoleSystemSeeder

echo "📋 Step 5: Clearing and caching configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "📋 Step 6: Creating storage links..."
php artisan storage:link

echo "📋 Step 7: Setting proper permissions..."
chmod -R 775 storage bootstrap/cache
chmod -R 755 .

echo ""
echo "✅ Multi-Role Food Company Management System Setup Complete!"
echo ""
echo "🔐 LOGIN CREDENTIALS:"
echo "===================="
echo ""
echo "🔥 SUPER ADMIN (Complete System Control):"
echo "   Email: superadmin@foodcompany.com"
echo "   Password: password123"
echo "   Access: All branches, all users, system settings"
echo ""
echo "🏢 BRANCH MANAGERS (Branch-Specific Management):"
echo "   Mumbai: manager.mumbai@foodcompany.com / manager123"
echo "   Delhi: manager.delhi@foodcompany.com / manager123"
echo "   Bangalore: manager.bangalore@foodcompany.com / manager123"
echo "   Access: Their branch staff, inventory, POS sessions"
echo ""
echo "💰 CASHIERS (POS Operations):"
echo "   Mumbai: cashier1.mumbai@foodcompany.com / cashier123"
echo "   Delhi: cashier1.delhi@foodcompany.com / cashier123"
echo "   Bangalore: cashier1.bangalore@foodcompany.com / cashier123"
echo "   Access: POS system, sales processing, customer management"
echo ""
echo "🚚 DELIVERY STAFF:"
echo "   Mumbai: delivery1.mumbai@foodcompany.com / delivery123"
echo "   Delhi: delivery1.delhi@foodcompany.com / delivery123"
echo "   Access: Order delivery, customer interaction"
echo ""
echo "🌐 SYSTEM FEATURES:"
echo "=================="
echo "✓ Hierarchical User Management (Super Admin → Branch Manager → Cashier)"
echo "✓ Branch-Specific Data Isolation"
echo "✓ Real-time POS Session Management"
echo "✓ Inventory Control per Branch"
echo "✓ Role-based Permission System"
echo "✓ Real-time Monitoring & Analytics"
echo "✓ Multi-branch Performance Tracking"
echo ""
echo "🚀 GETTING STARTED:"
echo "=================="
echo "1. Start the development server: php artisan serve"
echo "2. Visit: http://localhost:8000"
echo "3. Login with any of the above credentials"
echo "4. Explore role-specific dashboards and features"
echo ""
echo "📱 API ENDPOINTS:"
echo "================"
echo "• Real-time Monitoring: /api/monitoring/*"
echo "• User Management: /users/*"
echo "• Branch Management: /branches/*"
echo "• POS Sessions: /pos/sessions/*"
echo ""
echo "🔧 SYSTEM ARCHITECTURE:"
echo "======================"
echo "• Super Admin: Manages everything (branches, all users, system settings)"
echo "• Branch Manager: Manages their branch (staff, inventory, POS sessions)"
echo "• Cashier: Operates POS (sales, customer service, inventory viewing)"
echo "• Delivery Staff: Handles deliveries and returns"
echo ""
echo "💡 NEXT STEPS:"
echo "============="
echo "1. Customize branch settings in Super Admin dashboard"
echo "2. Add products to inventory"
echo "3. Start POS sessions for sales operations"
echo "4. Configure real-time monitoring alerts"
echo ""
echo "🎉 Your multi-role food company management system is ready!"
echo "Visit the application and start managing your food business efficiently!"