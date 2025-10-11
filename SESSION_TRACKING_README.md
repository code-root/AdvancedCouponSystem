# 🔐 نظام تتبع Sessions والإشعارات

## ✅ تم إنشاؤه بنجاح!

### 📦 المميزات:

1. **تتبع كامل للـ Sessions:**
   - معلومات الجهاز (Desktop, Mobile, Tablet)
   - نوع المتصفح والإصدار
   - نظام التشغيل
   - الموقع الجغرافي (الدولة، المدينة، الإحداثيات)
   - عنوان IP
   - الـ Referrer URL (من أين جاء المستخدم)
   - Landing Page (أول صفحة دخلها)
   - UTM Parameters (للتتبع التسويقي)

2. **إدارة السيشنات:**
   - عرض السيشن الحالي
   - عرض جميع السيشنات الأخرى
   - إنهاء سيشن معين
   - إنهاء جميع السيشنات الأخرى
   - تنظيف السيشنات المنتهية

3. **الإشعارات:**
   - إشعار فوري عند تسجيل دخول جديد
   - تفاصيل كاملة عن الجهاز والموقع
   - عرض الإشعارات في Topbar
   - صفحة كاملة للإشعارات

4. **التحديث المباشر (Real-time):**
   - استخدام Pusher للتحديث الفوري
   - تنبيه عند تسجيل دخول من جهاز جديد
   - تحديث قائمة السيشنات تلقائياً

---

## 🚀 خطوات الإعداد:

### 1. تشغيل Migrations:

```bash
php artisan migrate
```

### 2. إعداد Broadcasting (اختياري - للتحديث المباشر):

#### أ. إذا كنت تريد استخدام Pusher:

