#!/bin/bash

# Enhanced Food Company Management System - Module Setup Script
# This script sets up all the enhanced modules and features

echo "🍎🥬 Setting up Enhanced Food Company Management System Modules..."

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed or not in PATH"
    echo "Please install PHP 8.2+ and try again"
    exit 1
fi

# Check if we're in a Laravel project
if [ ! -f "artisan" ]; then
    echo "❌ Laravel artisan file not found"
    echo "Please run this script from the Laravel project root"
    exit 1
fi

echo "✅ PHP and Laravel detected"

# Run database migrations
echo "📊 Running database migrations..."
php artisan migrate --force

if [ $? -eq 0 ]; then
    echo "✅ Database migrations completed successfully"
else
    echo "❌ Database migration failed"
    exit 1
fi

# Seed enhanced system data
echo "🌱 Seeding enhanced system data..."
php artisan db:seed --class=EnhancedSystemSeeder

if [ $? -eq 0 ]; then
    echo "✅ Enhanced system data seeded successfully"
else
    echo "⚠️  Enhanced system seeding failed (this is optional)"
fi

# Clear and cache configuration
echo "🔧 Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🎉 Enhanced modules setup completed successfully!"
echo ""
echo "📋 Modules Added:"
echo "  ✅ Enhanced Inventory & Stock Management"
echo "     - Auto stock updates after sales"
echo "     - Threshold-based online availability"
echo "     - Batch-wise tracking with FIFO"
echo "     - Multi-branch stock management"
echo ""
echo "  ✅ Advanced Loss Tracking"
echo "     - Weight, water, and wastage loss tracking"
echo "     - Complimentary/adjustment tracking"
echo "     - Loss analytics and prevention recommendations"
echo ""
echo "  ✅ Product & Pricing Management"
echo "     - Enhanced categorization (Fruit/Vegetable/Leafy/Exotic)"
echo "     - Vendor-specific pricing"
echo "     - Branch-wise selling prices"
echo "     - Shelf life and storage management"
echo ""
echo "  ✅ Sales & Billing Management"
echo "     - Quick on-shop billing"
echo "     - Online payment processing"
echo "     - Bulk invoice generation"
echo "     - Partial payment handling"
echo ""
echo "  ✅ Delivery Boy Adjustment Module"
echo "     - Real-time delivery tracking"
echo "     - Customer return processing"
echo "     - Mobile app ready endpoints"
echo "     - Automatic invoice regeneration"
echo ""
echo "  ✅ Wholesaler Billing"
echo "     - Tiered pricing based on quantity"
echo "     - Customer-specific pricing"
echo "     - Credit management"
echo "     - Bulk purchase discounts"
echo ""
echo "  ✅ Expense & Cost Allocation"
echo "     - Transport, labour, operational cost tracking"
echo "     - Automatic cost distribution to products"
echo "     - True profit margin calculation"
echo ""
echo "🔧 Automated Tasks:"
echo "  Run: php artisan system:process-automated-tasks"
echo "  - Process expired batches"
echo "  - Update online availability"
echo "  - Generate stock alerts"
echo ""
echo "📖 Documentation:"
echo "  - API Documentation: ENHANCED_MODULES_API.md"
echo "  - System Documentation: SYSTEM_DOCUMENTATION.md"
echo ""
echo "🌐 Next Steps:"
echo "  1. Start the development server: php artisan serve"
echo "  2. Access the web interface at http://localhost:8000"
echo "  3. Use API endpoints for mobile app integration"
echo "  4. Set up automated tasks in crontab (optional)"
echo ""
echo "Happy coding! 🚀"