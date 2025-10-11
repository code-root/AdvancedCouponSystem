# 📱 خطوات التثبيت على Shared Hosting - مبسطة

## 🎯 الهدف
تشغيل المشروع على Shared Hosting الذي **لا يدعم** Node.js مباشرة.

---

## الجزء الأول: على جهازك (Local) 💻

### الخطوة 1: بناء Assets

```bash
cd /Users/mo/Documents/project/AdvancedCouponSystem

# تثبيت NPM packages
npm install

# بناء Assets للإنتاج
npm run build
```

✅ **النتيجة:** سيتم إنشاء مجلد `public/build/` يحتوي على جميع CSS و JS المطلوبة.

### الخطوة 2: تحضير Composer

```bash
# تثبيت Composer dependencies (بدون dev)
composer install --optimize-autoloader --no-dev
```

### الخطوة 3: إنشاء حزمة للرفع

**الطريقة الأولى - استخدام السكريبت:**
```bash
chmod +x build-for-deployment.sh
./build-for-deployment.sh
```

**الطريقة الثانية - يدوياً:**
```bash
cd /Users/mo/Documents/project

zip -r AdvancedCouponSystem-deploy.zip AdvancedCouponSystem \
  -x "*.git*" \
  -x "*node_modules*" \
  -x "*tests*" \
  -x "*.env"
```

---

## الجزء الثاني: على السيرفر (Hosting) 🌐

### الخطوة 1: رفع الملفات

**عبر cPanel File Manager:**
1. اذهب إلى cPanel → File Manager
2. انتقل إلى `public_html`
3. اضغط Upload
4. ارفع ملف `AdvancedCouponSystem-deploy.zip`
5. بعد الرفع، اضغط Extract

**عبر FTP (FileZilla):**
1. اتصل بالسيرفر:
   - Host: `ftp.yourdomain.com`
   - Username: `u711828393`
   - Port: `21`
2. ارفع المجلد كامل إلى `public_html/`

### الخطوة 2: فك الضغط (إذا رفعت ملف مضغوط)

```bash
# عبر SSH
ssh u711828393@de-fra-web1812

cd ~/public_html
unzip AdvancedCouponSystem-deploy.zip
cd AdvancedCouponSystem
```

### الخطوة 3: إعداد قاعدة البيانات في cPanel

1. **إنشاء Database:**
   - cPanel → MySQL Databases
   - اسم القاعدة: `coupon_system` → سيصبح `u711828393_coupon_system`
   - اضغط Create Database

2. **إنشاء User:**
   - في MySQL Users
   - Username: `coupon_user` → سيصبح `u711828393_coupon_user`
   - Password: كلمة مرور قوية
   - اضغط Create User

3. **ربط User بـ Database:**
   - في Add User To Database
   - اختر User و Database
   - اضغط Add
   - أعط ALL PRIVILEGES
   - اضغط Make Changes

### الخطوة 4: إعداد .env على السيرفر

```bash
cd ~/public_html/AdvancedCouponSystem

# نسخ .env
cp .env.example .env

# تعديل .env
nano .env
```

**عدّل هذه السطور:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_DATABASE=u711828393_coupon_system
DB_USERNAME=u711828393_coupon_user
DB_PASSWORD=كلمة_المرور_التي_أنشأتها
```

احفظ بـ `Ctrl+X` ثم `Y` ثم `Enter`

### الخطوة 5: إعداد Laravel

```bash
# توليد Application Key
php artisan key:generate

# تشغيل Migrations
php artisan migrate --force

# إنشاء Storage Link
php artisan storage:link

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### الخطوة 6: ضبط الصلاحيات

```bash
chmod -R 755 ~/public_html/AdvancedCouponSystem
chmod -R 775 ~/public_html/AdvancedCouponSystem/storage
chmod -R 775 ~/public_html/AdvancedCouponSystem/bootstrap/cache
chmod 600 ~/public_html/AdvancedCouponSystem/.env
```

### الخطوة 7: ضبط Document Root في cPanel

**إذا كان لديك Addon Domain:**
1. cPanel → Domains
2. اختر Domain الخاص بك
3. اضغط Manage
4. في Document Root ضع:
   ```
   public_html/AdvancedCouponSystem/public
   ```
5. احفظ

**إذا كان Main Domain:**
1. انتقل إلى `public_html`
2. أنشئ `.htaccess`:

```bash
cd ~/public_html
nano .htaccess
```

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/AdvancedCouponSystem/public
    RewriteRule ^(.*)$ AdvancedCouponSystem/public/$1 [L]
</IfModule>
```

### الخطوة 8: إعداد Cron Job

1. cPanel → Cron Jobs
2. أضف Cron Job جديد:
   - **Common Settings:** Once Per Minute (*/1)
   - **Command:**
     ```
     /usr/bin/php /home/u711828393/public_html/AdvancedCouponSystem/artisan schedule:run > /dev/null 2>&1
     ```
3. احفظ

### الخطوة 9: إنشاء Admin User

```bash
cd ~/public_html/AdvancedCouponSystem
php artisan tinker
```

```php
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@yourdomain.com';
$user->password = Hash::make('YourPassword123!');
$user->save();
exit
```

### الخطوة 10: اختبار الموقع

افتح: `https://yourdomain.com`

**تسجيل دخول:**
- Email: `admin@yourdomain.com`
- Password: `YourPassword123!`

---

## 🔥 حل سريع للمشاكل

### مشكلة: الموقع يعرض 500 Error

```bash
cd ~/public_html/AdvancedCouponSystem
tail -50 storage/logs/laravel.log
php artisan cache:clear
chmod -R 775 storage bootstrap/cache
```

### مشكلة: CSS/JS لا تظهر

**على جهازك المحلي:**
```bash
npm run build
```

ثم ارفع مجلد `public/build/` كامل عبر FTP إلى:
```
public_html/AdvancedCouponSystem/public/build/
```

### مشكلة: Database Connection Error

تحقق من:
```bash
cat .env | grep DB_
```

تأكد أن الأسماء تطابق ما في cPanel!

---

## ✅ قائمة التحقق النهائية

- [ ] ✅ بناء Assets على جهازك (npm run build)
- [ ] ✅ رفع المشروع للسيرفر
- [ ] ✅ رفع مجلد public/build/ كامل
- [ ] ✅ إنشاء Database في cPanel
- [ ] ✅ تعديل .env بمعلومات صحيحة
- [ ] ✅ php artisan key:generate
- [ ] ✅ php artisan migrate --force
- [ ] ✅ php artisan storage:link
- [ ] ✅ php artisan optimize
- [ ] ✅ ضبط الصلاحيات (775 للـ storage)
- [ ] ✅ ضبط Document Root
- [ ] ✅ إضافة Cron Job
- [ ] ✅ إنشاء Admin User
- [ ] ✅ اختبار الموقع

---

## 🎉 تم!

الموقع الآن يعمل على: **https://yourdomain.com**

