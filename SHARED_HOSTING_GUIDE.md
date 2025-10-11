# 🌐 دليل تثبيت المشروع على Shared Hosting

## ⚠️ ملاحظة مهمة
أنت على **Shared Hosting** وليس VPS، لذلك لا يمكنك:
- استخدام `sudo`
- تثبيت Node.js مباشرة
- تعديل إعدادات Nginx/Apache

**الحل:** بناء Assets على جهازك المحلي ثم رفعها للسيرفر!

---

## 📋 الخطوات على جهازك المحلي (قبل الرفع)

### 1. بناء Assets للإنتاج

```bash
# على جهازك المحلي
cd /Users/mo/Documents/project/AdvancedCouponSystem

# تثبيت Dependencies
npm install

# بناء Assets للإنتاج
npm run build

# التحقق من وجود ملفات Build
ls -la public/build/
```

يجب أن ترى:
```
public/build/
├── manifest.json
├── assets/
│   ├── app-[hash].js
│   ├── app-[hash].css
│   ├── icons-[hash].css
│   └── ... (ملفات أخرى)
```

### 2. تحضير المشروع للرفع

```bash
# على جهازك المحلي

# حذف node_modules (لن نحتاجها على السيرفر)
rm -rf node_modules

# حذف ملفات التطوير
rm -rf tests/

# التأكد من وجود .env.example
cp .env .env.example

# إزالة معلومات حساسة من .env.example
nano .env.example
# احذف: DB_PASSWORD, APP_KEY, وأي معلومات حساسة
```

### 3. ضغط المشروع

```bash
# على جهازك المحلي
cd /Users/mo/Documents/project

# ضغط المشروع (بدون .git و node_modules)
zip -r AdvancedCouponSystem.zip AdvancedCouponSystem \
  -x "*.git*" \
  -x "*node_modules*" \
  -x "*tests*" \
  -x "*.env"

# أو باستخدام tar
tar -czf AdvancedCouponSystem.tar.gz \
  --exclude='.git' \
  --exclude='node_modules' \
  --exclude='tests' \
  --exclude='.env' \
  AdvancedCouponSystem/
```

---

## 🚀 الخطوات على Shared Hosting

### 1. رفع الملفات

**عبر FTP/SFTP:**
```
استخدم FileZilla أو WinSCP:
- Host: ftp.yourdomain.com
- Username: u711828393
- Password: your_password
- Port: 21 (FTP) أو 22 (SFTP)

رفع الملف المضغوط إلى:
/home/u711828393/public_html/
```

**أو عبر SSH:**
```bash
# على جهازك المحلي
scp AdvancedCouponSystem.zip u711828393@de-fra-web1812:/home/u711828393/public_html/

# أو rsync
rsync -avz --exclude '.git' --exclude 'node_modules' \
  AdvancedCouponSystem/ \
  u711828393@de-fra-web1812:/home/u711828393/public_html/
```

### 2. فك الضغط على السيرفر

```bash
# تسجيل الدخول للسيرفر
ssh u711828393@de-fra-web1812

# الانتقال للمجلد
cd ~/public_html

# فك الضغط
unzip AdvancedCouponSystem.zip

# أو إذا كان tar
tar -xzf AdvancedCouponSystem.tar.gz

# حذف الملف المضغوط
rm AdvancedCouponSystem.zip

# الدخول للمشروع
cd AdvancedCouponSystem
```

### 3. إعداد .env

```bash
# نسخ .env
cp .env.example .env

# تعديل .env
nano .env
```

**أهم الإعدادات في .env:**
```env
APP_NAME="Advanced Coupon System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u711828393_coupon_db  # اسم قاعدة البيانات من cPanel
DB_USERNAME=u711828393_coupon    # اسم المستخدم من cPanel
DB_PASSWORD=YourPassword          # كلمة المرور من cPanel

QUEUE_CONNECTION=database
SESSION_DRIVER=file
CACHE_DRIVER=file
```

### 4. تثبيت Composer Dependencies

