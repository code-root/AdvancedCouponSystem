# Network Integration Guide

## Overview
هذا الدليل يشرح كيفية عمل نظام ربط الشبكات (Networks) في المشروع.

## البنية (Architecture)

### 1. Services Layer
كل شبكة لها Service خاص بها في `app/Services/Networks/`:
- `NetworkServiceInterface.php` - الواجهة الأساسية
- `BaseNetworkService.php` - الخدمة الأساسية المشتركة
- `BoostinyService.php` - خدمة Boostiny
- `ClickDealerService.php` - خدمة ClickDealer
- `AdmitadService.php` - خدمة Admitad
- `NetworkServiceFactory.php` - مصنع لإنشاء الخدمات

### 2. Controller
`NetworkController.php` يحتوي على:
- `getNetworkConfig()` - جلب إعدادات الشبكة والحقول المطلوبة
- `testConnection()` - اختبار الاتصال مع الشبكة
- `store()` - حفظ البيانات مع تشفير الحقول الحساسة

### 3. Frontend
`resources/views/dashboard/networks/create.blade.php`:
- حقول ديناميكية تتغير حسب الشبكة
- اختبار الاتصال قبل الحفظ
- عرض الإعدادات الافتراضية

## كيفية إضافة شبكة جديدة

### الخطوة 1: إنشاء Service
```php
<?php
namespace App\Services\Networks;

class NewNetworkService extends BaseNetworkService
{
    protected string $networkName = 'NewNetwork';
    
    protected array $requiredFields = [
        'api_key',
        'api_secret'
    ];
    
    protected array $defaultConfig = [
        'api_url' => 'https://api.newnetwork.com/v1',
        'timeout' => 30
    ];
    
    public function testConnection(array $credentials): array
    {
        // Validate
        $validation = $this->validateCredentials($credentials);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Invalid credentials',
                'errors' => $validation['errors']
            ];
        }
        
        // Test API connection
        $response = $this->makeRequest('get', $apiUrl . '/verify', [
            'headers' => [
                'Authorization' => 'Bearer ' . $credentials['api_key']
            ]
        ]);
        
        return [
            'success' => $response['success'],
            'message' => $response['success'] ? 'Connected!' : 'Failed',
            'data' => $response['data']
        ];
    }
}
```

### الخطوة 2: تحديث Factory
في `NetworkServiceFactory.php`:
```php
public static function getAllServices(): array
{
    return [
        'newnetwork' => NewNetworkService::class,
        // ... existing services
    ];
}
```

### الخطوة 3: إضافة البيانات
في `NetworksSeeder.php` أو مباشرة في قاعدة البيانات:
```php
[
    'name' => 'newnetwork',
    'display_name' => 'New Network',
    'api_url' => 'https://api.newnetwork.com/v1',
    'is_active' => true,
]
```

## Boostiny Integration

### الحقول المطلوبة:
- `api_key` - API Key من Boostiny (Access Token)
- `api_secret` - API Secret من Boostiny (اختياري)

### كيفية الحصول على الـ credentials:
1. سجل دخول إلى Boostiny Dashboard
2. اذهب إلى Settings → API
3. احصل على API Key (Access Token)
4. استخدم API Key مباشرة في الحقل

### API Endpoints المستخدمة:
- Test: `GET /v2/publisher/performance?limit=1000&from={first_day_of_month}&to={today}`
- Campaigns: `GET /v2/publisher/performance`
- Link Performance: `GET /v2/reports/link-performance/data`

### Date Range:
- يتم اختبار الاتصال بجلب البيانات من أول يوم في الشهر الحالي إلى اليوم الحالي
- مثال: إذا كان اليوم 10 أكتوبر 2025، سيجلب البيانات من 1 أكتوبر إلى 10 أكتوبر

### مثال على الاستخدام:
```javascript
// Frontend - عند اختيار Boostiny
{
    network_id: 1,
    credentials: {
        api_key: 'your_access_token_here',
        api_secret: 'optional'
    }
}
```

### Authentication Method:
- يتم استخدام API Key مباشرة كـ Access Token
- يتم إرسالها في Header: `Authorization: Bearer {api_key}`
- الـ API Secret اختياري ولا يُستخدم في المصادقة

## Security
- جميع الحقول الحساسة (`client_secret`, `api_secret`, `password`, `token`) يتم تشفيرها تلقائياً
- الـ credentials تُخزن في عمود `credentials` من نوع JSON مشفر

## Testing
1. اختر الشبكة من القائمة المنسدلة
2. سيتم تحميل الحقول المطلوبة تلقائياً
3. أدخل البيانات المطلوبة
4. عند الحفظ، سيتم اختبار الاتصال أولاً
5. إذا نجح الاختبار → يحفظ البيانات
6. إذا فشل → يعطي خيار للمحاولة مرة أخرى

## Notes
- كل شبكة لها حقول مختلفة
- بعض الشبكات تستخدم OAuth2 وتحتاج `client_id` و `client_secret`
- بعض الشبكات تستخدم Access Token مباشرة
- الـ API URL يتم ملؤه تلقائياً ويكون read-only

