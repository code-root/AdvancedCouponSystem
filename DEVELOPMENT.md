# Advanced Coupon System - Development Guide

## 📋 Controllers Summary

### Authentication Controllers

#### 1. **LoginController** (`Auth/LoginController.php`)
- `showLoginForm()` - Display login page
- `login()` - Handle login
- `logout()` - Handle logout

#### 2. **RegisterController** (`Auth/RegisterController.php`)
- `showRegistrationForm()` - Display registration page
- `register()` - Handle registration

#### 3. **ForgotPasswordController** (`Auth/ForgotPasswordController.php`)
- `showLinkRequestForm()` - Display password reset request form
- `sendResetLinkEmail()` - Send password reset email

#### 4. **ResetPasswordController** (`Auth/ResetPasswordController.php`)
- `showResetForm()` - Display password reset form
- `reset()` - Handle password reset

#### 5. **AuthController** (API Authentication)
- `register()` - API user registration
- `login()` - API user login
- `logout()` - API user logout
- `refresh()` - Refresh token
- `user()` - Get current user

---

### Main Controllers

#### 6. **DashboardController**
- `index()` - Dashboard main page
- `overview()` - Dashboard overview data (JSON)
- `analytics()` - Analytics data (JSON)
- `recentActivities()` - Recent activities (JSON)
- `profile()` - User profile page
- `updateProfile()` - Update user profile
- `updatePassword()` - Update user password
- `settings()` - Settings page (Admin)
- `updateGeneralSettings()` - Update general settings
- `updateEmailSettings()` - Update email settings
- `updateNotificationSettings()` - Update notification settings
- `users()` - List users (Admin)
- `createUser()` - Create user form (Admin)
- `storeUser()` - Store user (Admin)
- `editUser()` - Edit user form (Admin)
- `updateUser()` - Update user (Admin)
- `destroyUser()` - Delete user (Admin)
- `assignRole()` - Assign role to user (Admin)

#### 7. **NetworkController**
- `index()` - List all networks
- `create()` - Create network form
- `store()` - Store new network
- `show()` - Show network details
- `edit()` - Edit network form
- `update()` - Update network
- `destroy()` - Delete network
- `createConnection()` - Create network connection
- `getData()` - Get network data (JSON)

#### 8. **CampaignController**
- `index()` - List all campaigns
- `create()` - Create campaign form
- `store()` - Store new campaign
- `show()` - Show campaign details
- `edit()` - Edit campaign form
- `update()` - Update campaign
- `destroy()` - Delete campaign
- `activate()` - Activate campaign
- `deactivate()` - Deactivate campaign
- `statistics()` - Get campaign statistics (JSON)
- `coupons()` - Get campaign coupons
- `active()` - Get active campaigns (Public API)

#### 9. **CouponController**
- `index()` - List all coupons
- `create()` - Create coupon form
- `store()` - Store new coupon
- `show()` - Show coupon details
- `edit()` - Edit coupon form
- `update()` - Update coupon
- `destroy()` - Delete coupon
- `validate()` - Validate coupon (API)
- `redeem()` - Redeem coupon (API)
- `activate()` - Activate coupon
- `deactivate()` - Deactivate coupon
- `history()` - Get coupon history (JSON)
- `bulkGenerate()` - Generate multiple coupons
- `export()` - Export coupons

#### 10. **PurchaseController**
- `index()` - List all purchases
- `create()` - Create purchase form
- `store()` - Store new purchase
- `show()` - Show purchase details
- `edit()` - Edit purchase form
- `update()` - Update purchase
- `destroy()` - Delete purchase
- `confirm()` - Confirm purchase
- `cancel()` - Cancel purchase
- `statistics()` - Get purchase statistics (JSON)
- `export()` - Export purchases

#### 11. **CountryController**
- `index()` - List all countries
- `create()` - Create country form
- `store()` - Store new country
- `show()` - Show country details
- `edit()` - Edit country form
- `update()` - Update country
- `destroy()` - Delete country
- `networks()` - Get country networks (JSON)

