<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NetworkProxy;

class NetworkProxiesSeeder extends Seeder
{
    public function run(): void
    {
        // Prefer Webshare proxies (HTTP) provided by user
        $marketeersNetwork = 'marketeers';
        $platformanceNetwork = 'platformance';
        $globalemediaNetwork = 'globalemedia';

        $proxies = [
            'p.webshare.io:80:topqdsqw-1:jny9eo7vjwhq',
            'p.webshare.io:80:topqdsqw-2:jny9eo7vjwhq',
            'p.webshare.io:80:topqdsqw-3:jny9eo7vjwhq',
            'p.webshare.io:80:topqdsqw-4:jny9eo7vjwhq',
            'p.webshare.io:80:topqdsqw-5:jny9eo7vjwhq',
            'p.webshare.io:80:topqdsqw-6:jny9eo7vjwhq',
            'p.webshare.io:80:topqdsqw-7:jny9eo7vjwhq',
            'p.webshare.io:80:topqdsqw-8:jny9eo7vjwhq',
            'p.webshare.io:80:topqdsqw-9:jny9eo7vjwhq',
            'p.webshare.io:80:topqdsqw-10:jny9eo7vjwhq',
            'p.webshare.io:80:topqdsqw-11:jny9eo7vjwhq',
            'p.webshare.io:80:topqdsqw-12:jny9eo7vjwhq',
            'p.webshare.io:80:topqdsqw-13:jny9eo7vjwhq',
            'p.webshare.io:80:topqdsqw-14:jny9eo7vjwhq',
            'p.webshare.io:80:topqdsqw-15:jny9eo7vjwhq',
            'p.webshare.io:80:topqdsqw-16:jny9eo7vjwhq',
            'p.webshare.io:80:topqdsqw-17:jny9eo7vjwhq',
            'p.webshare.io:80:topqdsqw-18:jny9eo7vjwhq',
            'p.webshare.io:80:topqdsqw-19:jny9eo7vjwhq',
            'p.webshare.io:80:topqdsqw-20:jny9eo7vjwhq',
        ];

        // Helper to seed a list for a specific network
        $seedFor = function (string $networkName) use ($proxies) {
            foreach ($proxies as $proxy) {
                [$host, $port, $user, $pass] = explode(':', $proxy);
                NetworkProxy::updateOrCreate(
                    [
                        'host' => $host,
                        'port' => (int)$port,
                        'username' => $user,
                        'network' => $networkName,
                    ],
                    [
                        'password' => $pass,
                        'scheme' => 'http',
                        'is_active' => true,
                        'fail_count' => 0,
                    ]
                );
            }
        };

        // Seed for all three networks
        $seedFor($marketeersNetwork);
        $seedFor($platformanceNetwork);
        $seedFor($globalemediaNetwork);
    }
}


