<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SiteSettingService
{
    /**
     * Cache key prefix for site settings
     */
    const CACHE_PREFIX = 'site_setting_';
    
    /**
     * Cache TTL in minutes
     */
    const CACHE_TTL = 60;

    /**
     * Get a site setting value by key with caching
     */
    public static function get(string $key, $default = null, string $locale = 'en'): mixed
    {
        $cacheKey = self::CACHE_PREFIX . $key . '_' . $locale;
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default, $locale) {
            $setting = SiteSetting::where('key', $key)
                ->where('locale', $locale)
                ->where('is_active', true)
                ->first();
                
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a site setting value and clear cache
     */
    public static function set(string $key, $value, string $group = 'general', string $locale = 'en', ?int $adminId = null): SiteSetting
    {
        $setting = SiteSetting::updateOrCreate(
            [
                'key' => $key,
                'locale' => $locale,
            ],
            [
                'value' => $value,
                'group' => $group,
                'is_active' => true,
                'last_modified_at' => now(),
                'updated_by' => $adminId,
                'created_by' => $adminId,
            ]
        );

        // Clear cache for this setting
        self::clearCache($key, $locale);
        
        // Log the change
        Log::info('Site setting updated', [
            'key' => $key,
            'value' => $value,
            'group' => $group,
            'locale' => $locale,
            'admin_id' => $adminId
        ]);

        return $setting;
    }

    /**
     * Get all settings by group with caching
     */
    public static function getByGroup(string $group, string $locale = 'en'): array
    {
        $cacheKey = self::CACHE_PREFIX . 'group_' . $group . '_' . $locale;
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($group, $locale) {
            return SiteSetting::byGroup($group)
                ->byLocale($locale)
                ->active()
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /**
     * Get multiple settings by keys with caching
     */
    public static function getMultiple(array $keys, string $locale = 'en'): array
    {
        $result = [];
        
        foreach ($keys as $key) {
            $result[$key] = self::get($key, null, $locale);
        }
        
        return $result;
    }

    /**
     * Set multiple settings at once
     */
    public static function setMultiple(array $settings, string $group = 'general', string $locale = 'en', ?int $adminId = null): void
    {
        foreach ($settings as $key => $value) {
            self::set($key, $value, $group, $locale, $adminId);
        }
    }

    /**
     * Clear cache for a specific setting
     */
    public static function clearCache(string $key, string $locale = 'en'): void
    {
        $cacheKey = self::CACHE_PREFIX . $key . '_' . $locale;
        Cache::forget($cacheKey);
        
        // Also clear group cache
        $groupCacheKey = self::CACHE_PREFIX . 'group_*_' . $locale;
        Cache::forget($groupCacheKey);
    }

    /**
     * Clear all site settings cache
     */
    public static function clearAllCache(): void
    {
        $pattern = self::CACHE_PREFIX . '*';
        
        // Get all cache keys matching the pattern
        $keys = Cache::getRedis()->keys($pattern);
        
        if (!empty($keys)) {
            Cache::getRedis()->del($keys);
        }
    }

    /**
     * Get branding settings
     */
    public static function getBrandingSettings(string $locale = 'en'): array
    {
        return self::getByGroup('branding', $locale);
    }

    /**
     * Get SEO settings
     */
    public static function getSeoSettings(string $locale = 'en'): array
    {
        return self::getByGroup('seo', $locale);
    }

    /**
     * Get SMTP settings
     */
    public static function getSmtpSettings(string $locale = 'en'): array
    {
        return self::getByGroup('smtp', $locale);
    }

    /**
     * Get general settings
     */
    public static function getGeneralSettings(string $locale = 'en'): array
    {
        return self::getByGroup('general', $locale);
    }

    /**
     * Get site name
     */
    public static function getSiteName(string $locale = 'en'): string
    {
        return self::get('site_name', 'AdvancedCouponSystem', $locale);
    }

    /**
     * Get site logo
     */
    public static function getSiteLogo(string $type = 'default', string $locale = 'en'): string
    {
        $key = match($type) {
            'light' => 'logo_light',
            'dark' => 'logo_dark',
            'small' => 'logo_sm',
            default => 'logo'
        };
        
        return self::get($key, '/images/logo-tr.png', $locale);
    }

    /**
     * Get favicon
     */
    public static function getFavicon(string $locale = 'en'): string
    {
        return self::get('favicon', '/images/favicon.ico', $locale);
    }

    /**
     * Get meta description
     */
    public static function getMetaDescription(string $locale = 'en'): string
    {
        return self::get('meta_description', 'Advanced Coupon and Affiliate Marketing System', $locale);
    }

    /**
     * Get meta author
     */
    public static function getMetaAuthor(string $locale = 'en'): string
    {
        return self::get('meta_author', 'AdvancedCouponSystem', $locale);
    }

    /**
     * Check if maintenance mode is enabled
     */
    public static function isMaintenanceMode(): bool
    {
        return (bool) self::get('maintenance_mode', false);
    }

    /**
     * Get timezone setting
     */
    public static function getTimezone(): string
    {
        return self::get('timezone', 'UTC');
    }

    /**
     * Get currency setting
     */
    public static function getCurrency(): string
    {
        return self::get('currency', 'USD');
    }

    /**
     * Get currency symbol
     */
    public static function getCurrencySymbol(): string
    {
        return self::get('currency_symbol', '$');
    }

    /**
     * Get language setting
     */
    public static function getLanguage(): string
    {
        return self::get('language', 'en');
    }

    /**
     * Get date format
     */
    public static function getDateFormat(): string
    {
        return self::get('date_format', 'Y-m-d');
    }

    /**
     * Get time format
     */
    public static function getTimeFormat(): string
    {
        return self::get('time_format', 'H:i:s');
    }

    /**
     * Warm up cache for all settings
     */
    public static function warmUpCache(): void
    {
        $groups = ['branding', 'seo', 'smtp', 'general'];
        $locales = ['en']; // Add more locales as needed
        
        foreach ($groups as $group) {
            foreach ($locales as $locale) {
                self::getByGroup($group, $locale);
            }
        }
    }

    /**
     * Get settings with fallback to default locale
     */
    public static function getWithFallback(string $key, $default = null, string $locale = 'en'): mixed
    {
        $value = self::get($key, null, $locale);
        
        // If not found in requested locale, try default locale
        if ($value === null && $locale !== 'en') {
            $value = self::get($key, $default, 'en');
        }
        
        return $value ?? $default;
    }

    /**
     * Bulk update settings from array
     */
    public static function bulkUpdate(array $settings, ?int $adminId = null): void
    {
        foreach ($settings as $setting) {
            if (isset($setting['key']) && isset($setting['value'])) {
                self::set(
                    $setting['key'],
                    $setting['value'],
                    $setting['group'] ?? 'general',
                    $setting['locale'] ?? 'en',
                    $adminId
                );
            }
        }
    }
}



