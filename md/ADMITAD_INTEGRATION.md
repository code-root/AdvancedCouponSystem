# Admitad Integration Guide

## Overview

This guide explains how the Admitad network integration works in the Advanced Coupon System.

## Architecture

### Components

1. **RecaptchaService** (`app/Services/RecaptchaService.php`)
   - Solves reCAPTCHA v3 challenges using OmoCaptcha API
   - Standalone service that can be used by any network requiring captcha solving

2. **AdmitadService** (`app/Services/Networks/AdmitadService.php`)
   - Implements complete Admitad OAuth flow
   - Handles login, authorization, and token management
   - Uses RecaptchaService for solving captchas

3. **OAuth Callback** (`admitadCallback` in NetworkController)
   - Receives authorization code from Admitad
   - Exchanges code for access token
   - Stores credentials securely

## Authentication Flow

### Step-by-Step Process

```
1. User Login to Mitgo (Keycloak)
   ↓
2. Get Login Page
   - Extract execution, tab_id, session_code
   - Store cookies
   ↓
3. POST Authentication
   - Submit email + password
   - Get redirect location
   ↓
4-7. Follow OAuth Redirect Chain
   - Multiple 302 redirects between Mitgo and Admitad Store
   - Accumulate cookies from all redirects
   ↓
8. Get Client Credentials
   - Call /en/api/w/credentials/
   - Extract client_id
   ↓
9. Solve reCAPTCHA
   - Use RecaptchaService
   - Get reCAPTCHA token
   ↓
10. Get Authorization Page
   - Load authorization page
   - Extract CSRF token
   ↓
11. Submit Authorization
   - POST with CSRF + reCAPTCHA token
   - Get authorization code in redirect
   ↓
12. OAuth Callback
   - System receives code
   - Exchange code for access_token
   - Store encrypted token
```

## Required Credentials

### For Network Connection

```php
[
    'email' => 'user@example.com',     // Admitad account email
    'password' => 'SecurePass123',     // Admitad account password
]
```

### Stored After Connection

```php
[
    'email' => 'user@example.com',
    'password' => '***encrypted***',
    'client_id' => 'UTu5t2VXVDGC3ZbGlkj6J2MV06VVni',
    'client_secret' => '***encrypted***',
    'cookies' => 'session_id=...; csrf_token=...',
    'access_token' => '***encrypted***',
    'refresh_token' => '***encrypted***',
    'token_expires_at' => '2025-10-13 12:00:00',
]
```

## Usage Examples

### Test Connection

```php
use App\Services\Networks\AdmitadService;

$service = new AdmitadService();

$result = $service->testConnection([
    'email' => 'user@example.com',
    'password' => 'SecurePass123'
]);

if ($result['success']) {
    echo "Connected! Client ID: " . $result['data']['client_id'];
} else {
    echo "Failed: " . $result['message'];
}
```

### Get Authorization Code (with reCAPTCHA)

```php
$authResult = $service->getAuthorizationCode(
    $clientId,
    $clientSecret,
    $cookies
);

if ($authResult['success']) {
    $code = $authResult['code'];
    // Redirect to callback: /admitad/callback?code=...
}
```

### Exchange Code for Token

```php
$tokenResult = $service->exchangeCodeForToken(
    $code,
    $clientId,
    $clientSecret
);

if ($tokenResult['success']) {
    $accessToken = $tokenResult['access_token'];
    $refreshToken = $tokenResult['refresh_token'];
}
```

## reCAPTCHA Service

### Standalone Usage

```php
use App\Services\RecaptchaService;

$captchaService = new RecaptchaService();

$token = $captchaService->solveRecaptchaV3(
    'https://store.admitad.com/...',  // Website URL
    '6LfcGc4UAAAAAJUHmqqqR5cybEkn_N7QeS8nk_U9',  // Site key
    'myverify',  // Page action
    0.3  // Min score
);

if ($token) {
    echo "reCAPTCHA solved: " . $token;
} else {
    echo "Failed to solve reCAPTCHA";
}
```

### Configuration

```php
// In RecaptchaService.php
private const CLIENT_KEY = 'OMO_IIVGP6TSCKOANJDRBOTZWKJJMS2JIHOTFJAKDVRENA1GD2GVHSF6GULCPFNSIZ1756818806';
private const CREATE_URL = 'https://api.omocaptcha.com/v2/createTask';
private const RESULT_URL = 'https://api.omocaptcha.com/v2/getTaskResult';
private const POLL_INTERVAL_SECONDS = 5;
private const MAX_WAIT_SECONDS = 120;
```

## OAuth Callback

### Route

```php
// routes/web.php
Route::get('/admitad/callback', [NetworkController::class, 'admitadCallback'])->name('admitad.callback');
```

