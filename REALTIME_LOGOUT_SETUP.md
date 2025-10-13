# Real-Time Logout Setup Guide

## تم التنفيذ ✅

تم إضافة نظام **تسجيل خروج فوري وreal-time** عند حذف الجلسة!

---

## كيف يعمل النظام 🔧

### التدفق الكامل:

```
Device A (الجهاز الأول)          Device B (الجهاز الثاني)
     │                                  │
     │  1. يحذف جلسة Device B         │
     ├──────────────────────────────────┤
     │                                  │
     │  2. يرسل SessionTerminated      │
     │     event عبر Pusher             │
     ├──────────────────────────────────►
     │                                  │ 3. يستقبل Event فوراً
     │                                  │
     │                                  │ 4. يظهر تنبيه:
     │                                  │    "Session Terminated!"
     │                                  │
     │                                  │ 5. بعد 3 ثواني:
     │                                  │    - يمسح localStorage
     │                                  │    - يحذف ملف الجلسة
     │                                  │    - يسجل خروج فوري
     │                                  │    - يوجه لـ Login
     │                                  ▼
                                    تم تسجيل الخروج! ✅
```

---

## الملفات المُعدّلة 📁

### 1. **SessionTerminated Event** - جديد
📄 `app/Events/SessionTerminated.php`

```php
class SessionTerminated implements ShouldBroadcast
{
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId),
            new PrivateChannel('session.' . $this->session->session_id),
        ];
    }
}
```

---

### 2. **SessionController** - محدّث
📄 `app/Http/Controllers/SessionController.php`

```php
public function destroy($id)
{
    $session = UserSession::findOrFail($id);
    
    // Broadcast BEFORE terminating
    broadcast(new SessionTerminated($session, 'forced'))->toOthers();
    
    // Delete session file from system
    $this->invalidateSessionFile($session->session_id);
    
    return ['success' => true];
}
```

**الميزات الجديدة**:
- ✅ `broadcast(new SessionTerminated())` - إرسال event
- ✅ `invalidateSessionFile()` - حذف ملف الجلسة
- ✅ يدعم `file` و `database` sessions

---

### 3. **Frontend - Global Listener** 
📄 `resources/views/layouts/partials/footer-scripts.blade.php`

```javascript
// يعمل في جميع الصفحات! 🌐
window.Echo.private('session.' + currentSessionId)
    .listen('SessionTerminated', (data) => {
        // Show alert
        Swal.fire('Session Terminated!');
        
        // Force logout after 3 seconds
        setTimeout(() => performForceLogout(), 3000);
    });
```

---

### 4. **Broadcasting Routes** 
📄 `routes/web.php`

```php
Route::middleware(['auth'])->group(function () {
    Broadcast::routes(); // ← Pusher authentication
});
```

📄 `routes/channels.php`

```php
// Private channel for specific session
Broadcast::channel('session.{sessionId}', function ($user, $sessionId) {
    return session()->getId() === $sessionId;
});
```

---

### 5. **Bootstrap.js** - محدّث
📄 `resources/js/bootstrap.js`

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    authEndpoint: '/broadcasting/auth',
});
```

---

### 6. **package.json** - إضافة Dependencies
📄 `package.json`

```json
"dependencies": {
    "laravel-echo": "^1.16.1",
    "pusher-js": "^8.4.0-rc2",
}
```

---

## التثبيت 🚀

### الخطوة 1: تثبيت Packages

```bash
npm install
```

### الخطوة 2: Build Assets

```bash
npm run build

# أو للتطوير:
npm run dev
```

### الخطوة 3: التحقق من Pusher Configuration

تأكد من وجود هذه القيم في `.env`:

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
VITE_PUSHER_HOST=
VITE_PUSHER_PORT=
VITE_PUSHER_SCHEME=https
```

### الخطوة 4: Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## الاختبار 🧪

### الطريقة 1: باستخدام متصفحين

1. **المتصفح الأول** (Chrome):
   - سجل دخول لـ `trakifi.com`
   - افتح Console: `F12`

2. **المتصفح الثاني** (Firefox):
   - سجل دخول بنفس الحساب
   - افتح Console: `F12`

3. **في المتصفح الأول**:
   - اذهب لـ `/dashboard/sessions`
   - احذف جلسة المتصفح الثاني

