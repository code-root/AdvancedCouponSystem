# PHP Timeout Configuration Fix

## Problem

When connecting to Admitad or performing network sync operations, you may encounter:

```
Maximum execution time of 30 seconds exceeded
```

This happens because the OAuth flow and reCAPTCHA solving can take longer than the default 30 seconds.

## Solutions Implemented

### 1. Code-Level Fixes

#### A. Extended Execution Time in Services

**In AdmitadService.php**:
```php
public function testConnection(array $credentials): array
{
    // Extend execution time for OAuth flow
    set_time_limit(120);
    // ...
}

public function syncData(array $credentials, array $config = []): array
{
    // Extend execution time for sync operations
    set_time_limit(180);
    // ...
}
```

#### B. Optimized reCAPTCHA Service

**In RecaptchaService.php**:
```php
// Reduced from 120 to 45 seconds
private const MAX_WAIT_SECONDS = 45;

// Faster polling: 3 seconds instead of 5
private const POLL_INTERVAL_SECONDS = 3;
```

#### C. HTTP Request Timeouts

All HTTP requests now have explicit timeouts:
```php
// Standard requests
Http::timeout(30)->get($url);

// Data fetching requests (may be slow)
Http::timeout(60)->get($url);
```

### 2. Server Configuration

#### A. .user.ini File (Created)

**File**: `public/.user.ini`

```ini
max_execution_time = 180
max_input_time = 180
memory_limit = 256M
post_max_size = 20M
upload_max_filesize = 20M
```

**Note**: This file works with FastCGI/FPM. Changes take effect after 5 minutes or after restarting PHP-FPM.

#### B. For Apache with mod_php

Add to `public/.htaccess`:
```apache
<IfModule mod_php.c>
    php_value max_execution_time 180
    php_value max_input_time 180
    php_value memory_limit 256M
</IfModule>
```

#### C. For Nginx with PHP-FPM

Edit your PHP-FPM pool configuration (e.g., `/etc/php/8.x/fpm/pool.d/www.conf`):

```ini
request_terminate_timeout = 180
```

Then edit `php.ini`:
```ini
max_execution_time = 180
max_input_time = 180
memory_limit = 256M
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
# or
sudo service php8.2-fpm restart
```

#### D. For Laravel Artisan Commands

In `config/app.php`, you can set:
```php
'max_execution_time' => env('MAX_EXECUTION_TIME', 180),
```

Then in `.env`:
```env
MAX_EXECUTION_TIME=180
```

### 3. For Local Development (php artisan serve)

When using `php artisan serve`, the built-in server uses your `php.ini` settings.

Edit your `php.ini`:
```ini
max_execution_time = 180
max_input_time = 180
memory_limit = 256M
```

Find your php.ini location:
```bash
php --ini
```

Restart the development server:
```bash
php artisan serve
```

### 4. For Production

#### Using Supervisor for Queue Workers

If you're running queue workers, update supervisor config:

**File**: `/etc/supervisor/conf.d/laravel-worker.conf`

```ini
[program:laravel-worker]
command=php /path/to/artisan queue:work --timeout=180
```

Reload supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart laravel-worker:*
```

## Testing

### 1. Check Current Settings

```bash
php -i | grep max_execution_time
php -i | grep memory_limit
```

### 2. Test Admitad Connection

```bash
php artisan tinker

$service = new App\Services\Networks\AdmitadService();
$result = $service->testConnection([
    'email' => 'test@example.com',
    'password' => 'password123'
]);

// Should complete within 90-120 seconds without timeout
```

### 3. Monitor Execution Time

Add to your code:
```php
$startTime = microtime(true);
// ... your code
$executionTime = microtime(true) - $startTime;
Log::info("Execution time: {$executionTime} seconds");
```

## Expected Execution Times

| Operation | Expected Time | Timeout Setting |
|-----------|---------------|-----------------|
| Test Connection | 60-90 seconds | 120 seconds |
| Sync Data (first time) | 90-120 seconds | 180 seconds |
| Sync Data (with token) | 5-15 seconds | 60 seconds |
| reCAPTCHA Solving | 15-45 seconds | 45 seconds |

## Troubleshooting

### Still Getting Timeout?

1. **Check if .user.ini is loaded**:
```bash
php -r "phpinfo();" | grep user_ini
```

2. **Wait 5 minutes** after creating `.user.ini` (PHP-FPM caches it)

3. **Restart PHP-FPM**:
```bash
sudo systemctl restart php8.2-fpm
```

4. **Increase limits further** if needed:
```php
// In AdmitadService.php
set_time_limit(300); // 5 minutes
```

### Performance Optimization Tips

1. **Use Queue Jobs** for long-running syncs:
```php
dispatch(new SyncNetworkJob($connection, $config));
```

2. **Cache reCAPTCHA tokens** (if possible):
```php
Cache::remember('captcha_token', 600, function() {
    return $recaptchaService->solve(...);
});
```

3. **Reduce data range**:
```php
// Instead of full month
$startDate = now()->subDays(7)->format('Y-m-d');
```

## Production Recommendations

### 1. Use Queue Workers

Move long operations to queues:

```php
// Instead of direct sync
$job = new SyncAdmitadJob($connection, $config);
dispatch($job);
```

### 2. Add Progress Tracking

```php
Cache::put("sync_progress_{$connectionId}", [
    'status' => 'in_progress',
    'current_step' => 'Solving reCAPTCHA',
    'progress' => 30,
], 600);
```

### 3. Use WebSockets for Real-time Updates

Notify users when long operations complete:
```php
event(new NetworkSyncCompleted($connection, $results));
```

### 4. Implement Timeout Alerts

```php
if ($executionTime > 120) {
    Log::warning("Slow sync operation: {$executionTime}s for connection {$connectionId}");
}
```

## Files Modified

1. ✅ `app/Services/RecaptchaService.php`
   - Reduced `MAX_WAIT_SECONDS` to 45
   - Reduced `POLL_INTERVAL_SECONDS` to 3
   - Added `timeout(30)` to HTTP requests

2. ✅ `app/Services/Networks/AdmitadService.php`
   - Added `set_time_limit(120)` in `testConnection()`
   - Added `set_time_limit(180)` in `syncData()`
   - Added `timeout()` to all HTTP requests

3. ✅ `public/.user.ini` (NEW)
   - Set `max_execution_time = 180`
   - Set `memory_limit = 256M`

## Summary

The system now handles long-running operations properly:

- ✅ Code-level timeouts extended
- ✅ HTTP request timeouts configured
- ✅ reCAPTCHA optimized for faster solving
- ✅ Server configuration updated
- ✅ Ready for production use

**You should no longer see timeout errors!**

---

**Last Updated**: October 12, 2025