### Callback URL Configuration

**Important**: Update the following URLs in your code to match your domain:

1. **In AdmitadService.php**:
```php
'redirect_uri' => url('/admitad/callback')
```

2. **In Admitad OAuth App Settings**:
- Callback URL: `https://yourdomain.com/admitad/callback`

### Testing Callback Locally

```bash
# Use ngrok or similar for local testing
ngrok http 8000

# Update callback URL to ngrok URL
https://abc123.ngrok.io/admitad/callback
```

## Security Features

### 1. Encrypted Credentials
All sensitive data is encrypted before storage:
- Passwords
- Access tokens
- Refresh tokens
- Client secrets

### 2. Cookie Management
- Cookies are collected throughout OAuth flow
- Used for maintaining session with Admitad
- Stored securely in connection credentials

### 3. Token Expiration
- Tokens have expiration time
- System can refresh tokens automatically (TODO)

## Error Handling

### Common Errors

**1. "Failed to solve reCAPTCHA"**
- Check OmoCaptcha API key
- Verify reCAPTCHA site key
- Check API balance

**2. "Authentication failed - invalid credentials"**
- Verify email and password
- Check if account is active
- Try logging in manually first

**3. "Authorization code not found"**
- reCAPTCHA might have failed
- CSRF token expired
- Try again with fresh session

**4. "Token exchange failed"**
- Check client_id and client_secret
- Verify authorization code hasn't expired
- Check callback URL matches registered URL

## Logs

### Enable Detailed Logging

```php
// In AdmitadService.php
Log::info('Admitad: Step 1 - Loading login page');
Log::info('Admitad: Step 2 - Sending login credentials');
// ... etc

// In RecaptchaService.php
Log::info("RecaptchaService: Task created with ID: {$taskId}");
Log::info("RecaptchaService: Polling attempt #{$attempt}");
```

### View Logs

```bash
tail -f storage/logs/laravel.log | grep -i admitad
tail -f storage/logs/laravel.log | grep -i recaptcha
```

## Database Schema

### NetworkConnection Table

```php
[
    'user_id' => 1,
    'network_id' => 5,  // Admitad network ID
    'connection_name' => 'My Admitad Account',
    'api_endpoint' => 'https://store.admitad.com',
    'credentials' => [
        'email' => 'user@example.com',
        'password' => '***encrypted***',
        'client_id' => 'UTu5t2VXV...',
        'client_secret' => '***encrypted***',
        'access_token' => '***encrypted***',
        'refresh_token' => '***encrypted***',
        'token_expires_at' => '2025-10-13 12:00:00',
        'cookies' => 'session_id=...',
    ],
    'status' => 'active',
    'is_connected' => true,
    'connected_at' => '2025-10-12 10:00:00',
    'last_sync' => null,
]
```

## Testing

### Manual Testing Steps

1. **Test reCAPTCHA Service**:
```php
php artisan tinker
$service = new App\Services\RecaptchaService();
$token = $service->solveRecaptchaV3('https://store.admitad.com/...', '6LfcGc4UAAAAAJUHmqqqR5cybEkn_N7QeS8nk_U9');
echo $token;
```

2. **Test Admitad Connection**:
```php
php artisan tinker
$service = new App\Services\Networks\AdmitadService();
$result = $service->testConnection([
    'email' => 'test@example.com',
    'password' => 'password123'
]);
print_r($result);
```

3. **Test Complete Flow**:
- Create network connection via UI
- Enter email and password
- Click "Connect"
- System should handle OAuth flow
- Check callback receives access_token

## Future Enhancements

### TODO

- [ ] Implement token refresh mechanism
- [ ] Add support for multiple Admitad accounts per user
- [ ] Implement data sync from Admitad API
- [ ] Add webhook support for real-time updates
- [ ] Cache reCAPTCHA tokens for reuse
- [ ] Add retry logic for failed requests
- [ ] Implement rate limiting
- [ ] Add monitoring and alerts

## Troubleshooting

### Issue: Captcha Always Fails

**Solution**: 
- Check OmoCaptcha balance
- Verify API key is correct
- Increase MAX_WAIT_SECONDS if needed

### Issue: OAuth Redirect Loop

**Solution**:
- Clear cookies
- Start fresh session
- Check if account needs email verification

### Issue: Callback Not Receiving Code

**Solution**:
- Verify callback URL in Admitad app settings
- Check if state parameter matches
- Look for error parameters in callback URL

## Support

For issues or questions:
- Check `storage/logs/laravel.log`
- Review this documentation
- Test with manual OAuth flow first
- Contact system administrator

---

**Last Updated**: October 12, 2025
**Version**: 1.0.0

