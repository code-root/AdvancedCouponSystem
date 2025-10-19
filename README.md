# 🚀 Advanced Coupon System

نظام إدارة الكوبونات والاشتراكات المتقدم مع لوحة إدارة شاملة

## ✨ الميزات الرئيسية

### 🎯 نظام الاشتراكات
- ✅ خطط اشتراك متعددة (Starter, Pro, Enterprise)
- ✅ تجربة مجانية 14 يوم
- ✅ نظام الحدود والتحكم في الاستخدام
- ✅ كوبونات الخصم
- ✅ دعم Stripe و PayPal

### 👨‍💼 لوحة الإدارة
- ✅ إدارة المستخدمين والاشتراكات
- ✅ إدارة الخطط والكوبونات
- ✅ إدارة الشبكات والبروكسيات
- ✅ التقارير والإحصائيات
- ✅ إعدادات الموقع الديناميكية
- ✅ نظام التنقل كـ مستخدم (Impersonation)

### 🔒 الأمان والمراقبة
- ✅ مراقبة الأخطاء
- ✅ مراقبة الأداء
- ✅ نظام النسخ الاحتياطي
- ✅ حماية البيانات حسب user_id

### 🎨 التخصيص
- ✅ ثيمات متعددة (Light, Dark, Auto)
- ✅ تخطيطات مختلفة (Vertical, Horizontal, Two Column)
- ✅ ألوان قابلة للتخصيص
- ✅ إعدادات ديناميكية للموقع

## 🛠️ التثبيت

### المتطلبات
- PHP 8.1+
- MySQL 5.7+
- Composer
- Node.js & NPM

### خطوات التثبيت

1. **استنساخ المشروع**
```bash
git clone <repository-url>
cd AdvancedCouponSystem
```

2. **تثبيت التبعيات**
```bash
composer install
npm install
```

3. **إعداد البيئة**
```bash
cp .env.example .env
php artisan key:generate
```

4. **إعداد قاعدة البيانات**
```bash
# تحديث ملف .env بقاعدة البيانات
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=advanced_coupon_system
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. **تشغيل المايجريشن والـ Seeders**
```bash
php artisan migrate
php artisan db:seed
```

6. **بناء الأصول**
```bash
npm run build
```

7. **تشغيل الخادم**
```bash
php artisan serve
```

## 🔐 بيانات تسجيل الدخول

### للمستخدمين العاديين
- **المسار**: `http://127.0.0.1:8000/login`
- **إنشاء حساب**: `http://127.0.0.1:8000/register`

### للأدمن
- **المسار**: `http://127.0.0.1:8000/admin/login`
- **البريد الإلكتروني**: `admin@example.com`
- **كلمة المرور**: `password`

## 📊 المسارات الرئيسية

### للمستخدمين
- `/dashboard` - لوحة التحكم
- `/subscriptions/plans` - خطط الاشتراك
- `/subscriptions/compare` - مقارنة الخطط
- `/subscriptions/manage` - إدارة الاشتراك

### للأدمن
- `/admin/` - لوحة التحكم الرئيسية
- `/admin/user-management/` - إدارة المستخدمين
- `/admin/plans/` - إدارة الخطط
- `/admin/reports/` - التقارير
- `/admin/settings/` - الإعدادات

## 🎨 تخصيص الثيم

النظام يدعم تخصيص الثيم من خلال:

1. **الضغط على أيقونة الإعدادات** في الجانب الأيمن
2. **اختيار التخطيط**: Vertical, Horizontal, Two Column
3. **اختيار الثيم**: Light, Dark, Auto
4. **تخصيص الألوان**: Sidebar, Topbar
5. **حفظ الإعدادات** تلقائياً

## 🔧 الأوامر المفيدة

```bash
# تشغيل النسخ الاحتياطي
php artisan backup:database
php artisan backup:files

# تشغيل الاختبارات
php artisan test

# تشغيل الـ Jobs المجدولة
php artisan schedule:run

# مسح الـ Cache
php artisan cache:clear
php artisan route:clear
php artisan config:clear

# إعادة بناء الأصول
npm run build
npm run dev
```

