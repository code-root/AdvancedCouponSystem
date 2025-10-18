# حل مشكلة SMTP على السيرفر - Server SMTP Fix

## المشكلة 🔍

الخطأ يظهر على السيرفر وليس في البيئة المحلية:
```
Failed to authenticate on SMTP server with username "info@trakifi.com" 
using the following authenticators: "LOGIN", "PLAIN". 
Authenticator "LOGIN" returned "Expected response code "235" but got code "535", 
with message "535 Incorrect authentication data".
```

## الأسباب المحتملة على السيرفر 🖥️

1. **إعدادات .env مختلفة على السيرفر**
2. **كلمة مرور Gmail App Password غير صحيحة**
3. **مشاكل في الشبكة أو Firewall**
4. **إعدادات PHP أو SSL مختلفة**
5. **متغيرات البيئة غير محددة بشكل صحيح**

---

## الحلول السريعة ⚡

### الحل 1: تحديث إعدادات السيرفر

#### A. تحقق من ملف .env على السيرفر

```bash
# على السيرفر
cat .env | grep MAIL_
```

يجب أن تكون الإعدادات كالتالي:
```env
MAIL_MAILER=gmail
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=info@trakifi.com
MAIL_PASSWORD="gpag evdp tazg gjjr"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@trakifi.com
MAIL_FROM_NAME="Trakifi"
MAIL_TIMEOUT=60
MAIL_VERIFY_PEER=false
MAIL_ALLOW_SELF_SIGNED=true
```

#### B. إعادة إنشاء App Password

1. **اذهب إلى Gmail**:
   - https://myaccount.google.com/apppasswords
   - احذف App Password القديم
   - أنشئ App Password جديد

2. **تحديث .env على السيرفر**:
```bash
# على السيرفر
nano .env
# أو
vim .env
```

3. **تحديث كلمة المرور**:
```env
MAIL_PASSWORD="your-new-16-character-app-password"
```

#### C. مسح Cache

