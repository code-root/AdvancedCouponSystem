# 🏗️ System Overview

## 📋 Table of Contents
- [Architecture](#architecture)
- [Database Schema](#database-schema)
- [Authentication & Authorization](#authentication--authorization)
- [Subscription System](#subscription-system)
- [Payment Integration](#payment-integration)
- [Admin Panel](#admin-panel)
- [Monitoring & Logging](#monitoring--logging)
- [Security Features](#security-features)
- [Performance Optimization](#performance-optimization)

## 🏛️ Architecture

### System Components
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Backend       │    │   Database      │
│   (Blade + JS)  │◄──►│   (Laravel)     │◄──►│   (MySQL)       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Admin Panel   │    │   API Layer     │    │   File Storage  │
│   (Separate)    │    │   (RESTful)     │    │   (Local/S3)    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Technology Stack
- **Backend**: Laravel 10.x
- **Frontend**: Blade Templates + Bootstrap 5
- **Database**: MySQL 8.0
- **Cache**: Redis (optional)
- **Queue**: Database/Redis
- **Payment**: Stripe + PayPal
- **Monitoring**: Custom Middleware

## 🗄️ Database Schema

### Core Tables

#### Users & Authentication
```sql
users
├── id (Primary Key)
├── name
├── email (Unique)
├── password
├── parent_user_id (For sub-users)
├── created_at
└── updated_at

admins
├── id (Primary Key)
├── name
├── email (Unique)
├── password
├── created_at
└── updated_at
```

#### Subscription System
```sql
plans
├── id (Primary Key)
├── name
├── description
├── price
├── billing_cycle (monthly/yearly)
├── trial_days
├── max_networks
├── sync_window_unit (minute/hour/day)
├── sync_window_size
├── daily_sync_limit
├── monthly_sync_limit
├── revenue_cap
├── orders_cap
├── is_active
└── created_at

subscriptions
├── id (Primary Key)
├── user_id (Foreign Key)
├── plan_id (Foreign Key)
├── status (active/trialing/cancelled)
├── trial_ends_at
├── current_period_start
├── current_period_end
├── cancelled_at
└── created_at

sync_usages
├── id (Primary Key)
├── user_id (Foreign Key)
├── period (daily/monthly)
├── window_start
├── window_end
├── sync_count
├── revenue_count
├── orders_count
└── created_at
```

#### Coupon System
```sql
plan_coupons
├── id (Primary Key)
├── code (Unique)
├── type (percentage/fixed)
├── value
├── max_uses
├── used_count
├── expires_at
├── is_active
└── created_at

plan_coupon_redemptions
├── id (Primary Key)
├── coupon_id (Foreign Key)
├── user_id (Foreign Key)
├── subscription_id (Foreign Key)
├── discount_amount
└── created_at
```

#### Monitoring & Logging
```sql
error_logs
├── id (Primary Key)
├── level (error/warning/info)
├── message
├── context (JSON)
├── user_id (Foreign Key)
├── ip_address
├── user_agent
├── url
├── method
└── created_at

performance_metrics
├── id (Primary Key)
├── url
├── method
├── response_time_ms
├── memory_usage_kb
├── db_queries
├── user_id (Foreign Key)
├── ip_address
└── created_at
```

## 🔐 Authentication & Authorization

### User Authentication
- **Guard**: `web` (default)
- **Provider**: `users` table
- **Middleware**: `auth`
- **Features**: Remember me, Password reset

### Admin Authentication
- **Guard**: `admin`
- **Provider**: `admins` table
- **Middleware**: `auth:admin`
- **Features**: Separate login, Impersonation

### Authorization Levels
```
User Levels:
├── Regular User
├── Sub-User (linked to parent)
└── Admin (full access)

Permission System:
├── subscription.manage
├── networks.manage
├── sync.manual
├── admin.users.manage
├── admin.plans.manage
└── admin.settings.manage
```

## 💳 Subscription System

### Plan Structure
```php
Plan Features:
├── Network Limits
├── Sync Frequency
├── Usage Limits (Daily/Monthly)
├── Revenue Caps
├── Order Caps
└── Trial Period
```

### Subscription States
```
Subscription Lifecycle:
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Trialing  │───►│   Active    │───►│  Cancelled  │
└─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│ Trial Ended │    │   Renewed   │    │   Expired   │
└─────────────┘    └─────────────┘    └─────────────┘
```

### Usage Tracking
```php
Usage Metrics:
├── Daily Sync Count
├── Monthly Sync Count
├── Revenue Tracking
├── Order Tracking
└── Network Connections
```

## 💰 Payment Integration

### Stripe Integration
```php
Stripe Features:
├── Payment Intents
├── Webhooks
├── Subscription Management
├── Invoice Generation
└── Refund Processing
```

### PayPal Integration
```php
PayPal Features:
├── Order Creation
├── Payment Capture
├── Webhooks
├── Subscription Management
└── Refund Processing
```

### Payment Flow
```
Payment Process:
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│ Select Plan │───►│ Payment UI  │───►│ Process     │
└─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│ Apply Coupon│    │ Webhook     │    │ Activate    │
└─────────────┘    └─────────────┘    └─────────────┘
```

## 👨‍💼 Admin Panel

### Admin Features
```
Admin Capabilities:
├── User Management
│   ├── View All Users
│   ├── Edit User Details
│   ├── Link/Unlink Sub-users
│   └── Impersonate Users
├── Plan Management
│   ├── Create/Edit Plans
│   ├── Set Limits
│   └── Manage Pricing
├── Coupon Management
│   ├── Create Coupons
│   ├── Set Discounts
│   └── Track Usage
├── System Reports
│   ├── User Analytics
│   ├── Revenue Reports
│   └── Usage Statistics
└── Site Settings
    ├── Branding
    ├── SEO Settings
    └── SMTP Configuration
```

### Admin Dashboard
```
Dashboard Sections:
├── Overview Stats
├── Recent Activity
├── System Health
├── User Growth
├── Revenue Trends
└── Quick Actions
```

## 📊 Monitoring & Logging

### Error Monitoring
```php
Error Tracking:
├── Exception Logging
├── User Context
├── Request Details
├── Stack Traces
└── Error Classification
```

### Performance Monitoring
```php
Performance Metrics:
├── Response Time
├── Memory Usage
├── Database Queries
├── User Activity
└── System Load
```

### Logging Levels
```
Log Levels:
├── ERROR: System errors
├── WARNING: Potential issues
├── INFO: General information
├── DEBUG: Development details
└── CRITICAL: System failures
```

## 🔒 Security Features

### Data Protection
```php
Security Measures:
├── CSRF Protection
├── XSS Prevention
├── SQL Injection Prevention
├── Rate Limiting
├── Input Validation
└── Data Encryption
```

### User Data Isolation
```php
Data Isolation:
├── User-specific Data
├── Admin Access Control
├── Sub-user Linking
├── Session Management
└── Impersonation Security
```

### Authentication Security
```php
Auth Security:
├── Password Hashing
├── Session Timeout
├── Multi-device Tracking
├── Login Attempts
└── Two-factor Auth (Future)
```

## ⚡ Performance Optimization

### Caching Strategy
```php
Cache Layers:
├── Application Cache
├── Database Query Cache
├── View Cache
├── Route Cache
└── Config Cache
```

### Database Optimization
```php
DB Optimization:
├── Indexed Columns
├── Query Optimization
├── Connection Pooling
├── Read Replicas (Future)
└── Database Sharding (Future)
```

### Frontend Optimization
```php
Frontend Performance:
├── Asset Minification
├── Image Optimization
├── Lazy Loading
├── CDN Integration
└── Progressive Web App
```

## 🔄 Background Jobs

### Scheduled Tasks
```php
Cron Jobs:
├── RotateSyncUsageJob (Daily)
├── ResetDailyCountersJob (Daily)
├── NotifyTrialEndingJob (Daily)
├── BackupDatabaseJob (Daily)
└── CleanupLogsJob (Weekly)
```

### Queue System
```php
Queue Jobs:
├── ProcessPayments
├── SendNotifications
├── SyncData
├── GenerateReports
└── SendEmails
```

## 📱 API Architecture

### RESTful API Design
```php
API Structure:
├── Authentication
├── User Management
├── Subscription Management
├── Payment Processing
├── Network Management
├── Sync Operations
├── Analytics
└── Admin Operations
```

### API Security
```php
API Security:
├── Token Authentication
├── Rate Limiting
├── Request Validation
├── Response Sanitization
└── CORS Configuration
```

## 🚀 Deployment Architecture

### Production Setup
```
Production Stack:
├── Load Balancer (Nginx)
├── Application Servers (PHP-FPM)
├── Database Server (MySQL)
├── Cache Server (Redis)
├── File Storage (S3/Local)
└── Monitoring (Custom)
```

### Environment Configuration
```php
Environment Variables:
├── Database Configuration
├── Cache Configuration
├── Queue Configuration
├── Mail Configuration
├── Payment Gateway Keys
└── Monitoring Settings
```

## 📈 Scalability Considerations

### Horizontal Scaling
```php
Scaling Strategy:
├── Load Balancing
├── Database Replication
├── Cache Clustering
├── Queue Workers
└── CDN Integration
```

### Vertical Scaling
```php
Resource Scaling:
├── CPU Optimization
├── Memory Management
├── Storage Optimization
├── Network Optimization
└── Database Tuning
```

## 🔧 Maintenance & Updates

### Backup Strategy
```php
Backup System:
├── Database Backups
├── File Backups
├── Configuration Backups
├── Automated Scheduling
└── Recovery Procedures
```

### Update Process
```php
Update Workflow:
├── Development Testing
├── Staging Deployment
├── Production Deployment
├── Rollback Procedures
└── Monitoring
```

---

**تم تطوير هذا النظام بواسطة فريق التطوير المتقدم** 🚀



