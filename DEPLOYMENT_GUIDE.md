# 🚀 دليل تثبيت AdvancedCouponSystem على السيرفر

## 📋 المتطلبات الأساسية

### 1. متطلبات السيرفر:
- PHP >= 8.2
- Composer
- Node.js >= 18.x
- NPM >= 9.x
- MySQL >= 8.0 أو MariaDB >= 10.3
- Nginx أو Apache
- Supervisor (لتشغيل Queue Workers)

---

## 🔧 خطوات التثبيت

### 1️⃣ تحديث السيرفر وتثبيت المتطلبات

```bash
# تحديث النظام
sudo apt update && sudo apt upgrade -y

# تثبيت PHP 8.2 والإضافات المطلوبة
sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring \
    php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-intl \
    php8.2-bcmath php8.2-redis php8.2-imagick

# تثبيت Composer

curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# تثبيت Node.js 20.x LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# تحقق من الإصدارات
php -v
composer -V
node -v
npm -v
```

### 2️⃣ تثبيت وإعداد MySQL

```bash
# تثبيت MySQL
sudo apt install -y mysql-server

# تأمين MySQL
sudo mysql_secure_installation

# الدخول إلى MySQL
sudo mysql -u root -p

# إنشاء قاعدة البيانات والمستخدم
CREATE DATABASE advanced_coupon_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'coupon_user'@'localhost' IDENTIFIED BY 'StrongPassword123!@#';
GRANT ALL PRIVILEGES ON advanced_coupon_system.* TO 'coupon_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3️⃣ رفع المشروع إلى السيرفر

```bash
# الانتقال إلى مجلد الويب
cd /var/www

# استنساخ المشروع (أو رفعه عبر FTP/Git)
# إذا كان على GitHub:
git clone https://github.com/your-username/AdvancedCouponSystem.git
cd AdvancedCouponSystem

# أو إذا كنت ترفع ملف مضغوط:
# scp AdvancedCouponSystem.zip user@server:/var/www/
# cd /var/www
# unzip AdvancedCouponSystem.zip
# cd AdvancedCouponSystem

# إعطاء الصلاحيات المناسبة
sudo chown -R www-data:www-data /var/www/AdvancedCouponSystem
sudo chmod -R 755 /var/www/AdvancedCouponSystem
sudo chmod -R 775 /var/www/AdvancedCouponSystem/storage
sudo chmod -R 775 /var/www/AdvancedCouponSystem/bootstrap/cache
```

### 4️⃣ تثبيت Dependencies

```bash
cd /var/www/AdvancedCouponSystem

# تثبيت Composer Dependencies
composer install --optimize-autoloader --no-dev

# تثبيت NPM Dependencies
npm install

# بناء Assets للإنتاج
npm run build
```

### 5️⃣ إعداد ملف Environment

```bash
# نسخ ملف .env من المثال
cp .env.example .env

# تعديل ملف .env
nano .env
```

**محتويات .env المهمة:**

```env
APP_NAME="Advanced Coupon System"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=advanced_coupon_system
DB_USERNAME=coupon_user
DB_PASSWORD=StrongPassword123!@#

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Redis (اختياري - لأداء أفضل)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 6️⃣ توليد Application Key وإعداد قاعدة البيانات

```bash
# توليد Application Key
php artisan key:generate

# تشغيل Migrations
php artisan migrate --force

# تشغيل Seeders (إذا كانت موجودة)
php artisan db:seed --force

# إنشاء Storage Link
php artisan storage:link

# تحسين التطبيق للإنتاج
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 7️⃣ إعداد Nginx

```bash
# إنشاء ملف إعداد Nginx
sudo nano /etc/nginx/sites-available/advanced-coupon-system
```

**محتوى الملف:**

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/AdvancedCouponSystem/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

```bash
# تفعيل الموقع
sudo ln -s /etc/nginx/sites-available/advanced-coupon-system /etc/nginx/sites-enabled/

# حذف الموقع الافتراضي (اختياري)
sudo rm /etc/nginx/sites-enabled/default

# اختبار إعدادات Nginx
sudo nginx -t

# إعادة تشغيل Nginx
sudo systemctl restart nginx
```

### 8️⃣ إعداد SSL مع Let's Encrypt (اختياري لكن موصى به)

```bash
# تثبيت Certbot
sudo apt install -y certbot python3-certbot-nginx

# الحصول على شهادة SSL
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# تجديد تلقائي للشهادة
sudo systemctl enable certbot.timer
```

### 9️⃣ إعداد Queue Worker مع Supervisor

```bash
# تثبيت Supervisor
sudo apt install -y supervisor