#### 12. **ReportController**
- `index()` - Reports index page
- `coupons()` - Coupons report
- `purchases()` - Purchases report
- `campaigns()` - Campaigns report
- `networks()` - Networks report
- `revenue()` - Revenue report
- `export()` - Export report
- `download()` - Download exported file

---

## 🗄️ Models & Relationships

### User Model
- **Traits:** HasApiTokens, HasRoles, Notifiable
- **Relationships:**
  - `purchases()` - hasMany Purchase
  - `roles()` - belongsToMany Role (via Spatie Permission)
  - `permissions()` - via HasRoles trait

### Network Model
- **Relationships:**
  - `country()` - belongsTo Country
  - `connections()` - hasMany NetworkConnection
  - `data()` - hasMany NetworkData
  - `campaigns()` - hasMany Campaign
  - `purchases()` - hasMany Purchase

### Campaign Model
- **Relationships:**
  - `network()` - belongsTo Network
  - `coupons()` - hasMany Coupon
  - `purchases()` - hasMany Purchase

### Coupon Model
- **Relationships:**
  - `campaign()` - belongsTo Campaign
  - `purchases()` - hasMany Purchase

### Purchase Model
- **Relationships:**
  - `user()` - belongsTo User
  - `coupon()` - belongsTo Coupon
  - `campaign()` - belongsTo Campaign

### Country Model
- **Relationships:**
  - `networks()` - hasMany Network

---

## 🛣️ Routes Summary

### Web Routes

```php
// Public
GET  /                          - Home page

// Authentication
GET  /login                     - Login form
POST /login                     - Process login
GET  /register                  - Registration form
POST /register                  - Process registration
POST /logout                    - Logout
GET  /password/reset            - Forgot password form
POST /password/email            - Send reset email
GET  /password/reset/{token}    - Reset password form
POST /password/reset            - Process password reset

// Dashboard (auth required)
GET  /dashboard                 - Dashboard home
GET  /dashboard/overview        - Overview data
GET  /dashboard/analytics       - Analytics data
GET  /dashboard/profile         - User profile
PUT  /dashboard/profile         - Update profile
PUT  /dashboard/password        - Update password

// Networks (auth required)
GET  /networks                   - List networks
GET  /networks/create            - Create form
POST /networks                   - Store network
GET  /networks/{id}              - Show network
GET  /networks/{id}/edit         - Edit form
PUT  /networks/{id}              - Update network
DELETE /networks/{id}            - Delete network

// Campaigns (auth required)
GET  /campaigns                 - List campaigns
GET  /campaigns/create          - Create form
POST /campaigns                 - Store campaign
GET  /campaigns/{id}            - Show campaign
GET  /campaigns/{id}/edit       - Edit form
PUT  /campaigns/{id}            - Update campaign
DELETE /campaigns/{id}          - Delete campaign

// Coupons (auth required)
GET  /coupons                   - List coupons
GET  /coupons/create            - Create form
POST /coupons                   - Store coupon
GET  /coupons/{id}              - Show coupon
GET  /coupons/{id}/edit         - Edit form
PUT  /coupons/{id}              - Update coupon
DELETE /coupons/{id}            - Delete coupon

// Purchases (auth required)
GET  /purchases                 - List purchases
GET  /purchases/create          - Create form
POST /purchases                 - Store purchase
GET  /purchases/{id}            - Show purchase
GET  /purchases/{id}/edit       - Edit form
PUT  /purchases/{id}            - Update purchase
DELETE /purchases/{id}          - Delete purchase

// Countries (auth required)
GET  /countries                 - List countries
GET  /countries/create          - Create form
POST /countries                 - Store country
GET  /countries/{id}            - Show country
GET  /countries/{id}/edit       - Edit form
PUT  /countries/{id}            - Update country
DELETE /countries/{id}          - Delete country

// Reports (auth required)
GET  /reports                   - Reports index
GET  /reports/coupons           - Coupons report
GET  /reports/purchases         - Purchases report
GET  /reports/campaigns         - Campaigns report
GET  /reports/revenue           - Revenue report
POST /reports/export/{type}     - Export report

// Settings (admin only)
GET  /settings                  - Settings page
PUT  /settings/general          - Update general settings
PUT  /settings/email            - Update email settings
PUT  /settings/notification     - Update notification settings

// Users (admin only)
GET  /users                     - List users
GET  /users/create              - Create form
POST /users                     - Store user
GET  /users/{id}/edit           - Edit form
PUT  /users/{id}                - Update user
DELETE /users/{id}              - Delete user
POST /users/{id}/roles          - Assign role
```

