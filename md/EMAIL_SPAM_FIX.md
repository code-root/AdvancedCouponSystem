# Email Spam Prevention Guide - حل مشكلة Spam

## المشكلة الحالية 🔍

الإيميلات تصل لكن في مجلد **Spam/Junk** بدلاً من **Inbox**.

**السبب**: Gmail وخدمات الإيميل الأخرى تعتبر الإيميلات spam للأسباب التالية:
- ❌ لا يوجد SPF record للدومين
- ❌ لا يوجد DKIM signature
- ❌ لا يوجد DMARC policy
- ❌ الإرسال من IP غير موثوق
- ❌ عدم وجود reputation للدومين

---

## الحلول المتاحة ✅

### الحل 1: استخدام خدمة إيميلات موثوقة (الأفضل) ⭐

#### A. SendGrid (مجاني - 100 إيميل/يوم)

**خطوات التفعيل**:

1. **التسجيل**:
   - اذهب لـ: https://sendgrid.com
   - سجل حساب مجاني
   - فعّل الحساب

2. **إنشاء API Key**:
   - Dashboard → Settings → API Keys
   - Create API Key
   - انسخ الـ API Key

3. **تحديث .env**:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=SG.your-api-key-here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@trakifi.com
MAIL_FROM_NAME="Trakifi"
MAIL_TIMEOUT=30
```

4. **Verify Domain** (اختياري لكن موصى به):
   - Settings → Sender Authentication
   - Authenticate Your Domain
   - أضف DNS records المطلوبة

**الفائدة**: 
- ✅ وصول 99% في Inbox
- ✅ Reputation عالية
- ✅ Analytics وإحصائيات
- ✅ مجاني حتى 100 إيميل/يوم

---

#### B. Mailgun (مجاني - 5,000 إيميل/شهر)

**خطوات التفعيل**:

1. **التسجيل**:
   - اذهب لـ: https://mailgun.com
   - سجل حساب مجاني

2. **احصل على SMTP Credentials**:
   - Sending → Domain Settings
   - SMTP Credentials

3. **تحديث .env**:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@yourdomain.mailgun.org
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@trakifi.com
MAIL_FROM_NAME="Trakifi"
MAIL_TIMEOUT=30
```

**الفائدة**:
- ✅ 5,000 إيميل مجاناً شهرياً
- ✅ وصول عالي في Inbox
- ✅ API قوية
- ✅ Tracking مفصل

---

#### C. Amazon SES (رخيص جداً)

**التكلفة**: $0.10 لكل 1,000 إيميل

```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
MAIL_FROM_ADDRESS=info@trakifi.com
MAIL_FROM_NAME="Trakifi"
```

---

### الحل 2: إصلاح DNS Records لدومينك 🌐

#### إذا كنت تملك `trakifi.com`:

يجب إضافة DNS records التالية:

#### A. SPF Record (Sender Policy Framework)

أضف TXT record:

```
Type: TXT
Name: @
Value: v=spf1 include:_spf.mx.cloudflare.net include:sendgrid.net ~all
TTL: 3600
```

**أو إذا كنت تستخدم SMTP الخاص**:
```
v=spf1 ip4:your-server-ip include:mail.trakifi.com ~all
```

---

#### B. DKIM Record

**إذا استخدمت SendGrid/Mailgun**:
- سيعطوك DKIM records جاهزة
- انسخها وأضفها لـ DNS

**أو أنشئ DKIM خاص**:
```bash
# Generate DKIM keys
openssl genrsa -out dkim_private.pem 1024
openssl rsa -in dkim_private.pem -pubout -out dkim_public.pem
```

---

#### C. DMARC Record

أضف TXT record:

```
Type: TXT
Name: _dmarc
Value: v=DMARC1; p=quarantine; rua=mailto:dmarc@trakifi.com
TTL: 3600
```

---

### الحل 3: تحسين محتوى الإيميلات 📝

<function_calls>
<invoke name="read_file">
<parameter name="target_file">/Users/mo/Documents/project/AdvancedCouponSystem/resources/views/emails/verify-email.blade.php
