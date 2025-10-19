# 📚 API Documentation

## 🔐 Authentication

### User Authentication
```http
POST /login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

### Admin Authentication
```http
POST /admin/login
Content-Type: application/json

{
    "email": "admin@example.com",
    "password": "password"
}
```

## 👤 User Management

### Get User Profile
```http
GET /api/user/profile
Authorization: Bearer {token}
```

### Update User Profile
```http
PUT /api/user/profile
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com"
}
```

### Get User Subscription
```http
GET /api/user/subscription
Authorization: Bearer {token}
```

## 📊 Subscription Management

### Get Available Plans
```http
GET /api/subscriptions/plans
Authorization: Bearer {token}
```

### Subscribe to Plan
```http
POST /api/subscriptions/subscribe
Authorization: Bearer {token}
Content-Type: application/json

{
    "plan_id": 1,
    "payment_method": "stripe",
    "coupon_code": "DISCOUNT10"
}
```

### Cancel Subscription
```http
POST /api/subscriptions/cancel
Authorization: Bearer {token}
```

### Get Usage Statistics
```http
GET /api/subscriptions/usage
Authorization: Bearer {token}
```

## 💳 Payment Processing

### Create Payment Intent (Stripe)
```http
POST /api/payments/stripe/create-intent
Authorization: Bearer {token}
Content-Type: application/json

{
    "amount": 2999,
    "currency": "usd",
    "plan_id": 1
}
```

### Confirm Payment (Stripe)
```http
POST /api/payments/stripe/confirm
Authorization: Bearer {token}
Content-Type: application/json

{
    "payment_intent_id": "pi_1234567890"
}
```

### Create PayPal Order
```http
POST /api/payments/paypal/create-order
Authorization: Bearer {token}
Content-Type: application/json

{
    "amount": 29.99,
    "currency": "USD",
    "plan_id": 1
}
```

### Capture PayPal Payment
```http
POST /api/payments/paypal/capture
Authorization: Bearer {token}
Content-Type: application/json

{
    "order_id": "ORDER_ID"
}
```

## 🎫 Coupon Management

### Get Available Coupons
```http
GET /api/coupons
Authorization: Bearer {token}
```

### Validate Coupon
```http
POST /api/coupons/validate
Authorization: Bearer {token}
Content-Type: application/json

{
    "code": "DISCOUNT10"
}
```

### Apply Coupon
```http
POST /api/coupons/apply
Authorization: Bearer {token}
Content-Type: application/json

{
    "code": "DISCOUNT10",
    "plan_id": 1
}
```

## 🌐 Network Management

### Get User Networks
```http
GET /api/networks
Authorization: Bearer {token}
```

### Add Network
```http
POST /api/networks
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "My Network",
    "type": "shopify",
    "config": {
        "api_key": "your_api_key",
        "shop_domain": "your-shop.myshopify.com"
    }
}
```

### Update Network
```http
PUT /api/networks/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Updated Network Name",
    "config": {
        "api_key": "new_api_key"
    }
}
```

### Delete Network
```http
DELETE /api/networks/{id}
Authorization: Bearer {token}
```

## 🔄 Sync Operations

### Manual Sync
```http
POST /api/sync/manual
Authorization: Bearer {token}
Content-Type: application/json

