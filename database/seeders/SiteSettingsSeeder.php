<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSettings = [
            // Branding Settings
            'site_name' => [
                'value' => 'AdvancedCouponSystem',
                'group' => 'branding',
            ],
            'meta_description' => [
                'value' => 'Advanced Coupon and Affiliate Marketing System for managing brokers, campaigns, and coupons',
                'group' => 'branding',
            ],
            'meta_author' => [
                'value' => 'AdvancedCouponSystem',
                'group' => 'branding',
            ],
            'favicon' => [
                'value' => '/images/favicon.ico',
                'group' => 'branding',
            ],
            'logo' => [
                'value' => '/images/logo-tr.png',
                'group' => 'branding',
            ],
            'logo_light' => [
                'value' => '/images/logo-tr.png',
                'group' => 'branding',
            ],
            'logo_dark' => [
                'value' => '/images/trakifi-m.png',
                'group' => 'branding',
            ],
            'logo_sm' => [
                'value' => '/images/trakifi-m.png',
                'group' => 'branding',
            ],

            // SEO Settings
            'google_analytics_id' => [
                'value' => '',
                'group' => 'seo',
            ],
            'google_tag_manager_id' => [
                'value' => '',
                'group' => 'seo',
            ],
            'meta_keywords' => [
                'value' => 'coupon, affiliate, marketing, campaigns, brokers',
                'group' => 'seo',
            ],
            'og_image' => [
                'value' => '/images/logo-tr.png',
                'group' => 'seo',
            ],

            // SMTP Settings
            'smtp_host' => [
                'value' => '',
                'group' => 'smtp',
            ],
            'smtp_port' => [
                'value' => '587',
                'group' => 'smtp',
            ],
            'smtp_username' => [
                'value' => '',
                'group' => 'smtp',
            ],
            'smtp_password' => [
                'value' => '',
                'group' => 'smtp',
            ],
            'smtp_encryption' => [
                'value' => 'tls',
                'group' => 'smtp',
            ],
            'smtp_from_name' => [
                'value' => 'AdvancedCouponSystem',
                'group' => 'smtp',
            ],
            'smtp_from_email' => [
                'value' => 'noreply@advancedcouponsystem.com',
                'group' => 'smtp',
            ],

            // General Settings
            'timezone' => [
                'value' => 'UTC',
                'group' => 'general',
            ],
            'date_format' => [
                'value' => 'Y-m-d',
                'group' => 'general',
            ],
            'time_format' => [
                'value' => 'H:i:s',
                'group' => 'general',
            ],
            'currency' => [
                'value' => 'USD',
                'group' => 'general',
            ],
            'currency_symbol' => [
                'value' => '$',
                'group' => 'general',
            ],
            'language' => [
                'value' => 'en',
                'group' => 'general',
            ],
            'maintenance_mode' => [
                'value' => '0',
                'group' => 'general',
            ],
        ];

        foreach ($defaultSettings as $key => $setting) {
            SiteSetting::firstOrCreate(
                ['key' => $key, 'locale' => 'en'],
                [
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                    'is_active' => true,
                    'last_modified_at' => now(),
                ]
            );
        }
    }
}