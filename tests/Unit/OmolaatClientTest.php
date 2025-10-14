<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Networks\Omolaat\Client;

final class OmolaatClientTest extends TestCase
{
    private function makeFakeClient(array $queue): Client
    {
        // Anonymous class to override request and feed queued responses
        return new class($queue) extends Client {
            private array $queue;
            public function __construct(array $queue)
            {
                parent::__construct();
                $this->queue = $queue;
            }
            protected function request(string $method, string $url, array $headers = [], ?string $body = null, bool $json = false): array
            {
                if (empty($this->queue)) {
                    throw new \RuntimeException('Queue empty for fake request: ' . $method . ' ' . $url);
                }
                $resp = array_shift($this->queue);
                // If response wants to set cookies, emulate Set-Cookie parsing
                if (!empty($resp['set_cookie'])) {
                    foreach ($resp['set_cookie'] as $cookieLine) {
                        [$k, $v] = explode('=', $cookieLine, 2);
                        $this->cookies[$k] = $v;
                    }
                }
                return [
                    'status' => $resp['status'] ?? 200,
                    'headers' => $resp['headers'] ?? '',
                    'body' => $resp['body'] ?? '',
                ];
            }
        };
    }

    public function test_login_success_with_fake_transport(): void
    {
        $fakeCookies = ['omolaat_live_u2main' => 'token|USER_12345|rest'];
        $queue = [
            // initializeSession sequence: GET affiliate, GET /, GET init, POST /user/hi
            ['status' => 200, 'headers' => '', 'body' => '', 'set_cookie' => ['omolaat_live_u2main=' . $fakeCookies['omolaat_live_u2main']]],
            ['status' => 200, 'headers' => '', 'body' => ''],
            ['status' => 200, 'headers' => '', 'body' => ''],
            ['status' => 200, 'headers' => '', 'body' => '{}'],
            // login workflow
            ['status' => 200, 'headers' => '', 'body' => '{"ok":true}'],
        ];

        $client = $this->makeFakeClient($queue);
        $result = $client->login('user@example.com', 'secret');

        $this->assertArrayHasKey('cookies', $result);
        $this->assertArrayHasKey('headers', $result);
        $this->assertArrayHasKey('raw', $result);
        $this->assertIsArray($result['cookies']);
        $this->assertNotEmpty($result['cookies']);
    }

    public function test_login_fails_without_cookie_user_id(): void
    {
        $queue = [
            ['status' => 200, 'headers' => '', 'body' => ''],
            ['status' => 200, 'headers' => '', 'body' => ''],
            ['status' => 200, 'headers' => '', 'body' => ''],
            ['status' => 200, 'headers' => '', 'body' => '{}'],
        ];
        $client = $this->makeFakeClient($queue);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not get user_id from cookies');
        $client->login('user@example.com', 'secret');
    }
}



