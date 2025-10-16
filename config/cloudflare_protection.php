<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cloudflare Protection Settings
    |--------------------------------------------------------------------------
    |
    | These settings help bypass Cloudflare protection for network services
    |
    */

    'platformance' => [
        'enabled' => true,
        'random_delay_min' => 2, // Minimum delay in seconds
        'random_delay_max' => 5, // Maximum delay in seconds
        'use_proxy' => true, // Enable proxy support from database
        'proxy_url' => null, // Not used when using database proxies
        'user_agents' => [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
        ],
        'retry_on_cloudflare_block' => true,
        'max_retries' => 3,
    ],

    'marketeers' => [
        'enabled' => true,
        'random_delay_min' => 1,
        'random_delay_max' => 3,
        'use_proxy' => false,
        'proxy_url' => null,
    ],

    'globalemedia' => [
        'enabled' => true,
        'random_delay_min' => 1,
        'random_delay_max' => 3,
        'use_proxy' => false,
        'proxy_url' => null,
    ],
];