{
    "network_id": 1,
    "sync_type": "full"
}
```

### Get Sync Status
```http
GET /api/sync/status/{job_id}
Authorization: Bearer {token}
```

### Get Sync History
```http
GET /api/sync/history
Authorization: Bearer {token}
```

## 📈 Analytics & Reports

### Get Dashboard Stats
```http
GET /api/analytics/dashboard
Authorization: Bearer {token}
```

### Get Revenue Report
```http
GET /api/analytics/revenue
Authorization: Bearer {token}
```

### Get Usage Report
```http
GET /api/analytics/usage
Authorization: Bearer {token}
```

## 🔔 Notifications

### Get Notifications
```http
GET /api/notifications
Authorization: Bearer {token}
```

### Mark Notification as Read
```http
PUT /api/notifications/{id}/read
Authorization: Bearer {token}
```

### Mark All as Read
```http
POST /api/notifications/mark-all-read
Authorization: Bearer {token}
```

## 👨‍💼 Admin APIs

### Get All Users
```http
GET /api/admin/users
Authorization: Bearer {admin_token}
```

### Get User Details
```http
GET /api/admin/users/{id}
Authorization: Bearer {admin_token}
```

### Update User
```http
PUT /api/admin/users/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "name": "Updated Name",
    "email": "updated@example.com",
    "status": "active"
}
```

### Impersonate User
```http
POST /api/admin/users/{id}/impersonate
Authorization: Bearer {admin_token}
```

### Get All Plans
```http
GET /api/admin/plans
Authorization: Bearer {admin_token}
```

### Create Plan
```http
POST /api/admin/plans
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "name": "Premium Plan",
    "description": "Premium features",
    "price": 99.99,
    "billing_cycle": "monthly",
    "trial_days": 14,
    "max_networks": 20,
    "daily_sync_limit": 1000,
    "monthly_sync_limit": 20000,
    "revenue_cap": 100000,
    "orders_cap": 5000,
    "is_active": true
}
```

### Update Plan
```http
PUT /api/admin/plans/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "name": "Updated Plan Name",
    "price": 79.99
}
```

### Delete Plan
```http
DELETE /api/admin/plans/{id}
Authorization: Bearer {admin_token}
```

### Get All Coupons
```http
GET /api/admin/coupons
Authorization: Bearer {admin_token}
```

### Create Coupon
```http
POST /api/admin/coupons
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "code": "SAVE20",
    "type": "percentage",
    "value": 20,
    "max_uses": 100,
    "expires_at": "2024-12-31 23:59:59",
    "is_active": true
}
```

### Get System Reports
```http
GET /api/admin/reports/system
Authorization: Bearer {admin_token}
```

### Get User Reports
```http
GET /api/admin/reports/users
Authorization: Bearer {admin_token}
```

### Get Revenue Reports
```http
GET /api/admin/reports/revenue
Authorization: Bearer {admin_token}
```

## ⚙️ Settings Management

### Get Site Settings
```http
GET /api/admin/settings
Authorization: Bearer {admin_token}
```

### Update Site Settings
```http
PUT /api/admin/settings
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "site_name": "My Site",
    "site_logo": "logo.png",
    "meta_description": "Site description",
    "meta_author": "Site Author"
}
```

### Get SMTP Settings
```http
GET /api/admin/settings/smtp
Authorization: Bearer {admin_token}
```

### Update SMTP Settings
```http
PUT /api/admin/settings/smtp
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "smtp_host": "smtp.gmail.com",
    "smtp_port": 587,
    "smtp_username": "your_email@gmail.com",
    "smtp_password": "your_password",
    "smtp_encryption": "tls"
}
```

## 📊 Error Monitoring

### Get Error Logs
```http
GET /api/admin/errors
Authorization: Bearer {admin_token}
```

### Get Performance Metrics
```http
GET /api/admin/performance
Authorization: Bearer {admin_token}
```

## 🔄 Webhooks

### Stripe Webhook
```http
POST /webhooks/stripe
Content-Type: application/json
Stripe-Signature: {signature}

{
    "type": "payment_intent.succeeded",
    "data": {
        "object": {
            "id": "pi_1234567890",
            "amount": 2999,
            "currency": "usd"
        }
    }
}
```

### PayPal Webhook
```http
POST /webhooks/paypal
Content-Type: application/json

{
    "event_type": "PAYMENT.CAPTURE.COMPLETED",
    "resource": {
        "id": "CAPTURE_ID",
        "amount": {
            "value": "29.99",
            "currency_code": "USD"
        }
    }
}
```

## 📝 Response Formats

### Success Response
```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {
        // Response data
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        "field": ["Error message"]
    }
}
```

### Pagination Response
```json
{
    "success": true,
    "data": {
        "items": [],
        "pagination": {
            "current_page": 1,
            "last_page": 10,
            "per_page": 15,
            "total": 150
        }
    }
}
```

## 🔒 Rate Limiting

- **User APIs**: 100 requests per minute
- **Admin APIs**: 200 requests per minute
- **Payment APIs**: 10 requests per minute

## 📋 Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `429` - Too Many Requests
- `500` - Internal Server Error

## 🧪 Testing

### Test User Credentials
```json
{
    "email": "test@example.com",
    "password": "password"
}
```

### Test Admin Credentials
```json
{
    "email": "admin@example.com",
    "password": "password"
}
```

## 📞 Support

للحصول على الدعم:
- إنشاء Issue في GitHub
- التواصل عبر البريد الإلكتروني
- مراجعة الوثائق

---

**تم تطوير هذا النظام بواسطة فريق التطوير المتقدم** 🚀