```bash
cd ~/public_html/AdvancedCouponSystem

# تثبيت Composer Dependencies (بدون dev)
composer install --optimize-autoloader --no-dev
```

### 5. إعداد Laravel

```bash
# توليد Application Key
php artisan key:generate

# تشغيل Migrations
php artisan migrate --force

# إنشاء Storage Link
php artisan storage:link

# Optimize للإنتاج
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize
```

### 6. إعداد الصلاحيات

```bash
# إعطاء صلاحيات للمجلدات
chmod -R 755 ~/public_html/AdvancedCouponSystem
chmod -R 775 ~/public_html/AdvancedCouponSystem/storage
chmod -R 775 ~/public_html/AdvancedCouponSystem/bootstrap/cache
chmod 600 ~/public_html/AdvancedCouponSystem/.env
```

### 7. ربط Domain بمجلد public

في **cPanel**:

#### الطريقة الأولى - عبر cPanel (الأسهل):
1. اذهب إلى **Domains** أو **Addon Domains**
2. أضف Domain جديد
3. في **Document Root** ضع: `public_html/AdvancedCouponSystem/public`

#### الطريقة الثانية - عبر .htaccess:
إذا كان Domain الرئيسي:

```bash
# في public_html
nano .htaccess
```

أضف:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ AdvancedCouponSystem/public/$1 [L]
</IfModule>
```

### 8. إنشاء أول مستخدم Admin

```bash
cd ~/public_html/AdvancedCouponSystem

# استخدام Tinker
php artisan tinker
```

```php
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@yourdomain.com';
$user->password = Hash::make('AdminPassword123!');
$user->save();
exit
```

---

## 🔄 إعداد Cron Jobs عبر cPanel

1. اذهب إلى **Cron Jobs** في cPanel
2. أضف Cron Job جديد:
   - **Minute**: `*`
   - **Hour**: `*`
   - **Day**: `*`
   - **Month**: `*`
   - **Weekday**: `*`
   - **Command**: 
     ```
     cd /home/u711828393/public_html/AdvancedCouponSystem && php artisan schedule:run >> /dev/null 2>&1
     ```

---

## ⚙️ إعداد Queue Worker عبر cPanel

في **Shared Hosting**، لا يمكنك استخدام Supervisor. **الحلول البديلة:**

### الحل 1: استخدام Cron لتشغيل Queue كل دقيقة

أضف Cron Job:
```
* * * * * cd /home/u711828393/public_html/AdvancedCouponSystem && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

### الحل 2: تغيير Queue Connection إلى sync

في `.env`:
```env
QUEUE_CONNECTION=sync
```

⚠️ **ملاحظة**: `sync` يعني تنفيذ Jobs فوراً بدون queue (أبطأ لكن يعمل على Shared Hosting)

---

## 📁 هيكل المجلدات النهائي على السيرفر

```
/home/u711828393/
└── public_html/
    └── AdvancedCouponSystem/
        ├── app/
        ├── bootstrap/
        ├── config/
        ├── database/
        ├── public/          ← هذا يجب أن يكون Document Root
        │   ├── build/       ← Assets المبنية
        │   ├── index.php
        │   └── ...
        ├── resources/
        ├── routes/
        ├── storage/
        ├── vendor/
        ├── .env
        └── ...
```

---

## 🔍 التحقق من التثبيت

### 1. افتح المتصفح:
```
https://yourdomain.com
```

يجب أن ترى صفحة تسجيل الدخول ✅

### 2. تسجيل الدخول:
```
Email: admin@yourdomain.com
Password: AdminPassword123!
```

### 3. التحقق من Assets:
افتح DevTools (F12) → Network
- يجب ألا ترى أخطاء 404 في CSS/JS
- جميع الملفات يجب أن تحمّل من `/build/assets/`

---

## 🐛 مشاكل شائعة وحلولها

### المشكلة: 500 Internal Server Error