1. سجل في [Pusher.com](https://pusher.com) (مجاني حتى 200k رسالة/يوم)
2. أنشئ App جديد
3. احصل على المعلومات التالية:
   - App ID
   - Key
   - Secret
   - Cluster

4. في `.env`:
```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=eu
```

#### ب. إذا لم تر يد استخدام Pusher:
في `.env`:
```env
BROADCAST_DRIVER=log
```

⚠️ **ملاحظة:** بدون Pusher، لن يكون هناك تحديث مباشر، لكن كل الميزات الأخرى ستعمل!

### 3. مسح Cache:

```bash
php artisan config:clear
php artisan cache:clear
```

---

## 📱 كيفية الاستخدام:

### الوصول للصفحة:

```
/dashboard/sessions
```

أو من Sidebar:
```
Security → Login Sessions
```

### عرض الإشعارات:

1. **في Topbar**: اضغط على أيقونة الجرس 🔔
2. **صفحة كاملة**: Sidebar → Notifications

---

## 🎯 الميزات التفصيلية:

### 1. Current Session Card (السيشن الحالي):
- معلومات الجهاز الحالي
- المتصفح
- الموقع
- IP Address
- من أين جاء (Referrer)

### 2. Other Sessions (السيشنات الأخرى):
- قائمة بجميع الأجهزة المسجل دخولها
- حالة كل سيشن (Active/Inactive)
- آخر نشاط
- إمكانية إنهاء أي سيشن
- إمكانية إنهاء جميع السيشنات الأخرى

### 3. Session Details (تفاصيل كاملة):
- Device: نوع الجهاز واسمه
- Browser: المتصفح والإصدار
- Platform: نظام التشغيل
- Location: الدولة، المدينة، المنطقة
- Coordinates: الإحداثيات (رابط Google Maps)
- IP Address: عنوان IP
- Timezone: المنطقة الزمنية
- Referrer: من أين جاء
- Landing Page: أول صفحة دخلها
- UTM Parameters: معلومات الحملة التسويقية
- Login Time: وقت تسجيل الدخول
- Last Activity: آخر نشاط
- Duration: مدة السيشن

### 4. إشعار تسجيل الدخول:
عند تسجيل دخول جديد، يحصل المستخدم على:
- ✅ Database Notification (يظهر في قائمة الإشعارات)
- ✅ Browser Notification (إذا كان الصفحة مفتوحة - عبر Pusher)
- ✅ Email Notification (اختياري - يمكن تفعيله)

---

## 🔧 التخصيص:

### تعطيل Email Notifications:

في `app/Notifications/NewLoginNotification.php`:
```php
public function via(object $notifiable): array
{
    return ['database', 'broadcast']; // أزل 'mail'
}
```

### تغيير مدة Session:

في `config/session.php`:
```php
'lifetime' => 120, // بالدقائق
```

### تعطيل Geo-location:

في `app/Http/Middleware/TrackUserSession.php`:
```php
protected function getLocationFromIP(string $ip): array
{
    return []; // لا جلب للموقع
}
```

---

## 🌐 API للـ IP Location:

يستخدم النظام `ipapi.co` المجاني:
- **الحد:** 1000 طلب/يوم
- **Cache:** 24 ساعة لكل IP
- **لا يحتاج API Key**

إذا تجاوزت الحد، يمكنك:
1. استخدام API Key من ipapi.co ($10/شهر)
2. استخدام خدمة أخرى مثل:
   - ip-api.com (مجاني، 45 طلب/دقيقة)
   - ipgeolocation.io (مجاني، 1000 طلب/يوم)

---

## 📊 الجداول في قاعدة البيانات:

### user_sessions:
```sql
- id
- user_id
- session_id (Laravel session ID)
- ip_address
- user_agent
- device_type, device_name, platform
- browser, browser_version
- country, country_code, region, city
- timezone, latitude, longitude
- referrer_url, landing_page
- utm_source, utm_medium, utm_campaign
- last_activity, login_at, expires_at
- is_active, logout_reason
- created_at, updated_at
```

### notifications:
```sql
- id
- type (NewLoginNotification)
- notifiable_type (User)
- notifiable_id (user_id)
- data (JSON - معلومات الإشعار)
- read_at (وقت القراءة)
- created_at, updated_at
```

---

## 🎨 الصفحات المنشأة:

1. ✅ `/dashboard/sessions` - عرض جميع السيشنات
2. ✅ `/dashboard/sessions/{id}` - تفاصيل سيشن معين
3. ✅ `/dashboard/notifications` - عرض جميع الإشعارات

---

## 🔔 إعداد Pusher (للتحديث المباشر):

### 1. تسجيل في Pusher:
https://pusher.com/signup

### 2. إنشاء App جديد:
- اسم App: AdvancedCouponSystem
- Cluster: اختر أقرب منطقة (eu, us-east-1, ap-1)

### 3. نسخ البيانات إلى .env:
```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=1234567
PUSHER_APP_KEY=abcdef123456
PUSHER_APP_SECRET=xyz789secret
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=eu

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

### 4. مسح Cache:
```bash
php artisan config:clear
```

### 5. تشغيل Queue Worker (إذا استخدمت ShouldQueue):
```bash
php artisan queue:work
```

---

## 🧪 الاختبار:

### 1. افتح المتصفح:
```
http://127.0.0.1:8000/dashboard/sessions
```

### 2. سجل دخول من جهاز/متصفح آخر:
- افتح متصفح آخر (Chrome, Firefox)
- أو استخدم Incognito Mode
- سجل دخول بنفس الحساب

### 3. يجب أن يحدث:
- ✅ يظهر notification في Topbar
- ✅ إذا كنت في صفحة Sessions، سيظهر SweetAlert
- ✅ سيتم تحديث القائمة تلقائياً
- ✅ العدد في Badge سيتحدث

### 4. اختبار إنهاء سيشن:
- اضغط "Logout" على أي سيشن
- يجب أن يتم إنهاؤه مباشرة
- المستخدم على الجهاز الآخر سيتم تسجيل خروجه

---

## ⚠️ ملاحظات مهمة:

1. **Shared Hosting**: لن يعمل Pusher real-time، لكن الإشعارات ستعمل
2. **Performance**: البيانات تُخزن في Cache لمدة 24 ساعة
3. **Privacy**: المعلومات الجغرافية تقريبية (على مستوى المدينة)
4. **Security**: جميع routes محمية بـ auth middleware

---

## 🎉 الخلاصة:

تم إنشاء نظام كامل لتتبع Sessions مع:
- ✅ جميع المعلومات المطلوبة (جهاز، متصفح، موقع، IP، referrer)
- ✅ إشعارات عند تسجيل دخول جديد
- ✅ إدارة كاملة للسيشنات
- ✅ تحديث مباشر (مع Pusher)
- ✅ واجهة جميلة ومتجاوبة

**استمتع بالميزة الجديدة!** 🚀

