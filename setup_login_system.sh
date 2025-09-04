#!/bin/bash

# FoodCo Login System Setup Script
# This script sets up the enhanced login system with sample data

echo "🍽️ Setting up FoodCo Enhanced Login System..."
echo "================================================"

# Check if we're in a Laravel project
if [ ! -f "artisan" ]; then
    echo "❌ Error: This script must be run from the Laravel project root directory."
    exit 1
fi

echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader

echo "🔑 Generating application key (if needed)..."
php artisan key:generate --force

echo "🗃️ Running database migrations..."
php artisan migrate --force

echo "🌱 Seeding login system data..."
php artisan db:seed --class=LoginSystemSeeder

echo "🏗️ Building frontend assets..."
if [ -f "package.json" ]; then
    npm install
    npm run build
else
    echo "⚠️  No package.json found, skipping npm build"
fi

echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "📊 Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "✅ Login system setup completed successfully!"
echo ""
echo "🎯 Login Credentials:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "👑 ADMIN LOGIN:"
echo "   Email: admin@foodcompany.com"
echo "   Password: admin123"
echo "   Access: Full system administration"
echo ""
echo "🏢 BRANCH MANAGER LOGIN:"
echo "   Email: manager@foodcompany.com"
echo "   Password: manager123"
echo "   Access: Branch operations management"
echo ""
echo "🏪 OUTLET STAFF LOGIN:"
echo "   Outlet Code: FDC001 (Main Branch)"
echo "   Email: cashier@foodcompany.com"
echo "   Password: cashier123"
echo "   Access: POS and daily operations"
echo ""
echo "   Additional outlets: FDC002, FDC003"
echo "   Additional cashiers: cashier2@foodcompany.com, cashier3@foodcompany.com"
echo ""
echo "🧪 TEST USER:"
echo "   Email: test@foodcompany.com"
echo "   Password: password123"
echo "   Access: Basic system access"
echo ""
echo "📍 Access your application:"
echo "   Development: http://localhost:8000"
echo "   Production: Your configured domain"
echo ""
echo "📖 Documentation:"
echo "   - See COMPREHENSIVE_LOGIN_GUIDE.md for detailed instructions"
echo "   - Check LOGIN_README.md for quick setup info"
echo ""
echo "🚀 To start the development server:"
echo "   php artisan serve"
echo ""
echo "🎉 Your FoodCo login system is ready to use!"