```bash
# 1. تحقق من Logs
tail -50 ~/public_html/AdvancedCouponSystem/storage/logs/laravel.log

# 2. تحقق من الصلاحيات
chmod -R 775 storage bootstrap/cache

# 3. امسح Cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### المشكلة: Styles لا تظهر (CSS/JS مفقود)

**السبب:** Assets لم تُبنى أو لم تُرفع

**الحل:**
```bash
# على جهازك المحلي
npm run build

# ثم ارفع مجلد public/build كامل للسيرفر
# عبر FTP: ارفع public/build/ إلى public_html/AdvancedCouponSystem/public/build/
```

### المشكلة: Class 'Spatie\Permission\...' not found

```bash
# تثبيت Dependencies كاملة
composer install --optimize-autoloader --no-dev

# أو إذا لم يعمل
composer update --no-dev
```

### المشكلة: SQLSTATE[HY000] [1045] Access denied

```bash
# تحقق من بيانات قاعدة البيانات في .env
cat .env | grep DB_

# تحقق من وجود قاعدة البيانات في cPanel → MySQL Databases
```

---

## 📦 طريقة سريعة: رفع المشروع الجاهز

### على جهازك المحلي:

```bash
cd /Users/mo/Documents/project/AdvancedCouponSystem

# 1. بناء Assets
npm run build

# 2. Optimize Composer
composer install --optimize-autoloader --no-dev

# 3. إنشاء حزمة جاهزة للرفع
tar -czf deploy-ready.tar.gz \
  --exclude='.git' \
  --exclude='node_modules' \
  --exclude='tests' \
  --exclude='.env' \
  --exclude='storage/logs/*' \
  --exclude='storage/framework/cache/*' \
  --exclude='storage/framework/sessions/*' \
  --exclude='storage/framework/views/*' \
  .

# أو zip
zip -r deploy-ready.zip . \
  -x "*.git*" \
  -x "*node_modules*" \
  -x "*tests*" \
  -x ".env" \
  -x "storage/logs/*" \
  -x "storage/framework/cache/*"
```

### على السيرفر:

```bash
# 1. رفع الملف عبر FTP أو:
# scp deploy-ready.tar.gz u711828393@de-fra-web1812:~/public_html/

# 2. فك الضغط
cd ~/public_html
tar -xzf deploy-ready.tar.gz
# أو: unzip deploy-ready.zip

# 3. إعداد .env
cp .env.example .env
nano .env  # أدخل بيانات قاعدة البيانات

# 4. تشغيل Laravel
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize

# 5. الصلاحيات
chmod -R 755 .
chmod -R 775 storage bootstrap/cache
chmod 600 .env
```

---

## 🎯 إعداد Domain في cPanel

### 1. إعداد Document Root:

في cPanel → **Domains** → **yourdomain.com**:

**Document Root** اضبطه على:
```
public_html/AdvancedCouponSystem/public
```

أو إذا كان الدومين الرئيسي:

### 2. إنشاء .htaccess في public_html:

```bash
cd ~/public_html
nano .htaccess
```

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Redirect to Laravel public folder
    RewriteCond %{REQUEST_URI} !^/AdvancedCouponSystem/public
    RewriteRule ^(.*)$ AdvancedCouponSystem/public/$1 [L]
</IfModule>
```

---

## 🔧 إعداد قاعدة البيانات في cPanel

### 1. إنشاء قاعدة البيانات:

1. اذهب إلى **MySQL Databases** في cPanel
2. أنشئ قاعدة بيانات جديدة:
   - Database Name: `coupon_system`
   - Full Name: `u711828393_coupon_system`

### 2. إنشاء مستخدم:

1. في نفس الصفحة، قسم **MySQL Users**
2. أنشئ مستخدم:
   - Username: `coupon_user`
   - Password: كلمة مرور قوية
   - Full Name: `u711828393_coupon_user`

### 3. ربط المستخدم بقاعدة البيانات:

1. في قسم **Add User To Database**
2. اختر المستخدم والقاعدة
3. أعط **ALL PRIVILEGES**