## 📁 هيكل المشروع

```
AdvancedCouponSystem/
├── app/
│   ├── Console/Commands/     # أوامر Artisan
│   ├── Http/
│   │   ├── Controllers/      # المتحكمات
│   │   │   ├── admin/        # متحكمات الأدمن
│   │   │   └── ...
│   │   └── Middleware/       # الـ Middleware
│   ├── Jobs/                 # الـ Jobs
│   ├── Models/               # النماذج
│   ├── Notifications/        # الإشعارات
│   └── Services/             # الخدمات
├── database/
│   ├── migrations/           # المايجريشن
│   └── seeders/              # الـ Seeders
├── resources/
│   ├── views/
│   │   ├── admin/            # صفحات الأدمن
│   │   ├── dashboard/        # صفحات المستخدمين
│   │   └── ...
│   └── js/                   # ملفات JavaScript
├── routes/
│   ├── admin.php             # مسارات الأدمن
│   ├── dashboard.php         # مسارات المستخدمين
│   └── web.php               # المسارات العامة
├── tests/                    # الاختبارات
└── public/
    └── assets/               # الأصول العامة
```

## 🧪 الاختبارات

```bash
# تشغيل جميع الاختبارات
php artisan test

# تشغيل اختبارات الوحدة
php artisan test --testsuite=Unit

# تشغيل اختبارات التكامل
php artisan test --testsuite=Feature

# تشغيل اختبارات مع تغطية
php artisan test --coverage
```

## 📈 المراقبة والأداء

### مراقبة الأخطاء
- يتم تسجيل جميع الأخطاء في جدول `error_logs`
- يمكن مراقبة الأخطاء من لوحة الأدمن

### مراقبة الأداء
- يتم تسجيل مقاييس الأداء في جدول `performance_metrics`
- تتبع وقت الاستجابة واستخدام الذاكرة

### النسخ الاحتياطي
- نسخ احتياطي لقاعدة البيانات: `php artisan backup:database`
- نسخ احتياطي للملفات: `php artisan backup:files`

## 🔄 الجدولة

النظام يشمل Jobs مجدولة:

- **RotateSyncUsageJob**: دوْرَنة بيانات الاستخدام يومياً
- **ResetDailyCountersJob**: إعادة تعيين العدادات اليومية
- **NotifyTrialEndingJob**: إشعارات انتهاء التجربة

## 🎯 الخطط المتاحة

### Starter Plan
- 3 شبكات كحد أقصى
- 100 عملية sync يومياً
- 2000 عملية sync شهرياً
- حد الإيرادات: $10,000
- حد الطلبات: 500

### Pro Plan
- 10 شبكات كحد أقصى
- 500 عملية sync يومياً
- 10000 عملية sync شهرياً
- حد الإيرادات: $50,000
- حد الطلبات: 2500

### Enterprise Plan
- شبكات غير محدودة
- عمليات sync غير محدودة
- إيرادات غير محدودة
- طلبات غير محدودة

## 🚀 النشر

### إعدادات الإنتاج

1. **تحديث ملف .env**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

2. **تحسين الأداء**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. **إعداد الخادم**
- Apache/Nginx
- SSL Certificate
- Database optimization

## 🤝 المساهمة

1. Fork المشروع
2. إنشاء branch للميزة الجديدة
3. Commit التغييرات
4. Push إلى الـ branch
5. إنشاء Pull Request

## 📄 الترخيص

هذا المشروع مرخص تحت رخصة MIT.

## 📞 الدعم

للحصول على الدعم:
- إنشاء Issue في GitHub
- التواصل عبر البريد الإلكتروني
- مراجعة الوثائق

---

**تم تطوير هذا النظام بواسطة فريق التطوير المتقدم** 🚀