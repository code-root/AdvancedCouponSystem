# Email Testing Guide

## Overview

This guide explains how to test email functionality in the Advanced Coupon System.

## Test File Location

📄 `tests/Feature/EmailNotificationTest.php`

## Setup

### 1. Configure Your Email

Open the test file and change the test email:

```php
// tests/Feature/EmailNotificationTest.php
private const TEST_EMAIL = 'your-email@example.com'; // ← غير هذا إلى إيميلك
```

### 2. Configure Mail Settings

Make sure your `.env` file has valid email configuration:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourcouponapp.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Available Tests

### 1. Configuration Test (Safe - No Emails Sent)

Tests if email configuration is valid:

```bash
php artisan test --filter=test_email_configuration_is_valid
```

**Output**:
```
✅ Email configuration valid!
📧 From: Advanced Coupon System <noreply@yourcouponapp.com>
🚀 Driver: smtp
🌐 SMTP Host: smtp.gmail.com
🔌 SMTP Port: 587
🔐 SMTP Encryption: tls
```

---

### 2. Verification Email Test (Fake - No Real Email)

Tests verification email logic without sending:

```bash
php artisan test --filter=test_can_send_verification_email
```

**Output**:
```
✅ Verification email test passed!
📧 Email would be sent to: your-email@example.com
```

---

### 3. Reset Password Email Test (Fake - No Real Email)

Tests password reset email logic:

```bash
php artisan test --filter=test_can_send_reset_password_email
```

**Output**:
```
✅ Reset password email test passed!
📧 Email would be sent to: your-email@example.com
```

---

### 4. New Login Notification Test (Fake - No Real Email)

Tests login notification logic:

```bash
php artisan test --filter=test_can_send_new_login_notification
```

**Output**:
```
✅ New login notification test passed!
📧 Email would be sent to: your-email@example.com
```

---

## Sending REAL Emails (For Testing)

### Important: Enable Real Email Tests

The following tests are **disabled by default** to prevent accidental emails.

To enable them, edit the test file and **comment out** the skip line:

```php
// Before (disabled)
$this->markTestSkipped('Skipped by default. Enable to send real email.');

// After (enabled)
// $this->markTestSkipped('Skipped by default. Enable to send real email.');
```

### Test 5: Send REAL Verification Email

**⚠️ WARNING**: This will send an actual email!

```bash
php artisan test --filter=test_send_actual_verification_email
```

After enabling, this will:
- ✅ Create a test user
- ✅ Send a REAL verification email to TEST_EMAIL
- ✅ You can click the link to verify

**Expected Output**:
```
✅ REAL verification email sent!
📧 Check your inbox at: your-email@example.com
⚠️  If you don't receive it, check:
   - Spam folder
   - Email configuration in .env
   - Mail logs in storage/logs/
```

---

### Test 6: Send REAL Password Reset Email

```bash
php artisan test --filter=test_send_actual_reset_password_email
```

**Output**:
```
✅ REAL password reset email sent!
📧 Check your inbox at: your-email@example.com
🔑 Reset token: test-reset-token-1728734567
```

---

### Test 7: Send REAL Login Notification

```bash
php artisan test --filter=test_send_actual_login_notification
```

**Output**:
```
✅ REAL login notification sent!
📧 Check your inbox at: your-email@example.com
```

---

## Running All Email Tests

### Safe Tests Only (No Real Emails)

```bash
php artisan test tests/Feature/EmailNotificationTest.php
```

### Include Real Email Tests

1. First, enable the tests (comment out `markTestSkipped`)
2. Then run:

```bash
php artisan test tests/Feature/EmailNotificationTest.php
```

---

## Quick Test Commands

### Test Only Configuration

```bash
php artisan test --filter=EmailNotificationTest::test_email_configuration_is_valid
```

### Test All Notifications (Fake)

```bash
php artisan test --filter=EmailNotificationTest --exclude-group=real-email
```

### Send Test Email to Yourself

```bash
# 1. Edit the test file:
#    - Change TEST_EMAIL to your email
#    - Comment out markTestSkipped in test_send_actual_verification_email

# 2. Run:
php artisan test --filter=test_send_actual_verification_email

# 3. Check your inbox!
```

---

## Email Templates Tested

### 1. Verification Email
- **File**: `resources/views/emails/verify-email.blade.php`
- **Purpose**: Email verification for new accounts
- **Contains**: Verification link

### 2. Password Reset Email
- **File**: `resources/views/emails/reset-password.blade.php`
- **Purpose**: Password reset requests
- **Contains**: Reset link with token

### 3. New Login Notification
- **File**: `resources/views/emails/new-login.blade.php`
- **Purpose**: Alert on new device login
- **Contains**: Login details (IP, device, location)

---

## Troubleshooting

### Email Not Received?

**1. Check Configuration**:
```bash
php artisan test --filter=test_email_configuration_is_valid
```

**2. Check Logs**:
```bash
tail -f storage/logs/laravel.log | grep -i mail
```

**3. Test SMTP Connection**:
```bash
php artisan tinker

Mail::raw('Test email from Laravel', function($message) {
    $message->to('your-email@example.com')
            ->subject('Test Email');
});

echo "Check your inbox!";
```

### Common Issues

**Issue 1: "Connection refused"**
- Check SMTP host and port
- Verify firewall allows outbound connections
- Try different SMTP server

**Issue 2: "Authentication failed"**
- For Gmail: use App Password, not regular password
- Check username and password in .env
- Enable "Less secure app access" (Gmail)