### 4. تحديث .env:

```env
DB_DATABASE=u711828393_coupon_system
DB_USERNAME=u711828393_coupon_user
DB_PASSWORD=YourStrongPassword
```

---

## 🔄 كيفية التحديث (Update)

### على جهازك المحلي:

```bash
cd /Users/mo/Documents/project/AdvancedCouponSystem

# 1. سحب آخر تحديثات (إذا Git)
git pull

# 2. تحديث Dependencies
composer install --optimize-autoloader --no-dev
npm install

# 3. بناء Assets
npm run build

# 4. إنشاء حزمة Update
# رفع فقط الملفات المحدثة:
# - public/build/ (Assets الجديدة)
# - app/
# - resources/views/
# - database/migrations/ (إذا كانت هناك migrations جديدة)
# - routes/
# - composer.lock
# - package.json
```

### على السيرفر:

```bash
cd ~/public_html/AdvancedCouponSystem

# 1. وضع الصيانة
php artisan down

# 2. رفع الملفات الجديدة عبر FTP
# (استبدل الملفات القديمة)

# 3. تحديث Composer (إذا تغير composer.lock)
composer install --optimize-autoloader --no-dev

# 4. تشغيل Migrations الجديدة
php artisan migrate --force

# 5. مسح Cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. إلغاء وضع الصيانة
php artisan up
```

---

## ✅ Checklist للـ Shared Hosting

- [ ] بناء Assets على جهازك المحلي (npm run build)
- [ ] رفع المشروع كامل مع مجلد `public/build/`
- [ ] إنشاء قاعدة البيانات في cPanel
- [ ] نسخ وتعديل .env
- [ ] تشغيل `php artisan key:generate`
- [ ] تشغيل `php artisan migrate --force`
- [ ] تشغيل `php artisan storage:link`
- [ ] تشغيل `php artisan optimize`
- [ ] ضبط Document Root على `public_html/AdvancedCouponSystem/public`
- [ ] ضبط الصلاحيات (755 للملفات، 775 للـ storage)
- [ ] إعداد Cron Job للـ schedule:run
- [ ] إنشاء مستخدم Admin عبر tinker
- [ ] اختبار الموقع في المتصفح

---

## 🎬 الأوامر الكاملة (نسخ ولصق)

```bash
# بعد رفع وفك ضغط المشروع:

cd ~/public_html/AdvancedCouponSystem

# Setup
cp .env.example .env
nano .env  # أدخل بيانات DB

# Laravel Setup
composer install --optimize-autoloader --no-dev
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Permissions
chmod -R 755 .
chmod -R 775 storage bootstrap/cache
chmod 600 .env

# Create Admin User
php artisan tinker
```

في Tinker:
```php
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@yourdomain.com';
$user->password = Hash::make('YourPassword123!');
$user->save();
exit
```

```bash
# Done!
echo "✅ Installation Complete!"
echo "Visit: https://yourdomain.com"
```

---

## 🌟 نصائح مهمة للـ Shared Hosting

1. **دائماً ابنِ Assets محلياً** ثم ارفعها
2. **لا تنسَ رفع مجلد `public/build/`** كامل
3. **استخدم FTP/SFTP** لرفع الملفات بشكل موثوق
4. **اضبط QUEUE_CONNECTION=sync** إذا لم يكن Supervisor متاح
5. **راقب Disk Space** - Shared Hosting له حد معين
6. **استخدم .htaccess** للتحكم في URL Rewriting
7. **فعّل Gzip** من cPanel → Optimize Website
8. **استخدم CloudFlare** لتحسين الأداء (مجاني)

---

## 📞 الدعم

إذا واجهت مشاكل:
1. تحقق من `storage/logs/laravel.log`
2. تحقق من Error Log في cPanel
3. تأكد من رفع `public/build/` كامل
4. تأكد من صحة بيانات .env

**العنوان:** https://yourdomain.com  
**Admin Login:** admin@yourdomain.com

