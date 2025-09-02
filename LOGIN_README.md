# Food Company Login System

This project now has a beautiful login page that serves as the home page for your food company application.

## Features

- **Modern Login Interface**: Beautiful, responsive design with food-themed colors (orange/red gradient)
- **Role-Based Access**: Built-in user roles (Admin, Branch Manager, Cashier, Delivery Boy)
- **Secure Authentication**: Uses Laravel's built-in authentication system
- **Responsive Design**: Works perfectly on desktop and mobile devices

## Setup Instructions

### 1. Install Dependencies
```bash
composer install
npm install
```

### 2. Environment Configuration
Copy the `.env.example` file to `.env` and configure your database:
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Setup
```bash
php artisan migrate
php artisan db:seed
```

### 4. Build Assets
```bash
npm run build
```

### 5. Start the Server
```bash
php artisan serve
```

## Login Credentials

After running the seeder, you can use these credentials to test the system:

### Admin User
- **Email**: `admin@example.com`
- **Password**: `admin123`

### Test User (Added by DatabaseSeeder)
- **Email**: `test@foodcompany.com`
- **Password**: `password123`

## How It Works

1. **Home Page**: When you visit `/`, you'll be redirected to the login page if not authenticated
2. **Login Page**: Beautiful login form with email/password fields
3. **Dashboard**: After successful login, users are redirected to `/dashboard`
4. **Logout**: Users can logout using the button in the dashboard navigation

## File Structure

- `resources/views/login.blade.php` - The main login page
- `resources/views/dashboard.blade.php` - Dashboard for authenticated users
- `app/Http/Controllers/Auth/WebAuthController.php` - Handles web authentication
- `routes/web.php` - Web routes including authentication

## Customization

### Colors
The login page uses a food-themed color scheme:
- Primary: Orange (`from-orange-500 to-red-500`)
- Background: Light orange/red gradient
- Accents: Various shades of orange and red

### Logo
The current logo is a book icon. You can replace it with your company logo by:
1. Replacing the SVG in the login view
2. Updating the company name in the title and footer

### Company Name
Update the company name by changing the `config('app.name')` value in your `.env` file or by updating the fallback values in the views.

## Security Features

- CSRF protection on all forms
- Password hashing
- Session-based authentication
- Remember me functionality
- Input validation and sanitization

## Next Steps

After implementing the login system, you can:

1. **Add Registration**: Create a user registration page
2. **Password Reset**: Implement forgot password functionality
3. **Email Verification**: Add email verification for new accounts
4. **Two-Factor Authentication**: Enhance security with 2FA
5. **User Management**: Build admin panels for user management
6. **Menu Management**: Create interfaces for managing food menus
7. **Order Processing**: Build order management systems

## Troubleshooting

### Common Issues

1. **"Class not found" errors**: Run `composer dump-autoload`
2. **Database connection issues**: Check your `.env` file database configuration
3. **Assets not loading**: Run `npm run build` to compile CSS/JS
4. **Login not working**: Ensure the database is seeded and users exist

### Getting Help

If you encounter any issues:
1. Check the Laravel logs in `storage/logs/`
2. Ensure all migrations have been run
3. Verify the database seeder ran successfully
4. Check that all required models exist

## Support

This login system is built on Laravel's robust authentication foundation and follows best practices for security and user experience.