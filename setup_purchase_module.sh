#!/bin/bash

echo "🚀 Setting up Purchase & Vendor Management Module..."

# Create necessary directories
echo "📁 Creating view directories..."
mkdir -p resources/views/purchase-orders
mkdir -p resources/views/vendors

# Build frontend assets
echo "🎨 Building frontend assets..."
npm run build

# Clear application cache
echo "🧹 Clearing application cache..."
if command -v php &> /dev/null; then
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    echo "✅ Laravel cache cleared"
else
    echo "⚠️  PHP not found in PATH. Please run the following commands manually:"
    echo "   php artisan config:clear"
    echo "   php artisan route:clear"
    echo "   php artisan view:clear"
fi

echo ""
echo "✅ Purchase & Vendor Management Module setup complete!"
echo ""
echo "📋 Available Routes:"
echo "   • /vendors - Vendor management"
echo "   • /purchase-orders - Purchase order management"
echo "   • /purchase-orders/dashboard - Purchase dashboard"
echo ""
echo "🎯 Key Features:"
echo "   ✓ Modern responsive design"
echo "   ✓ Complete purchase order workflow"
echo "   ✓ Vendor analytics and performance tracking"
echo "   ✓ Credit management system"
echo "   ✓ Inventory integration"
echo "   ✓ PDF generation"
echo "   ✓ Transport cost management"
echo ""
echo "🔐 Access Requirements:"
echo "   • Admin or Branch Manager role required"
echo "   • Authenticated user session"
echo ""
echo "📖 For detailed documentation, see: PURCHASE_VENDOR_MANAGEMENT_MODULE.md"