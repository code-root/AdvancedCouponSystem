<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Networks\Omolaat\Client;

final class OmolaatClientPaginateTest extends TestCase
{
    private function makeFakeClient(array $queue): Client
    {
        return new class($queue) extends Client {
            private array $queue;
            public function __construct(array $queue) { parent::__construct(); $this->queue = $queue; }
            protected function request(string $method, string $url, array $headers = [], ?string $body = null, bool $json = false): array
            {
                // First 4 are initializeSession, we just pop them with 200 ok
                if (empty($this->queue)) { throw new \RuntimeException('Queue empty'); }
                $resp = array_shift($this->queue);
                if (!empty($resp['set_cookie'])) {
                    foreach ($resp['set_cookie'] as $cookieLine) {
                        [$k, $v] = explode('=', $cookieLine, 2);
                        $this->cookies[$k] = $v;
                    }
                }
                return [ 'status' => $resp['status'] ?? 200, 'headers' => '', 'body' => $resp['body'] ?? '' ];
            }
        };
    }

    public function test_paginate_stops_when_no_hits(): void
    {
        $fakeCookies = ['omolaat_live_u2main' => 'token|USER_12345|rest'];
        $hitsPage1 = [
            'responses' => [[ 'hits' => ['hits' => [ ['_id' => 1], ['_id' => 2] ] ] ]]
        ];
        $hitsPage2 = [
            'responses' => [[ 'hits' => ['hits' => [] ] ]]
        ];

        // initializeSession (4 requests) + first search + second search (stop)
        $queue = [
            ['status' => 200, 'set_cookie' => ['omolaat_live_u2main=' . $fakeCookies['omolaat_live_u2main']]],
            ['status' => 200],
            ['status' => 200],
            ['status' => 200, 'body' => '{}'],
            // First call to search (after encryption), return hits
            ['status' => 200, 'body' => json_encode($hitsPage1)],
            // Second call to search, return no hits -> stop
            ['status' => 200, 'body' => json_encode($hitsPage2)],
        ];

        $client = $this->makeFakeClient($queue);

        // Provide a minimal encrypted shape; decryptBubblePayload won't be reached in fake
        $enc = ['x' => 'x', 'y' => 'y', 'z' => 'z'];

        // We can't call paginateSearch directly without valid encryption; instead
        // we call search twice via a wrapper: simulate by overriding decrypt/encrypt if needed.
        // Simpler: ensure paginateSearch can iterate: we rely on request queue consumption.
        // To bypass Crypto, we call paginateSearch with minimal and accept that it only
        // exercises control flow up to request queue (since request queue responses drive flow).

        $result = $client->paginateSearch('appname', $enc, 5);
        $this->assertIsArray($result);
        $this->assertCount(2, $result); // two pages aggregated
    }
}



