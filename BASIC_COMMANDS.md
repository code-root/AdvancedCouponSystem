# ⚡ الأوامر الأساسية السريعة

## 🚀 التثبيت السريع (خطوة بخطوة)

### 1. رفع المشروع للسيرفر
```bash
cd /var/www
# رفع المشروع هنا (عبر Git أو FTP)
```

### 2. تثبيت Dependencies
```bash
cd /var/www/AdvancedCouponSystem

# تثبيت Composer
composer install --optimize-autoloader --no-dev

# تثبيت NPM
npm install

# بناء Assets
npm run build
```

### 3. إعداد قاعدة البيانات
```bash
# نسخ .env
cp .env.example .env

# تعديل .env (أدخل بيانات قاعدة البيانات)
nano .env
```

### 4. إعداد Laravel
```bash
# توليد Key
php artisan key:generate

# تشغيل Migrations
php artisan migrate --force

# إنشاء Storage Link
php artisan storage:link

# تحسين للإنتاج
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. إعداد الصلاحيات
```bash
sudo chown -R www-data:www-data /var/www/AdvancedCouponSystem
sudo chmod -R 755 /var/www/AdvancedCouponSystem
sudo chmod -R 775 storage bootstrap/cache
```

### 6. إعداد Nginx
```bash
sudo nano /etc/nginx/sites-available/advanced-coupon-system
# أضف الإعدادات من DEPLOYMENT_GUIDE.md

sudo ln -s /etc/nginx/sites-available/advanced-coupon-system /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 7. إعداد Queue Worker
```bash
sudo nano /etc/supervisor/conf.d/advanced-coupon-queue.conf
# أضف الإعدادات من DEPLOYMENT_GUIDE.md

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start advanced-coupon-queue:*
```

### 8. إعداد Cron
```bash
sudo crontab -e -u www-data
# أضف: * * * * * cd /var/www/AdvancedCouponSystem && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔄 أوامر التحديث

```bash
cd /var/www/AdvancedCouponSystem

# 1. وضع الصيانة
php artisan down

# 2. سحب التحديثات (Git)
git pull origin main

# 3. تحديث Dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# 4. تشغيل Migrations
php artisan migrate --force

# 5. مسح Cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. إعادة تشغيل Workers
sudo supervisorctl restart advanced-coupon-queue:*

# 7. إلغاء وضع الصيانة
php artisan up

# 8. إعادة تشغيل Services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

---

## 🛠️ أوامر الصيانة اليومية

### مسح Cache:
```bash
cd /var/www/AdvancedCouponSystem
php artisan optimize:clear
```

### مراقبة Logs:
```bash
tail -f storage/logs/laravel.log
```

### التحقق من Queue:
```bash
sudo supervisorctl status
```

### إعادة تشغيل Services:
```bash
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
sudo supervisorctl restart all
```

---

## 📦 إنشاء مستخدم Admin

```bash
cd /var/www/AdvancedCouponSystem
php artisan tinker
```

في Tinker:
```php
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@yourdomain.com';
$user->password = Hash::make('YourStrongPassword123!');
$user->save();
exit
```

---

## 🔒 تثبيت SSL (Let's Encrypt)

```bash
# تثبيت Certbot
sudo apt install -y certbot python3-certbot-nginx

# الحصول على شهادة
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# تجديد تلقائي
sudo systemctl enable certbot.timer
```

---

## 🐛 استكشاف الأخطاء

### خطأ 500:
```bash
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/error.log
sudo chmod -R 775 storage bootstrap/cache
```

### Assets لا تظهر:
```bash
npm run build
php artisan view:clear
sudo systemctl restart nginx
```

### Queue لا يعمل:
```bash
sudo supervisorctl restart advanced-coupon-queue:*
tail -f storage/logs/queue-worker.log
```

---

## 📊 الأوامر المفيدة

```bash
# معلومات النظام
php artisan about

# مسح كل Cache
php artisan optimize:clear

# إعادة بناء كل Cache
php artisan optimize

# التحقق من Routes
php artisan route:list

# التحقق من Migrations
php artisan migrate:status

# عمل Backup لقاعدة البيانات
mysqldump -u coupon_user -p advanced_coupon_system > backup_$(date +%Y%m%d).sql
```

