# Email Testing Commands - دليل سريع 📧

## الأوامر المتاحة 🛠️

### 1. إرسال Test Email بسيط (أسرع طريقة)

```bash
php artisan email:test your-email@example.com
```

**أو بدون تحديد الإيميل** (سيطلب منك):
```bash
php artisan email:test
```

---

### 2. أنواع الإيميلات المختلفة 📨

#### أ) Verification Email (افتراضي):
```bash
php artisan email:test mo@example.com --type=verify
```

#### ب) Password Reset Email:
```bash
php artisan email:test mo@example.com --type=reset
```

#### ج) Login Notification:
```bash
php artisan email:test mo@example.com --type=login
```

#### د) Simple Test Email:
```bash
php artisan email:test mo@example.com --type=simple
```

---

## Unit Tests 🧪

### 1. اختبار Configuration فقط (آمن):
```bash
php artisan test --filter=test_email_configuration_is_valid
```

**Output**:
```
✅ Email configuration valid!
📧 From: trakifi <info@trakifi.com>
🚀 Driver: smtp
```

---

### 2. اختبار جميع الإيميلات (Fake - لا يرسل):
```bash
php artisan test tests/Feature/EmailNotificationTest.php
```

**Output**:
```
✅ Verification email test passed!
✅ Reset password email test passed!
✅ New login notification test passed!
✅ Email configuration valid!

Tests: 4 passed, 3 skipped
```

---

### 3. إرسال إيميل حقيقي (Real Email):

**الخطوة 1**: افتح الملف:
```bash
nano tests/Feature/EmailNotificationTest.php
```

**الخطوة 2**: غيّر الإيميل:
```php
private const TEST_EMAIL = 'mo@example.com'; // ← ضع إيميلك هنا
```

**الخطوة 3**: قم بتفعيل الـ test بإزالة التعليق:
```php
// Before (معطل):
$this->markTestSkipped('Skipped by default...');

// After (مُفعّل):
// $this->markTestSkipped('Skipped by default...');
```

**الخطوة 4**: شغّل الـ test:
```bash
php artisan test --filter=test_send_actual_verification_email
```

---

## أمثلة سريعة 🚀

### مثال 1: اختبار سريع
```bash
# إرسال test email لإيميلك
php artisan email:test mo@gmail.com --type=simple
```

### مثال 2: اختبار Verification
```bash
# إرسال verification email
php artisan email:test mo@gmail.com --type=verify

# تحقق من إيميلك
# اضغط على الرابط
```

### مثال 3: اختبار Password Reset
```bash
# إرسال password reset email
php artisan email:test mo@gmail.com --type=reset

# تحقق من إيميلك
# استخدم الـ token
```

### مثال 4: اختبار جميع الأنواع
```bash
# إرسال verification
php artisan email:test mo@gmail.com --type=verify

# إرسال reset
php artisan email:test mo@gmail.com --type=reset

# إرسال login notification
php artisan email:test mo@gmail.com --type=login

# إرسال simple
php artisan email:test mo@gmail.com --type=simple
```

---

## التحقق من الإيميل ✉️

### في Gmail:

1. ✅ افتح Gmail
2. ✅ ابحث عن: `from:info@trakifi.com`
3. ✅ تحقق من مجلد Spam أيضاً
4. ✅ يجب أن تجد الإيميل خلال 1-2 دقيقة

### في إيميل آخر:

1. ✅ افتح بريدك الإلكتروني
2. ✅ ابحث عن المرسل: `trakifi` أو `info@trakifi.com`
3. ✅ تحقق من Junk/Spam
4. ✅ انتظر 1-3 دقائق

---

## Troubleshooting 🔧

### المشكلة: لم يصل الإيميل

**الحل 1**: تحقق من Configuration
```bash
php artisan test --filter=test_email_configuration_is_valid
```

**الحل 2**: تحقق من Logs
```bash
tail -f storage/logs/laravel.log | grep -i mail
```

**الحل 3**: جرب إرسال بسيط
```bash
php artisan tinker

Mail::raw('Test', fn($m) => $m->to('mo@example.com')->subject('Test'));
```

---

### المشكلة: "Connection refused"

**الحل**: 
- تحقق من SMTP host و port في `.env`
- جرب port مختلف (587 أو 465)
- تأكد من أن الـ firewall لا يمنع الاتصال

```env
# جرّب هذه الإعدادات:
MAIL_PORT=587
MAIL_ENCRYPTION=tls

# أو:
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
```

---

### المشكلة: "Authentication failed"

**للـ Gmail**:
1. استخدم App Password وليس كلمة المرور العادية
2. اذهب لـ: https://myaccount.google.com/apppasswords
3. أنشئ password جديد
4. استخدمه في `.env`:

```env
MAIL_PASSWORD=abcd efgh ijkl mnop  # 16 حرف من Google
```

---

### المشكلة: الإيميل يذهب للـ Spam

**الحل**:
- استخدم domain حقيقي في `MAIL_FROM_ADDRESS`
- أضف SPF records لدومينك
- استخدم خدمة إيميل موثوقة (SendGrid, Mailgun)

---

## Configuration Examples 📝

### Gmail (مجاني):
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

### Mailtrap (للتطوير - يمسك الإيميلات):
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
```

### SendGrid (للإنتاج):
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Advanced Coupon System"
```

---

## Quick Commands Reference 📋

| Command | Purpose | Sends Email? |
|---------|---------|--------------|
| `php artisan email:test mo@example.com` | Send test email | ✅ Yes |
| `php artisan email:test mo@example.com --type=simple` | Simple test | ✅ Yes |
| `php artisan email:test mo@example.com --type=verify` | Verification | ✅ Yes |
| `php artisan email:test mo@example.com --type=reset` | Password reset | ✅ Yes |
| `php artisan email:test mo@example.com --type=login` | Login alert | ✅ Yes |
| `php artisan test --filter=test_email_configuration` | Check config | ❌ No |
| `php artisan test tests/Feature/EmailNotificationTest.php` | Run all tests | ❌ No (unless enabled) |

---

## Example Session 🎬

```bash
$ php artisan email:test

 Enter the email address to send test email to:
 > mo@gmail.com

📧 Preparing to send test email to: mo@gmail.com
📝 Email type: verify

📨 Sending verification email...
   → Verification email queued

✅ Email sent successfully!
📬 Check your inbox at: mo@gmail.com

⚠️  If you don't receive the email:
   - Check spam/junk folder
   - Verify .env email configuration
   - Check logs: tail -f storage/logs/laravel.log
```

---

## Tips 💡

### 1. Test Locally with Mailtrap

Free and safe for development:
- Sign up: https://mailtrap.io
- Use sandbox SMTP credentials
- All emails are caught (not delivered)

### 2. Monitor Logs

```bash
# Real-time monitoring
tail -f storage/logs/laravel.log | grep -i "mail\|email\|smtp"

# Check for errors
grep -i "error\|failed" storage/logs/laravel.log | grep -i "mail"
```

### 3. Test Queue

If using queues:
```bash
# Start queue worker
php artisan queue:work

# In another terminal, send test email
php artisan email:test mo@example.com
```

---

## الأوامر باللغة العربية 🇸🇦

### إرسال test email:
```bash
php artisan email:test mo@example.com
```

### إرسال verification email:
```bash
php artisan email:test mo@example.com --type=verify
```

### إرسال password reset:
```bash
php artisan email:test mo@example.com --type=reset
```

### اختبار Configuration:
```bash
php artisan test --filter=test_email_configuration_is_valid
```

---

**Last Updated**: October 12, 2025
**Version**: 1.0.0

