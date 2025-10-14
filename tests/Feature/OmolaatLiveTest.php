<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Networks\OmolaatService;
use Illuminate\Support\Facades\Log;

class OmolaatLiveTest extends TestCase
{
    public function test_live_login_and_fetch()
    {
        // تخطّي الاختبار إذا كنا في بيئة CI/testing لحمايته
        if (env('CI') || env('APP_ENV') === 'testing') {
            $this->markTestSkipped('Skipping live test in CI/testing environment');
        }

        $email = env('OMOLAAT_EMAIL');
        $password = env('OMOLAAT_PASSWORD');

        if (empty($email) || empty($password)) {
            $this->markTestSkipped('Missing OMOLAAT_EMAIL/OMOLAAT_PASSWORD in environment');
        }

        $service = new OmolaatService();

        // 1) تسجيل الدخول
        $conn = $service->testConnection(['email' => $email, 'password' => $password]);
        if (!($conn['success'] ?? false)) {
            $this->fail('Login failed: ' . ($conn['message'] ?? '')); 
        }

        // 2) جلب البيانات ضمن نطاق افتراضي (الشهر الحالي)
        $result = $service->syncData(['email' => $email, 'password' => $password]);

        Log::info('Omolaat live fetch summary', [
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? '',
            'total' => $result['data']['coupons']['total'] ?? null,
            'count' => isset($result['data']['coupons']['data']) ? count($result['data']['coupons']['data']) : null,
        ]);

        $this->assertTrue($result['success'] ?? false, $result['message'] ?? '');
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('coupons', $result['data']);
    }
}



