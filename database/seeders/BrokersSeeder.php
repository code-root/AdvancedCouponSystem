<?php

namespace Database\Seeders;

use App\Models\Broker;
use Illuminate\Database\Seeder;

class BrokersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brokers = [
            [
                'name' => 'boostiny',
                'display_name' => 'Boostiny',
                'description' => 'Affiliate Marketing Platform - Performance-based marketing network',
                'api_url' => 'https://api.boostiny.com/',
                'is_active' => true,
                'supported_features' => ['campaigns', 'analytics', 'real_time_tracking', 'api_integration'],
            ],
            [
                'name' => 'optimisemedia',
                'display_name' => 'OptimiseMedia',
                'description' => 'Performance Marketing Network',
                'api_url' => 'https://public.api.optimisemedia.com/v1/',
                'is_active' => true,
                'supported_features' => ['api_integration', 'performance_tracking'],
            ],
            [
                'name' => 'marketeers',
                'display_name' => 'Marketeers',
                'description' => 'Marketing Analytics Platform - Campaign optimization and data insights',
                'api_url' => 'https://api.marketeers.com/',
                'is_active' => true,
                'supported_features' => ['marketing_analytics', 'campaign_optimization', 'data_insights'],
            ],
            [
                'name' => 'digizag',
                'display_name' => 'Digizag',
                'description' => 'Digital Marketing Network - Publisher tools and performance tracking',
                'api_url' => 'https://digizag.api.hasoffers.com/Apiv3/json',
                'is_active' => true,
                'supported_features' => ['publisher_tools', 'performance_tracking', 'conversion_optimization'],
            ],
            [
                'name' => 'admitad',
                'display_name' => 'Admitad',
                'description' => 'Global Affiliate Network - Connect with thousands of advertisers',
                'api_url' => 'https://api.admitad.com/',
                'is_active' => true,
                'supported_features' => ['global_network', 'multiple_verticals', 'advanced_reporting'],
            ],
            [
                'name' => 'cpx',
                'display_name' => 'CPX',
                'description' => 'CPA Network - Performance marketing solutions',
                'api_url' => 'https://api.cpx.ae/api/auth/conversions_report_dashboard',
                'is_active' => false,
                'supported_features' => ['cpa_tracking', 'conversion_reporting'],
            ],
            [
                'name' => 'arabclicks',
                'display_name' => 'Arabclicks',
                'description' => 'Middle East Affiliate Network',
                'api_url' => 'https://arabclicks.api.hasoffers.com/Apiv3/json',
                'is_active' => true,
                'supported_features' => ['regional_network', 'arabic_support'],
            ],
            [
                'name' => 'globalnetwork',
                'display_name' => 'GlobalNetwork',
                'description' => 'International Performance Marketing Network',
                'api_url' => 'https://globalnetwork1.api.hasoffers.com/Apiv3/json',
                'is_active' => true,
                'supported_features' => ['global_reach', 'multi_currency'],
            ],
            [
                'name' => 'platformance',
                'display_name' => 'Platformance',
                'description' => 'Performance Marketing Platform - Advanced analytics and conversion tracking',
                'api_url' => 'https://login.platformance.co/publisher/performance',
                'is_active' => true,
                'supported_features' => ['advanced_analytics', 'conversion_tracking', 'multi_channel_support'],
            ],
            [
                'name' => 'iherb',
                'display_name' => 'iHerb',
                'description' => 'Health & Wellness Affiliate Program',
                'api_url' => 'https://api.partnerize.com/v3/partner/analytics/conversions',
                'is_active' => true,
                'supported_features' => ['health_wellness', 'commission_tracking'],
            ],
            [
                'name' => 'squatwolf',
                'display_name' => 'SquatWolf',
                'description' => 'Fitness & Sportswear Affiliate Program',
                'api_url' => 'https://api.squatwolf.com/',
                'is_active' => true,
                'supported_features' => ['fitness_niche', 'performance_tracking'],
            ],
            [
                'name' => 'linkaraby',
                'display_name' => 'LinkAraby',
                'description' => 'Arabic Content Affiliate Network',
                'api_url' => 'https://api.linkaraby.com/',
                'is_active' => true,
                'supported_features' => ['arabic_content', 'regional_focus'],
            ],
            [
                'name' => 'trendyol',
                'display_name' => 'Trendyol',
                'description' => 'Turkish E-commerce Affiliate Program',
                'api_url' => 'https://apigw.trendyol.com/',
                'is_active' => true,
                'supported_features' => ['ecommerce', 'turkish_market'],
            ],
            [
                'name' => 'aliexpress',
                'display_name' => 'AliExpress',
                'description' => 'Global E-commerce Affiliate Program',
                'api_url' => 'https://portals.aliexpress.com/cps/report/fetchEffectDetailNew',
                'is_active' => true,
                'supported_features' => ['global_ecommerce', 'high_volume'],
            ],
            [
                'name' => 'temu',
                'display_name' => 'Temu',
                'description' => 'Online Marketplace Affiliate Program',
                'api_url' => 'https://www.temu.com/api/link/generic_proxy/sugar/report',
                'is_active' => true,
                'supported_features' => ['marketplace', 'competitive_rates'],
            ],
            [
                'name' => 'omolaat',
                'display_name' => 'Omolaat',
                'description' => 'Gulf Region E-commerce Platform',
                'api_url' => 'https://my.omolaat.com/elasticsearch/msearch',
                'is_active' => true,
                'supported_features' => ['gulf_region', 'local_payment'],
            ],
            [
                'name' => 'globalemedia',
                'display_name' => 'GlobaleMedia',
                'description' => 'Global Performance Marketing Network',
                'api_url' => 'https://login.globalemedia.net/publisher/performance',
                'is_active' => true,
                'supported_features' => ['global_network', 'performance_based'],
            ],
        ];

        foreach ($brokers as $brokerData) {
            Broker::firstOrCreate(
                ['name' => $brokerData['name']],
                $brokerData
            );
        }

        $this->command->info('17 Brokers created successfully!');
    }
}