### API Routes

```php
// Authentication
POST /api/auth/register         - Register user
POST /api/auth/login            - Login user
POST /api/auth/logout           - Logout (auth required)
POST /api/auth/refresh          - Refresh token (auth required)
GET  /api/auth/user             - Get current user (auth required)

// Health Check
GET  /api/health                - API health check

// All other API routes require authentication (Sanctum)
```

---

## 🔐 Authentication & Authorization

### Web Authentication
- Uses Laravel's built-in session authentication
- Login/Register/Password Reset functionality

### API Authentication
- Uses Laravel Sanctum
- Token-based authentication
- Include token in header: `Authorization: Bearer {token}`

### Roles & Permissions
- Uses Spatie Laravel Permission package
- Available roles: `admin`, `user`
- Middleware: `role:admin` for admin-only routes

---

## 🎨 Views Structure (To Be Created)

```
resources/views/
├── auth/
│   ├── login.blade.php
│   ├── register.blade.php
│   └── passwords/
│       ├── email.blade.php
│       └── reset.blade.php
├── dashboard/
│   ├── index.blade.php
│   ├── profile.blade.php
│   ├── settings.blade.php
│   ├── networks/
│   ├── campaigns/
│   ├── coupons/
│   ├── purchases/
│   ├── countries/
│   ├── reports/
│   └── users/
└── layouts/
    ├── app.blade.php
    ├── guest.blade.php
    └── dashboard.blade.php
```

---

## 📦 Dependencies

### Installed Packages
- `laravel/sanctum` - API authentication
- `spatie/laravel-permission` - Roles & permissions

### Database
- SQLite (default)
- Can be changed to MySQL/PostgreSQL

---

## 🚀 Next Steps

1. **Create Views** - Build Blade templates for all pages
2. **Add Validation** - Implement form request validation
3. **Add Tests** - Write unit and feature tests
4. **API Documentation** - Complete API documentation
5. **Seeders** - Create database seeders for testing
6. **Policies** - Implement authorization policies
7. **Events & Listeners** - Add event-driven features
8. **Notifications** - Implement email notifications
9. **File Uploads** - Add file upload functionality
10. **Export Features** - Implement CSV/Excel exports

---

## 🛠️ Development Commands

```bash
# Run migrations
php artisan migrate

# Run migrations with fresh database
php artisan migrate:fresh

# Create seeder
php artisan make:seeder {Name}Seeder

# Run seeders
php artisan db:seed

# Create policy
php artisan make:policy {Name}Policy --model={Model}

# Create request
php artisan make:request {Name}Request

# Create event
php artisan make:event {Name}Event

# Create listener
php artisan make:listener {Name}Listener

# Run tests
php artisan test

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# List routes
php artisan route:list

# Start development server
php artisan serve
```

---

## 📝 Notes

- All controllers follow RESTful conventions
- API responses return JSON
- Web routes return Blade views
- Authentication required for most routes
- Admin role required for user management and settings
- Code follows Laravel 12 best practices
- Comments are in English as requested

---

**Created:** October 10, 2025
**Laravel Version:** 12.x
**PHP Version:** 8.2+