4. **في المتصفح الثاني**:
   - يجب أن ترى في Console:
     ```
     🚨 Current session terminated - forcing logout
     ```
   - يظهر تنبيه: "Session Terminated!"
   - بعد 3 ثواني → تسجيل خروج تلقائي!

---

### الطريقة 2: باستخدام Incognito

1. نافذة عادية + نافذة Incognito
2. سجل دخول في كليهما
3. احذف الجلسة من إحداهما
4. الأخرى تسجل خروج فوراً!

---

## Console Messages للتأكد 📊

### في الجهاز الذي سيُحذف:

```javascript
✅ Real-time session termination listener active
✅ Pusher connected
🚨 Current session terminated - forcing logout
→ Redirecting to login...
```

### في الجهاز الذي قام بالحذف:

```javascript
✅ Session deleted successfully
→ Reloading sessions list...
```

---

## Troubleshooting 🔧

### المشكلة 1: "Pusher not configured"

**الحل**:
```bash
# تحقق من .env
cat .env | grep PUSHER

# يجب أن يكون:
BROADCAST_DRIVER=pusher
PUSHER_APP_KEY=xxxxx
```

---

### المشكلة 2: "POST /pusher/auth 404"

**الحل**:
```bash
# تأكد من وجود route
php artisan route:list | grep broadcasting

# يجب أن يظهر:
POST broadcasting/auth
```

**إذا لم يظهر**:
```php
// في routes/web.php
Route::middleware(['auth'])->group(function () {
    Broadcast::routes(); // ← تأكد من وجودها
});
```

---

### المشكلة 3: "Echo is not defined"

**الحل**:
```bash
# Install packages
npm install laravel-echo pusher-js

# Build
npm run build
```

---

### المشكلة 4: الجلسة لا تخرج فوراً

**تحقق من**:

1. **Pusher متصل**:
```javascript
// في Console
console.log(Echo.connector.pusher.connection.state);
// يجب أن يكون: "connected"
```

2. **Channel مشترك**:
```javascript
console.log(Echo.connector.channels);
// يجب أن يحتوي: "private-session.xxxxx"
```

3. **Event يُرسل**:
```bash
# في Server logs
tail -f storage/logs/laravel.log | grep -i "broadcast\|pusher"
```

---

## بدون Pusher (Fallback) ⚙️

إذا لم يكن Pusher مُفعّل، النظام يعمل بـ **Polling**:

### في footer-scripts.blade.php:

```javascript
// Check session status every 30 seconds
setInterval(function() {
    fetch('/api/session/check')
        .then(r => r.json())
        .then(data => {
            if (!data.is_active) {
                performForceLogout();
            }
        });
}, 30000);
```

---

## Pusher Setup (إذا لم يكن مُفعّل) 📡

### 1. التسجيل في Pusher

1. اذهب لـ: https://pusher.com
2. سجل حساب مجاني
3. Create App

### 2. احصل على Credentials

```
App ID: 1234567
Key: abcdefghijk
Secret: lmnopqrstuv
Cluster: mt1 (or eu, us2, etc.)
```

### 3. تحديث .env

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=1234567
PUSHER_APP_KEY=abcdefghijk
PUSHER_APP_SECRET=lmnopqrstuv
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
VITE_PUSHER_SCHEME=https
```

### 4. تثبيت Pusher PHP SDK

```bash
composer require pusher/pusher-php-server
```

### 5. Rebuild Assets

```bash
npm run build
```

### 6. اختبر!

---

## الخلاصة 🎊

✅ **Event System** - SessionTerminated event جاهز  
✅ **Broadcasting** - Pusher integration كامل  
✅ **Real-time Listener** - يعمل في جميع الصفحات  
✅ **Session Invalidation** - حذف ملفات الجلسة  
✅ **Force Logout** - تسجيل خروج فوري  

---

## الأوامر المطلوبة الآن 🚀

```bash
# 1. Install packages
npm install

# 2. Build assets
npm run build

# 3. Clear cache
php artisan config:clear
php artisan cache:clear

# 4. Test!
# افتح متصفحين وجرب حذف الجلسة
```

---

**النظام جاهز! بعد تشغيل الأوامر، حذف أي جلسة سيخرج المستخدم فوراً! ⚡**

