# Email Configuration Guide - AdvancedCouponSystem

## 📧 Complete Authentication System

This system includes:
- ✅ Email Verification after registration
- ✅ Password Reset via email
- ✅ Custom email templates matching Osen theme
- ✅ SMTP configuration tools

---

## 🚀 Quick Setup (3 Steps)

### Step 1: Configure SMTP Settings

Add these settings to your `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=info@yourdomain.com
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@yourdomain.com
MAIL_FROM_NAME="AdvancedCouponSystem"
```

**OR** use the interactive setup wizard:

```bash
php artisan mail:setup
```

---

### Step 2: Test Email Configuration

Send a test email to verify everything works:

```bash
php artisan mail:test your-email@example.com
```

You should see:
```
✓ Test email sent successfully!
Check your inbox at: your-email@example.com
```

---

### Step 3: Test Complete Flow

1. **Register a new account**
   - Go to: `http://yourdomain.com/register`
   - Fill in: Name, Email, Password
   - Click "Sign Up"

2. **Check your email**
   - You'll receive: "Verify Your Email Address"
   - Click the "Verify Email Address" button

3. **Access Dashboard**
   - After verification, you can access the dashboard

4. **Test Password Reset**
   - Go to: `http://yourdomain.com/password/reset`
   - Enter your email
   - Check email for reset link
   - Create new password

---

## 📝 Common SMTP Providers

### Gmail
```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-specific-password
MAIL_ENCRYPTION=tls
```
**Note:** Enable "Less secure app access" or use App Password

### cPanel/Hosting Email
```env
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=info@yourdomain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
```

### Office 365
```env
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=your-email@company.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

### SendGrid
```env
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
```

---

## 🔍 Troubleshooting

### Email not sending?

1. **Check SMTP credentials**
   ```bash
   php artisan mail:test your-email@example.com
   ```

2. **Verify port is not blocked**
   - Port 587 (TLS) - Most common
   - Port 465 (SSL) - Alternative
   - Port 25 (No encryption) - Often blocked

3. **Check logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **Test with log driver** (for development)
   ```env
   MAIL_MAILER=log
   ```
   Emails will be saved to `storage/logs/laravel.log`

### Email verification link not working?

1. **Check APP_URL in .env**
   ```env
   APP_URL=http://yourdomain.com
   ```

2. **Clear config cache**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

### Password reset not working?

1. **Check password_reset_tokens table exists**
   ```bash
   php artisan migrate
   ```

2. **Verify email is in database**
   ```bash
   php artisan tinker
   User::where('email', 'your-email@example.com')->first()
   ```

---

## 🎨 Email Templates

All email templates are located in:
```
resources/views/emails/
├── layout.blade.php              # Main email layout
└── auth/
    ├── verify-email.blade.php    # Verification email
    ├── reset-password.blade.php  # Password reset email
    └── welcome.blade.php          # Welcome email (optional)
```

**Customize:** Edit these files to change email content, colors, or branding.

---

## 🔐 Security Best Practices

1. **Never commit .env file**
   - Already in `.gitignore`
   - Use `.env.example` as template

2. **Use App-Specific Passwords**
   - Gmail: Generate App Password
   - Don't use your main account password

3. **Enable 2FA on email account**
   - Adds extra security layer

4. **Use TLS encryption**
   - More secure than no encryption
   - Required by most providers

---

## 📚 API Documentation

### Routes Added

#### Email Verification
- `GET /email/verify` - Show verification notice
- `GET /email/verify/{id}/{hash}` - Verify email
- `POST /email/resend` - Resend verification email

#### Password Reset
- `GET /password/reset` - Show forgot password form
- `POST /password/email` - Send reset link email
- `GET /password/reset/{token}` - Show reset form
- `POST /password/reset` - Reset password

### Middleware
- `verified` - Requires email verification
- Applied to: Dashboard, Networks, Campaigns, Coupons

---

## 🛠 Artisan Commands

```bash
# Interactive SMTP setup
php artisan mail:setup

# Test email delivery
php artisan mail:test recipient@example.com

# Clear config cache
php artisan config:clear

# Run migrations
php artisan migrate
```

---

## ✅ Testing Checklist

- [ ] SMTP configuration in `.env`
- [ ] Test email sends successfully
- [ ] Register new user
- [ ] Receive verification email
- [ ] Click verification link
- [ ] Access dashboard after verification
- [ ] Test forgot password
- [ ] Receive reset email
- [ ] Reset password successfully
- [ ] Login with new password

---

## 📞 Support

If you encounter any issues:

1. Check the troubleshooting section above
2. Review `storage/logs/laravel.log`
3. Verify all .env settings
4. Test with `php artisan mail:test`

---

**🎉 Your authentication system is now complete and ready to use!**