# إنشاء ملف إعداد للـ Worker
sudo nano /etc/supervisor/conf.d/advanced-coupon-queue.conf
```

**محتوى الملف:**

```ini
[program:advanced-coupon-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/AdvancedCouponSystem/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/AdvancedCouponSystem/storage/logs/queue-worker.log
stopwaitsecs=3600
```

```bash
# تحديث Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start advanced-coupon-queue:*

# التحقق من حالة العمليات
sudo supervisorctl status
```

### 🔟 إعداد Cron Jobs

```bash
# تعديل crontab
sudo crontab -e -u www-data

# إضافة السطر التالي:
* * * * * cd /var/www/AdvancedCouponSystem && php artisan schedule:run >> /dev/null 2>&1
```

### 1️⃣1️⃣ إعداد Firewall

```bash
# تفعيل UFW
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable

# التحقق من الحالة
sudo ufw status
```

---

## 🔄 تحديث المشروع (Deployment)

عند رفع تحديثات جديدة:

```bash
cd /var/www/AdvancedCouponSystem

# 1. تفعيل وضع الصيانة
php artisan down

# 2. سحب آخر التحديثات (إذا كان Git)
git pull origin main

# 3. تحديث Dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# 4. تشغيل Migrations الجديدة
php artisan migrate --force

# 5. مسح وإعادة بناء Cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. إعادة تشغيل Queue Workers
sudo supervisorctl restart advanced-coupon-queue:*

# 7. إلغاء وضع الصيانة
php artisan up

# 8. إعادة تشغيل PHP-FPM و Nginx
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

---

## 📊 مراقبة الأداء والصيانة

### مسح Cache بشكل دوري:

```bash
# مسح Application Cache
php artisan cache:clear

# مسح Config Cache
php artisan config:clear

# مسح Route Cache
php artisan route:clear

# مسح View Cache
php artisan view:clear

# مسح Compiled Classes
php artisan clear-compiled

# إعادة بناء كل شيء
php artisan optimize
```

### مراقبة Logs:

```bash
# Laravel Logs
tail -f /var/www/AdvancedCouponSystem/storage/logs/laravel.log

# Nginx Access Logs
tail -f /var/log/nginx/access.log

# Nginx Error Logs
tail -f /var/log/nginx/error.log

# Queue Worker Logs
tail -f /var/www/AdvancedCouponSystem/storage/logs/queue-worker.log
```

### مراقبة Queue Workers:

```bash
# التحقق من حالة Workers
sudo supervisorctl status advanced-coupon-queue:*

# إعادة تشغيل Workers
sudo supervisorctl restart advanced-coupon-queue:*

# إيقاف Workers
sudo supervisorctl stop advanced-coupon-queue:*

# بدء Workers
sudo supervisorctl start advanced-coupon-queue:*
```

---

## 🎯 تشغيل Cron Jobs للمزامنة التلقائية

إذا كنت تريد مزامنة تلقائية للبيانات من الشبكات:

```bash
# تعديل routes/console.php أو إضافة في App\Console\Kernel.php
```

**في `app/Console/Kernel.php`:**

```php
protected function schedule(Schedule $schedule)
{
    // مزامنة يومية للبيانات من جميع الشبكات
    $schedule->command('sync:all-networks')->daily()->at('02:00');
    
    // مزامنة كل ساعة للبيانات الحديثة
    $schedule->command('sync:recent-purchases')->hourly();
    
    // تنظيف Logs القديمة
    $schedule->command('logs:clean')->weekly();
}
```

---

## 🔐 الأمان والحماية

### 1. تأمين ملف .env:

```bash
sudo chmod 600 /var/www/AdvancedCouponSystem/.env
sudo chown www-data:www-data /var/www/AdvancedCouponSystem/.env
```

### 2. تعطيل عرض الأخطاء في الإنتاج:

في `.env`:
```env
APP_DEBUG=false
APP_ENV=production
```

### 3. إعداد Rate Limiting:

تأكد من وجود Rate Limiting في `app/Http/Kernel.php`

### 4. تفعيل HTTPS فقط:

في `.env`:
```env
APP_URL=https://yourdomain.com
```

---

## 🚦 اختبار التثبيت

### 1. التحقق من الموقع:

```bash
# افتح المتصفح
https://yourdomain.com

# يجب أن ترى صفحة تسجيل الدخول
```

### 2. إنشاء أول مستخدم:

```bash
cd /var/www/AdvancedCouponSystem

# استخدام tinker
php artisan tinker
```

```php
// في Tinker
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@example.com';
$user->password = Hash::make('password123');
$user->save();

// أو استخدم Seeder
exit
php artisan db:seed --class=UserSeeder
```

### 3. اختبار Queue:

```bash
# إرسال job تجريبي
php artisan queue:work --once

# التحقق من عمل Supervisor
sudo supervisorctl status
```

---

## 📈 تحسينات الأداء

### 1. تفعيل OPcache:

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

أضف أو عدّل:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
```

```bash
sudo systemctl restart php8.2-fpm
```

### 2. تحسين Composer Autoloader:

```bash
composer dump-autoload --optimize --classmap-authoritative
```

### 3. استخدام Redis للـ Cache (اختياري):

```bash
# تثبيت Redis
sudo apt install -y redis-server

# تفعيل Redis
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

في `.env`:
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

---

## 🛠️ استكشاف الأخطاء

### المشكلة: صفحة 500 Internal Server Error

```bash
# التحقق من Logs
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/error.log

# التحقق من الصلاحيات
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# مسح Cache
php artisan cache:clear
php artisan config:clear
```

### المشكلة: Assets لا تظهر

```bash
# إعادة بناء Assets
npm run build

# التحقق من ملف manifest
ls -la public/build/manifest.json

# التحقق من صلاحيات public
sudo chmod -R 755 public
```

### المشكلة: Queue لا يعمل

```bash
# التحقق من Supervisor
sudo supervisorctl status

# إعادة تشغيل Workers
sudo supervisorctl restart advanced-coupon-queue:*

# التحقق من Logs
tail -f storage/logs/queue-worker.log
```

### المشكلة: Database Connection Error

```bash
# التحقق من اتصال MySQL
mysql -u coupon_user -p advanced_coupon_system

# التحقق من .env
cat .env | grep DB_

# مسح config cache
php artisan config:clear
```

---

## 📱 مراقبة الأداء

### تثبيت Laravel Telescope (اختياري للتطوير):

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

### تثبيت Laravel Horizon (لمراقبة Queues):

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
```

في `.env`:
```env
HORIZON_ENABLED=true
```

---

## 🔄 Backup والاستعادة

### Backup تلقائي يومي:

```bash
# إنشاء سكريبت Backup
sudo nano /usr/local/bin/backup-coupon-system.sh
```

```bash
#!/bin/bash

# متغيرات
PROJECT_DIR="/var/www/AdvancedCouponSystem"
BACKUP_DIR="/backups/coupon-system"
DATE=$(date +%Y-%m-%d_%H-%M-%S)

# إنشاء مجلد Backup
mkdir -p $BACKUP_DIR

# Backup قاعدة البيانات
mysqldump -u coupon_user -p'StrongPassword123!@#' advanced_coupon_system | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup الملفات
tar -czf $BACKUP_DIR/files_$DATE.tar.gz $PROJECT_DIR/storage $PROJECT_DIR/.env

# حذف Backups أقدم من 30 يوم
find $BACKUP_DIR -type f -mtime +30 -delete

echo "Backup completed: $DATE"
```

```bash
# إعطاء صلاحيات التنفيذ
sudo chmod +x /usr/local/bin/backup-coupon-system.sh

# إضافة إلى Cron (يومياً الساعة 3 صباحاً)
sudo crontab -e
```

أضف:
```
0 3 * * * /usr/local/bin/backup-coupon-system.sh >> /var/log/backup-coupon-system.log 2>&1
```

---

## ✅ Checklist النهائي

- [ ] PHP 8.2+ مثبت
- [ ] Composer مثبت
- [ ] Node.js 18+ مثبت
- [ ] MySQL/MariaDB مثبت
- [ ] قاعدة البيانات منشأة
- [ ] المشروع مرفوع على السيرفر
- [ ] Composer dependencies مثبتة
- [ ] NPM dependencies مثبتة
- [ ] Assets مبنية (npm run build)
- [ ] .env معد بشكل صحيح
- [ ] APP_KEY مولد
- [ ] Migrations منفذة
- [ ] Storage link منشأ
- [ ] Nginx معد
- [ ] SSL معد (Let's Encrypt)
- [ ] Supervisor معد للـ Queue
- [ ] Cron jobs معدة
- [ ] Firewall معد
- [ ] Backups معدة
- [ ] الصلاحيات صحيحة (755 للملفات، 775 للـ storage)

---

## 📞 نصائح إضافية

### تحسين الأمان:

1. **تغيير بيانات قاعدة البيانات الافتراضية**
2. **استخدام كلمات مرور قوية**
3. **تفعيل Two-Factor Authentication** (2FA)
4. **مراقبة Logs بشكل دوري**
5. **تحديث Dependencies بانتظام**: `composer update`, `npm update`

### النسخ الاحتياطي:

1. **Backup يومي** لقاعدة البيانات
2. **Backup أسبوعي** للملفات الكاملة
3. **حفظ Backups** خارج السيرفر (Cloud Storage)

### المراقبة:

1. **استخدام monitoring tools** مثل:
   - New Relic
   - Datadog
   - Laravel Telescope (Development)
   - Laravel Horizon (Queue Monitoring)

2. **إعداد تنبيهات** للأخطاء الحرجة

---

## 🎉 تم!

الآن المشروع يعمل على السيرفر بشكل كامل!

**للوصول:**
- الموقع: `https://yourdomain.com`
- تسجيل الدخول: `admin@example.com` / `password123`

**للدعم:**
راجع Laravel logs في `storage/logs/laravel.log`

