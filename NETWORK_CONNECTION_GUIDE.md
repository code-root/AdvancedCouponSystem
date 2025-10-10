# Network Connection System - دليل الاستخدام

## نظرة عامة

نظام ربط المستخدمين بالبروكرز (Networks) يسمح لكل مستخدم بإنشاء اتصالات متعددة مع البروكرز المختلفة.

---

## البروكرز المتاحة (17 Network)

### 1. **Boostiny**
- API: `https://api.boostiny.com/`
- الميزات: Campaigns, Analytics, Real-time Tracking, API Integration

### 2. **OptimiseMedia**
- API: `https://public.api.optimisemedia.com/v1/`
- الميزات: API Integration, Performance Tracking

### 3. **Marketeers**
- API: `https://api.marketeers.com/`
- الميزات: Marketing Analytics, Campaign Optimization

### 4. **Digizag**
- API: `https://digizag.api.hasoffers.com/Apiv3/json`
- الميزات: Publisher Tools, Performance Tracking

### 5. **Admitad**
- API: `https://api.admitad.com/`
- الميزات: Global Network, Advanced Reporting

### 6. **CPX** (غير مفعل)
- API: `https://api.cpx.ae/api/auth/conversions_report_dashboard`

### 7. **Arabclicks**
- API: `https://arabclicks.api.hasoffers.com/Apiv3/json`
- الميزات: Regional Network, Arabic Support

### 8. **GlobalNetwork**
- API: `https://globalnetwork1.api.hasoffers.com/Apiv3/json`
- الميزات: Global Reach, Multi-currency

### 9. **Platformance**
- API: `https://login.platformance.co/publisher/performance`
- الميزات: Advanced Analytics, Multi-channel Support

### 10. **iHerb**
- API: `https://api.partnerize.com/v3/partner/analytics/conversions`
- الميزات: Health & Wellness, Commission Tracking

### 11. **SquatWolf**
- API: `https://api.squatwolf.com/`
- الميزات: Fitness Niche, Performance Tracking

### 12. **LinkAraby**
- API: `https://api.linkaraby.com/`
- الميزات: Arabic Content, Regional Focus

### 13. **Trendyol**
- API: `https://apigw.trendyol.com/`
- الميزات: E-commerce, Turkish Market

### 14. **AliExpress**
- API: `https://portals.aliexpress.com/cps/report/fetchEffectDetailNew`
- الميزات: Global E-commerce, High Volume

### 15. **Temu**
- API: `https://www.temu.com/api/link/generic_proxy/sugar/report`
- الميزات: Marketplace, Competitive Rates

### 16. **Omolaat**
- API: `https://my.omolaat.com/elasticsearch/msearch`
- الميزات: Gulf Region, Local Payment

### 17. **GlobaleMedia**
- API: `https://login.globalemedia.net/publisher/performance`
- الميزات: Global Network, Performance Based

---

## كيفية الاتصال ببروكر

### الخطوات:

1. **اذهب إلى** `/networks/create`

2. **اختر البروكر** من القائمة المنسدلة

3. **أدخل معلومات الاتصال:**
   - Connection Name: اسم مميز للاتصال (مثل: My Boostiny Account)
   - Client ID: معرف العميل من البروكر
   - Client Secret: المفتاح السري
   - Token: رمز الوصول (اختياري)
   - Contact ID: معرف جهة الاتصال (اختياري)

4. **اضغط "Connect Network"**

---

## هيكل قاعدة البيانات

### جدول `networks`
```sql
- id
- name (boostiny, admitad, etc.)
- display_name (Boostiny, Admitad, etc.)
- description
- api_url
- is_active
- supported_features (JSON)
- created_at
- updated_at
```

### جدول `network_connections`
```sql
- id
- user_id (FK)
- network_id (FK)
- connection_name
- client_id
- client_secret
- token
- contact_id
- api_endpoint
- status (pending, connected, disconnected, failed)
- access_token
- refresh_token
- credentials (JSON)
- settings (JSON)
- is_active
- is_connected
- connected_at
- last_sync
- expires_at
- error_message
- created_at
- updated_at

UNIQUE KEY: (user_id, network_id)
```

---

## العلاقات (Relationships)

### User Model
```php
// Get all network connections
$user->networkConnections

// Get connected networks
$user->connectedNetworks

// Check if connected to network
$user->isConnectedToNetwork($networkId)

// Get active connections count
$user->getActiveNetworkConnectionsCount()
```

### Network Model
```php
// Get all user connections
$network->networkConnections

// Get connected users
$network->connectedUsers
```

### NetworkConnection Model
```php
// Get user
$connection->user

// Get network
$connection->network

// Check if active
$connection->isActive()

// Check if connected
$connection->isConnected()
```

---

## API Endpoints

### Create Connection
```http
POST /networks
Content-Type: application/x-www-form-urlencoded

network_id=1
connection_name=My Boostiny Account
client_id=your_client_id
client_secret=your_client_secret
token=optional_token
contact_id=optional_contact_id
```

### Get User Connections
```http
GET /networks
```

### View Connection Details
```http
GET /networks/{network_id}
```

### Update Connection
```http
PUT /networks/{network_id}
```

### Delete Connection
```http
DELETE /networks/connections/{connection_id}
```

---

## مميزات النظام

✅ **كل مستخدم له اتصالاته الخاصة**
- يمكن للمستخدم الاتصال بنفس البروكر مرة واحدة فقط
- كل مستخدم يرى اتصالاته فقط

✅ **تتبع الاتصالات**
- تاريخ الاتصال
- آخر مزامنة
- حالة الاتصال
- رسائل الأخطاء

✅ **أمان البيانات**
- Client Secret مخزن بشكل آمن
- Credentials مخزنة في JSON مشفر
- Unique constraint على (user_id, network_id)

✅ **إدارة مرنة**
- تفعيل/تعطيل الاتصال
- حذف الاتصال
- تحديث بيانات الاتصال
- مزامنة البيانات

---

## مثال على الاستخدام

```php
// Get current user's network connections
$myConnections = auth()->user()->networkConnections;

// Check if connected to Boostiny
$network = Network::where('name', 'boostiny')->first();
$isConnected = auth()->user()->isConnectedToNetwork($network->id);

// Create new connection
$connection = NetworkConnection::create([
    'user_id' => auth()->id(),
    'network_id' => $network->id,
    'connection_name' => 'My Boostiny Account',
    'client_id' => 'your_client_id',
    'client_secret' => 'your_secret',
    'status' => 'connected',
    'is_connected' => true,
    'connected_at' => now(),
]);

// Get connection count
$count = auth()->user()->getActiveNetworkConnectionsCount();
```

---

## الخطوات التالية

1. ✅ إنشاء 17 Network في قاعدة البيانات
2. ✅ نظام ربط User ↔ Network عبر NetworkConnection
3. ✅ صفحة Create مع Dropdown للبروكرز
4. ⏳ صفحة لعرض اتصالات كل مستخدم
5. ⏳ API لمزامنة البيانات من كل Network
6. ⏳ Dashboard لعرض إحصائيات كل اتصال

---

**Created:** October 10, 2025  
**Laravel Version:** 12.x

