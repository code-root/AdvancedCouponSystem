<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\SiteSetting;
use App\Services\SiteSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class SiteSettingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear cache before each test
        Cache::flush();
    }

    /** @test */
    public function it_can_get_setting_value()
    {
        SiteSetting::create([
            'key' => 'test_setting',
            'value' => 'test_value',
            'group' => 'general',
            'locale' => 'en',
            'is_active' => true,
        ]);

        $value = SiteSettingService::get('test_setting');

        $this->assertEquals('test_value', $value);
    }

    /** @test */
    public function it_returns_default_value_when_setting_not_found()
    {
        $value = SiteSettingService::get('non_existent_setting', 'default_value');

        $this->assertEquals('default_value', $value);
    }

    /** @test */
    public function it_returns_null_when_no_default_provided()
    {
        $value = SiteSettingService::get('non_existent_setting');

        $this->assertNull($value);
    }

    /** @test */
    public function it_can_set_setting_value()
    {
        $setting = SiteSettingService::set('new_setting', 'new_value', 'general', 'en', 1);

        $this->assertInstanceOf(SiteSetting::class, $setting);
        $this->assertEquals('new_setting', $setting->key);
        $this->assertEquals('new_value', $setting->value);
        $this->assertEquals('general', $setting->group);
        $this->assertEquals('en', $setting->locale);
        $this->assertTrue($setting->is_active);
    }

    /** @test */
    public function it_can_update_existing_setting()
    {
        SiteSetting::create([
            'key' => 'existing_setting',
            'value' => 'old_value',
            'group' => 'general',
            'locale' => 'en',
            'is_active' => true,
        ]);

        $setting = SiteSettingService::set('existing_setting', 'new_value', 'general', 'en', 1);

        $this->assertEquals('new_value', $setting->value);
        $this->assertEquals(1, $setting->updated_by);
    }

    /** @test */
    public function it_can_get_settings_by_group()
    {
        SiteSetting::create([
            'key' => 'setting1',
            'value' => 'value1',
            'group' => 'branding',
            'locale' => 'en',
            'is_active' => true,
        ]);

        SiteSetting::create([
            'key' => 'setting2',
            'value' => 'value2',
            'group' => 'branding',
            'locale' => 'en',
            'is_active' => true,
        ]);

        SiteSetting::create([
            'key' => 'setting3',
            'value' => 'value3',
            'group' => 'seo',
            'locale' => 'en',
            'is_active' => true,
        ]);

        $brandingSettings = SiteSettingService::getByGroup('branding');

        $this->assertCount(2, $brandingSettings);
        $this->assertEquals('value1', $brandingSettings['setting1']);
        $this->assertEquals('value2', $brandingSettings['setting2']);
        $this->assertArrayNotHasKey('setting3', $brandingSettings);
    }

    /** @test */
    public function it_can_get_multiple_settings()
    {
        SiteSetting::create([
            'key' => 'setting1',
            'value' => 'value1',
            'group' => 'general',
            'locale' => 'en',
            'is_active' => true,
        ]);

        SiteSetting::create([
            'key' => 'setting2',
            'value' => 'value2',
            'group' => 'general',
            'locale' => 'en',
            'is_active' => true,
        ]);

        $settings = SiteSettingService::getMultiple(['setting1', 'setting2', 'non_existent']);

        $this->assertEquals('value1', $settings['setting1']);
        $this->assertEquals('value2', $settings['setting2']);
        $this->assertNull($settings['non_existent']);
    }

    /** @test */
    public function it_can_set_multiple_settings()
    {
        $settings = [
            'setting1' => 'value1',
            'setting2' => 'value2',
            'setting3' => 'value3',
        ];

        SiteSettingService::setMultiple($settings, 'general', 'en', 1);

        $this->assertDatabaseHas('site_settings', [
            'key' => 'setting1',
            'value' => 'value1',
            'group' => 'general',
            'locale' => 'en',
        ]);

        $this->assertDatabaseHas('site_settings', [
            'key' => 'setting2',
            'value' => 'value2',
            'group' => 'general',
            'locale' => 'en',
        ]);

        $this->assertDatabaseHas('site_settings', [
            'key' => 'setting3',
            'value' => 'value3',
            'group' => 'general',
            'locale' => 'en',
        ]);
    }

    /** @test */
    public function it_can_clear_cache_for_specific_setting()
    {
        SiteSetting::create([
            'key' => 'cached_setting',
            'value' => 'cached_value',
            'group' => 'general',
            'locale' => 'en',
            'is_active' => true,
        ]);

        // Get setting to cache it
        SiteSettingService::get('cached_setting');

        // Update setting directly in database
        SiteSetting::where('key', 'cached_setting')->update(['value' => 'updated_value']);

        // Clear cache
        SiteSettingService::clearCache('cached_setting');

        // Get setting again - should get updated value
        $value = SiteSettingService::get('cached_setting');

        $this->assertEquals('updated_value', $value);
    }

    /** @test */
    public function it_can_get_branding_settings()
    {
        SiteSetting::create([
            'key' => 'site_name',
            'value' => 'Test Site',
            'group' => 'branding',
            'locale' => 'en',
            'is_active' => true,
        ]);

        SiteSetting::create([
            'key' => 'logo',
            'value' => '/images/logo.png',
            'group' => 'branding',
            'locale' => 'en',
            'is_active' => true,
        ]);

        $brandingSettings = SiteSettingService::getBrandingSettings();

        $this->assertEquals('Test Site', $brandingSettings['site_name']);
        $this->assertEquals('/images/logo.png', $brandingSettings['logo']);
    }

    /** @test */
    public function it_can_get_seo_settings()
    {
        SiteSetting::create([
            'key' => 'meta_description',
            'value' => 'Test description',
            'group' => 'seo',
            'locale' => 'en',
            'is_active' => true,
        ]);

        $seoSettings = SiteSettingService::getSeoSettings();

        $this->assertEquals('Test description', $seoSettings['meta_description']);
    }

    /** @test */
    public function it_can_get_site_name()
    {
        SiteSetting::create([
            'key' => 'site_name',
            'value' => 'My Test Site',
            'group' => 'branding',
            'locale' => 'en',
            'is_active' => true,
        ]);

        $siteName = SiteSettingService::getSiteName();

        $this->assertEquals('My Test Site', $siteName);
    }

    /** @test */
    public function it_returns_default_site_name_when_not_set()
    {
        $siteName = SiteSettingService::getSiteName();

        $this->assertEquals('AdvancedCouponSystem', $siteName);
    }

    /** @test */
    public function it_can_get_site_logo()
    {
        SiteSetting::create([
            'key' => 'logo_light',
            'value' => '/images/logo-light.png',
            'group' => 'branding',
            'locale' => 'en',
            'is_active' => true,
        ]);

        $logo = SiteSettingService::getSiteLogo('light');

        $this->assertEquals('/images/logo-light.png', $logo);
    }

    /** @test */
    public function it_returns_default_logo_when_not_set()
    {
        $logo = SiteSettingService::getSiteLogo();

        $this->assertEquals('/images/logo-tr.png', $logo);
    }

    /** @test */
    public function it_can_get_favicon()
    {
        SiteSetting::create([
            'key' => 'favicon',
            'value' => '/images/custom-favicon.ico',
            'group' => 'branding',
            'locale' => 'en',
            'is_active' => true,
        ]);

        $favicon = SiteSettingService::getFavicon();

        $this->assertEquals('/images/custom-favicon.ico', $favicon);
    }

    /** @test */
    public function it_can_get_meta_description()
    {
        SiteSetting::create([
            'key' => 'meta_description',
            'value' => 'Custom meta description',
            'group' => 'branding',
            'locale' => 'en',
            'is_active' => true,
        ]);

        $description = SiteSettingService::getMetaDescription();

        $this->assertEquals('Custom meta description', $description);
    }

    /** @test */
    public function it_can_get_meta_author()
    {
        SiteSetting::create([
            'key' => 'meta_author',
            'value' => 'Custom Author',
            'group' => 'branding',
            'locale' => 'en',
            'is_active' => true,
        ]);

        $author = SiteSettingService::getMetaAuthor();

        $this->assertEquals('Custom Author', $author);
    }

    /** @test */
    public function it_can_check_maintenance_mode()
    {
        SiteSetting::create([
            'key' => 'maintenance_mode',
            'value' => '1',
            'group' => 'general',
            'locale' => 'en',
            'is_active' => true,
        ]);

        $isMaintenanceMode = SiteSettingService::isMaintenanceMode();

        $this->assertTrue($isMaintenanceMode);
    }

    /** @test */
    public function it_returns_false_for_maintenance_mode_when_not_set()
    {
        $isMaintenanceMode = SiteSettingService::isMaintenanceMode();

        $this->assertFalse($isMaintenanceMode);
    }

    /** @test */
    public function it_can_get_timezone_setting()
    {
        SiteSetting::create([
            'key' => 'timezone',
            'value' => 'America/New_York',
            'group' => 'general',
            'locale' => 'en',
            'is_active' => true,
        ]);

        $timezone = SiteSettingService::getTimezone();

        $this->assertEquals('America/New_York', $timezone);
    }

    /** @test */
    public function it_returns_default_timezone_when_not_set()
    {
        $timezone = SiteSettingService::getTimezone();

        $this->assertEquals('UTC', $timezone);
    }

    /** @test */
    public function it_can_get_currency_setting()
    {
        SiteSetting::create([
            'key' => 'currency',
            'value' => 'EUR',
            'group' => 'general',
            'locale' => 'en',
            'is_active' => true,
        ]);

        $currency = SiteSettingService::getCurrency();

        $this->assertEquals('EUR', $currency);
    }

    /** @test */
    public function it_returns_default_currency_when_not_set()
    {
        $currency = SiteSettingService::getCurrency();

        $this->assertEquals('USD', $currency);
    }

    /** @test */
    public function it_can_get_currency_symbol()
    {
        SiteSetting::create([
            'key' => 'currency_symbol',
            'value' => '€',
            'group' => 'general',
            'locale' => 'en',
            'is_active' => true,
        ]);

        $symbol = SiteSettingService::getCurrencySymbol();

        $this->assertEquals('€', $symbol);
    }

    /** @test */
    public function it_returns_default_currency_symbol_when_not_set()
    {
        $symbol = SiteSettingService::getCurrencySymbol();

        $this->assertEquals('$', $symbol);
    }

    /** @test */
    public function it_can_get_language_setting()
    {
        SiteSetting::create([
            'key' => 'language',
            'value' => 'ar',
            'group' => 'general',
            'locale' => 'en',
            'is_active' => true,
        ]);

        $language = SiteSettingService::getLanguage();

        $this->assertEquals('ar', $language);
    }

    /** @test */
    public function it_returns_default_language_when_not_set()
    {
        $language = SiteSettingService::getLanguage();

        $this->assertEquals('en', $language);
    }

    /** @test */
    public function it_can_get_date_format()
    {
        SiteSetting::create([
            'key' => 'date_format',
            'value' => 'd/m/Y',
            'group' => 'general',
            'locale' => 'en',
            'is_active' => true,
        ]);

        $dateFormat = SiteSettingService::getDateFormat();

        $this->assertEquals('d/m/Y', $dateFormat);
    }

    /** @test */
    public function it_returns_default_date_format_when_not_set()
    {
        $dateFormat = SiteSettingService::getDateFormat();

        $this->assertEquals('Y-m-d', $dateFormat);
    }

    /** @test */
    public function it_can_get_time_format()
    {
        SiteSetting::create([
            'key' => 'time_format',
            'value' => 'h:i A',
            'group' => 'general',
            'locale' => 'en',
            'is_active' => true,
        ]);

        $timeFormat = SiteSettingService::getTimeFormat();

        $this->assertEquals('h:i A', $timeFormat);
    }

    /** @test */
    public function it_returns_default_time_format_when_not_set()
    {
        $timeFormat = SiteSettingService::getTimeFormat();

        $this->assertEquals('H:i:s', $timeFormat);
    }

    /** @test */
    public function it_can_bulk_update_settings()
    {
        $settings = [
            [
                'key' => 'setting1',
                'value' => 'value1',
                'group' => 'general',
                'locale' => 'en',
            ],
            [
                'key' => 'setting2',
                'value' => 'value2',
                'group' => 'branding',
                'locale' => 'en',
            ],
        ];

        SiteSettingService::bulkUpdate($settings, 1);

        $this->assertDatabaseHas('site_settings', [
            'key' => 'setting1',
            'value' => 'value1',
            'group' => 'general',
            'updated_by' => 1,
        ]);

        $this->assertDatabaseHas('site_settings', [
            'key' => 'setting2',
            'value' => 'value2',
            'group' => 'branding',
            'updated_by' => 1,
        ]);
    }

    /** @test */
    public function it_can_get_setting_with_fallback_locale()
    {
        // Create setting in default locale
        SiteSetting::create([
            'key' => 'test_setting',
            'value' => 'default_value',
            'group' => 'general',
            'locale' => 'en',
            'is_active' => true,
        ]);

        // Try to get setting in different locale
        $value = SiteSettingService::getWithFallback('test_setting', null, 'ar');

        $this->assertEquals('default_value', $value);
    }

    /** @test */
    public function it_returns_null_when_no_fallback_available()
    {
        $value = SiteSettingService::getWithFallback('non_existent', null, 'ar');

        $this->assertNull($value);
    }
}