```bash
# على السيرفر
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

---

### الحل 2: استخدام SendGrid (موصى به للسيرفر)

#### A. إنشاء حساب SendGrid

1. **التسجيل**: https://sendgrid.com
2. **إنشاء API Key**:
   - Dashboard → Settings → API Keys
   - Create API Key → Full Access
   - انسخ الـ API Key

#### B. تحديث .env على السيرفر

```env
MAIL_MAILER=sendgrid
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=SG.your-sendgrid-api-key-here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@trakifi.com
MAIL_FROM_NAME="Trakifi"
MAIL_TIMEOUT=60
MAIL_VERIFY_PEER=false
MAIL_ALLOW_SELF_SIGNED=true
SENDGRID_API_KEY=SG.your-sendgrid-api-key-here
```

#### C. مسح Cache وإعادة التشغيل

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

---

### الحل 3: استخدام Mailgun

#### A. إنشاء حساب Mailgun

1. **التسجيل**: https://mailgun.com
2. **الحصول على SMTP Credentials**:
   - Sending → Domain Settings → SMTP Credentials

#### B. تحديث .env على السيرفر

```env
MAIL_MAILER=mailgun
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@yourdomain.mailgun.org
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@trakifi.com
MAIL_FROM_NAME="Trakifi"
MAIL_TIMEOUT=60
MAIL_VERIFY_PEER=false
MAIL_ALLOW_SELF_SIGNED=true
MAILGUN_USERNAME=postmaster@yourdomain.mailgun.org
MAILGUN_PASSWORD=your-mailgun-password
```

---

## اختبار السيرفر 🧪

### 1. اختبار الإعدادات

```bash
# على السيرفر
php artisan email:test --config
```

### 2. اختبار إرسال إيميل

```bash
# على السيرفر
php artisan email:test your-email@example.com
```

### 3. اختبار إيميل التحقق

```bash
# على السيرفر
php artisan tinker
```

```php
$user = App\Models\User::first();
$user->sendEmailVerificationNotification();
echo "Email sent to: " . $user->email;
```

---

## استكشاف الأخطاء على السيرفر 🔍

### 1. فحص السجلات

```bash
# على السيرفر
tail -f storage/logs/laravel.log | grep -i mail
```

### 2. فحص إعدادات PHP

```bash
# على السيرفر
php -m | grep -i openssl
php -m | grep -i curl
```

### 3. اختبار الاتصال

```bash
# على السيرفر
telnet smtp.gmail.com 587
# أو
telnet smtp.sendgrid.net 587
```

### 4. فحص متغيرات البيئة

```bash
# على السيرفر
php artisan tinker
```

```php
echo env('MAIL_USERNAME');
echo env('MAIL_PASSWORD');
echo config('mail.default');
```

---

## حلول متقدمة للسيرفر 🔧

### 1. إعدادات SSL محسنة

```env
MAIL_VERIFY_PEER=false
MAIL_ALLOW_SELF_SIGNED=true
MAIL_TIMEOUT=120
```

### 2. إعدادات PHP محسنة

في `php.ini`:
```ini
openssl.cafile=/etc/ssl/certs/ca-certificates.crt
allow_url_fopen=On
```

### 3. إعدادات Firewall

```bash
# السماح بالاتصال بـ SMTP
sudo ufw allow out 587
sudo ufw allow out 465
```

### 4. إعدادات DNS

تأكد من أن السيرفر يمكنه الوصول إلى:
- smtp.gmail.com
- smtp.sendgrid.net
- smtp.mailgun.org

---

## أوامر سريعة للسيرفر ⚡

### إنشاء ملف .env جديد

```bash
# على السيرفر
php artisan email:setup --service=sendgrid --force
```

### اختبار شامل

```bash
# على السيرفر
php artisan email:test your-email@example.com --driver=sendgrid
```

### مسح جميع الـ Cache

```bash
# على السيرفر
php artisan optimize:clear
php artisan config:cache
```

---

## أفضل الممارسات للسيرفر 📋

### 1. استخدام خدمة إيميلات موثوقة

```env
# للإنتاج - استخدم SendGrid أو Mailgun
MAIL_MAILER=sendgrid
```

### 2. تفعيل Queue للإيميلات

```env
QUEUE_CONNECTION=database
```

```bash
# تشغيل Queue Worker
php artisan queue:work --daemon
```

### 3. مراقبة السجلات

```bash
# مراقبة مستمرة
tail -f storage/logs/laravel.log | grep -i "mail\|email"
```

### 4. نسخ احتياطي للإعدادات

```bash
# نسخ احتياطي لملف .env
cp .env .env.backup
```

---

## حلول الطوارئ 🚨

### 1. استخدام Mailtrap للاختبار

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
```

### 2. تعطيل إيميلات التحقق مؤقتاً

في `app/Models/User.php`:
```php
public function sendEmailVerificationNotification()
{
    // تعطيل مؤقت
    return;
    
    $this->notify(new \App\Notifications\CustomVerifyEmail);
}
```

### 3. استخدام Log Driver

```env
MAIL_MAILER=log
```

الإيميلات ستُحفظ في `storage/logs/laravel.log`

---

## ملخص الحلول 🎯

| الحل | السهولة | الموثوقية | التكلفة |
|------|---------|-----------|---------|
| **إصلاح Gmail** | ⭐⭐⭐ | ⭐⭐ | مجاني |
| **SendGrid** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | 100 إيميل/يوم مجاناً |
| **Mailgun** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | 5,000 إيميل/شهر مجاناً |
| **Mailtrap** | ⭐⭐⭐⭐⭐ | ⭐ | مجاني للاختبار |

---

## خطوات سريعة للحل 🚀

### الخطوة 1: اختبار الإعدادات الحالية

```bash
php artisan email:test --config
```

### الخطوة 2: إذا فشل Gmail، جرب SendGrid

```bash
php artisan email:setup --service=sendgrid --force
```

### الخطوة 3: اختبار الإرسال

```bash
php artisan email:test your-email@example.com
```

### الخطوة 4: مسح Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

---

**آخر تحديث**: ديسمبر 2024  
**الإصدار**: 1.0.0


