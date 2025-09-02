# Quick Setup Guide - Fruit & Vegetable Business Management System

## Prerequisites

Before you begin, ensure you have the following installed on your system:

- **PHP 8.2 or higher**
- **Composer** (PHP package manager)
- **MySQL/PostgreSQL/SQLite** (database)
- **Node.js & NPM** (for frontend assets)
- **Git** (for cloning the repository)

## Step 1: Clone and Setup

```bash
# Clone the repository
git clone <repository-url>
cd fruit-vegetable-business-system

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

## Step 2: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Edit the `.env` file and configure your database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fruit_veg_business
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## Step 3: Database Setup

```bash
# Run database migrations
php artisan migrate

# Seed initial data
php artisan db:seed
```

## Step 4: Build Frontend Assets

```bash
# Build assets for production
npm run build

# Or for development
npm run dev
```

## Step 5: Start the Application

```bash
# Start Laravel development server
php artisan serve

# The application will be available at http://localhost:8000
```

## Step 6: Initial Login

After seeding, you can login with these default credentials:

- **Email**: admin@example.com
- **Password**: password

## Default Data

The system comes with pre-configured:

- **Roles**: Admin, Branch Manager, Cashier, Delivery Boy
- **Branches**: Main Branch, North Branch, South Branch
- **Product Categories**: Fruit, Vegetable, Leafy, Exotic
- **GST Rates**: 0%, 5%, 12%, 18%, 28%
- **Expense Categories**: Transport, Labour, Utilities, Others

## Quick Test

1. **Login** with admin credentials
2. **Navigate** to Dashboard
3. **Check** Products section
4. **Verify** Inventory status
5. **Test** Order creation

## Common Issues & Solutions

### Issue: Database connection failed
**Solution**: Check your `.env` file database credentials

### Issue: Migration failed
**Solution**: Ensure database exists and user has proper permissions

### Issue: Assets not loading
**Solution**: Run `npm run build` and check file permissions

### Issue: Permission denied errors
**Solution**: Set proper permissions on storage and bootstrap/cache directories

## Next Steps

1. **Configure** your business branches
2. **Add** your products and categories
3. **Set up** vendor information
4. **Configure** pricing strategies
5. **Create** user accounts for staff
6. **Customize** the system as needed

## Support

- Check the `SYSTEM_DOCUMENTATION.md` for detailed information
- Review API endpoints in `routes/api.php`
- Check web routes in `routes/web.php`
- Examine models in `app/Models/` directory

## Development

For development purposes:

```bash
# Watch for changes and rebuild assets
npm run dev

# Run tests
php artisan test

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## Production Deployment

For production deployment:

1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false` in `.env`
3. Configure proper database credentials
4. Set up web server (Apache/Nginx)
5. Configure SSL certificates
6. Set up proper file permissions
7. Configure backup strategies

## Security Checklist

- [ ] Change default admin password
- [ ] Configure proper file permissions
- [ ] Set up SSL/HTTPS
- [ ] Configure firewall rules
- [ ] Set up regular backups
- [ ] Monitor error logs
- [ ] Keep dependencies updated

## Performance Tips

- Enable OPcache for PHP
- Use Redis for caching
- Configure database query caching
- Optimize images and assets
- Use CDN for static files
- Monitor database performance

---

**Note**: This is a development setup. For production use, ensure proper security measures, SSL certificates, and server hardening are implemented.