**Issue 3: "Email goes to spam"**
- Add SPF records to your domain
- Use verified sender email
- Avoid spam trigger words

**Issue 4: "SSL certificate problem"**
- Set `MAIL_ENCRYPTION=tls` instead of `ssl`
- Or add to .env: `MAIL_VERIFY_PEER=false` (not recommended for production)

---

## Gmail Configuration

### Get Gmail App Password

1. Go to: https://myaccount.google.com/apppasswords
2. Create new app password for "Mail"
3. Copy the 16-character password
4. Use in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-char-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Advanced Coupon System"
```

---

## Testing Workflow

### Step-by-Step

1. **Configure Test Email**:
```php
// In EmailNotificationTest.php
private const TEST_EMAIL = 'mo@example.com'; // Your email
```

2. **Test Configuration**:
```bash
php artisan test --filter=test_email_configuration_is_valid
```

3. **Test Fake Emails** (verify logic works):
```bash
php artisan test --filter=test_can_send_verification_email
php artisan test --filter=test_can_send_reset_password_email
php artisan test --filter=test_can_send_new_login_notification
```

4. **Send Real Test Email**:
```php
// In test file, comment out this line:
// $this->markTestSkipped('Skipped by default. Enable to send real email.');
```

```bash
php artisan test --filter=test_send_actual_verification_email
```

5. **Check Inbox** 📧
- Wait 1-2 minutes
- Check spam folder
- Look for email from your configured sender

---

## Advanced Testing

### Send Custom Test Email

Create a temporary test:

```php
public function test_send_custom_email(): void
{
    Mail::raw('This is a test email from Advanced Coupon System!', function($message) {
        $message->to(self::TEST_EMAIL)
                ->subject('🧪 Test Email - ' . now()->format('Y-m-d H:i:s'));
    });
    
    echo "\n✅ Custom email sent to: " . self::TEST_EMAIL . "\n";
    
    $this->assertTrue(true);
}
```

Run:
```bash
php artisan test --filter=test_send_custom_email
```

### Test with Multiple Recipients

```php
public function test_send_to_multiple_recipients(): void
{
    $recipients = [
        'email1@example.com',
        'email2@example.com',
        self::TEST_EMAIL,
    ];
    
    foreach ($recipients as $email) {
        Mail::raw("Test email for {$email}", function($message) use ($email) {
            $message->to($email)->subject('Test Email');
        });
    }
    
    echo "\n✅ Sent to " . count($recipients) . " recipients!\n";
}
```

---

## Email Queue Testing

### If Using Queues

```env
QUEUE_CONNECTION=database
```

Test queued emails:

```php
public function test_email_is_queued(): void
{
    Queue::fake();
    
    $user = User::factory()->create(['email' => self::TEST_EMAIL]);
    $user->notify(new CustomVerifyEmail());
    
    Queue::assertPushed(SendEmailJob::class);
}
```

Process queue:
```bash
php artisan queue:work
```

---

## Monitoring

### View Mail Logs

```bash
# Real-time log monitoring
tail -f storage/logs/laravel.log | grep -i "mail\|email"

# View last 50 mail-related logs
grep -i "mail\|email" storage/logs/laravel.log | tail -50
```

### Enable Mail Logging

Add to `.env`:
```env
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

---

## Production Recommendations

### 1. Use Mail Service

- **SendGrid**: Free tier (100 emails/day)
- **Mailgun**: Free tier (5,000 emails/month)
- **Amazon SES**: Very cheap ($0.10 per 1000 emails)

### 2. Queue Emails

```env
QUEUE_CONNECTION=redis  # or database
```

All notifications will be queued automatically.

### 3. Add Email Verification

```php
// In User model (already implemented)
implements MustVerifyEmail
```

### 4. Rate Limiting

Prevent spam:
```php
RateLimiter::for('emails', function ($job) {
    return Limit::perMinute(10);
});
```

---

## Quick Reference

| Command | Purpose | Sends Real Email? |
|---------|---------|-------------------|
| `php artisan test --filter=test_email_configuration_is_valid` | Check config | ❌ No |
| `php artisan test --filter=test_can_send_verification_email` | Test logic | ❌ No (faked) |
| `php artisan test --filter=test_send_actual_verification_email` | Send real email | ✅ Yes (if enabled) |
| `php artisan test tests/Feature/EmailNotificationTest.php` | Run all tests | Depends on what's enabled |

---

## Example Output

### Successful Test Run

```bash
$ php artisan test tests/Feature/EmailNotificationTest.php

   PASS  Tests\Feature\EmailNotificationTest
  ✓ can send verification email                      0.15s
  ✓ can send reset password email                    0.10s
  ✓ can send new login notification                  0.08s
  ✓ email configuration is valid                     0.02s
  - send actual verification email (skipped)         0.00s
  - send actual reset password email (skipped)       0.00s
  - send actual login notification (skipped)         0.00s

  Tests:    4 passed, 3 skipped (7 total)
  Duration: 0.35s
```

---

## Support

### Get Help

If tests fail:

1. Check `.env` configuration
2. Review logs: `storage/logs/laravel.log`
3. Test SMTP manually with telnet
4. Verify network allows SMTP connections
5. Try different mail service (Mailtrap for testing)

### Using Mailtrap for Testing

Free email testing service:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
```

All emails will be caught by Mailtrap (won't be delivered).

---

**Last Updated**: October 12, 2025
**Version**: 1.0.0

