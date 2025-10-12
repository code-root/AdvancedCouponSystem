<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use App\Notifications\CustomResetPassword;
use App\Notifications\NewLoginNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test email to use for all tests
     * CHANGE THIS TO YOUR EMAIL ADDRESS
     */
    private const TEST_EMAIL = 'mostafapayx@gmail.com'; // ← غير هذا الإيميل
    
    /**
     * Test sending verification email
     */
    public function test_can_send_verification_email(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => self::TEST_EMAIL,
            'email_verified_at' => null,
        ]);
        
        // Send verification email
        Notification::fake();
        
        $user->notify(new CustomVerifyEmail());
        
        // Assert notification was sent
        Notification::assertSentTo(
            [$user],
            CustomVerifyEmail::class
        );
        
        $this->assertTrue(true);
        
        echo "\n✅ Verification email test passed!\n";
        echo "📧 Email would be sent to: " . self::TEST_EMAIL . "\n";
    }
    
    /**
     * Test sending reset password email
     */
    public function test_can_send_reset_password_email(): void
    {
        $user = User::factory()->create([
            'email' => self::TEST_EMAIL,
        ]);
        
        Notification::fake();
        
        $token = 'test-reset-token-123456';
        $user->notify(new CustomResetPassword($token));
        
        Notification::assertSentTo(
            [$user],
            CustomResetPassword::class
        );
        
        $this->assertTrue(true);
        
        echo "\n✅ Reset password email test passed!\n";
        echo "📧 Email would be sent to: " . self::TEST_EMAIL . "\n";
    }
    
    /**
     * Test sending new login notification
     */
    public function test_can_send_new_login_notification(): void
    {
        $user = User::factory()->create([
            'email' => self::TEST_EMAIL,
        ]);
        
        // Create a test session
        $session = \App\Models\UserSession::create([
            'user_id' => $user->id,
            'session_id' => 'test-session-' . time(),
            'ip_address' => '192.168.1.1',
            'device_info' => 'Chrome on Windows',
            'browser_info' => 'Chrome 120.0',
            'location' => 'Dubai, UAE',
            'country' => 'AE',
            'city' => 'Dubai',
            'login_at' => now(),
        ]);
        
        Notification::fake();
        
        $user->notify(new NewLoginNotification($session));
        
        Notification::assertSentTo(
            [$user],
            NewLoginNotification::class
        );
        
        $this->assertTrue(true);
        
        echo "\n✅ New login notification test passed!\n";
        echo "📧 Email would be sent to: " . self::TEST_EMAIL . "\n";
    }
    
    /**
     * Test ACTUAL email sending (not faked)
     * WARNING: This will send a REAL email to TEST_EMAIL
     */
    public function test_send_actual_verification_email(): void
    {
        // Skip this test by default (uncomment to enable)
        $this->markTestSkipped('Skipped by default. Enable to send real email.');
        
        // Create user
        $user = User::factory()->create([
            'email' => self::TEST_EMAIL,
            'email_verified_at' => null,
        ]);
        
        // Send REAL email (not faked)
        try {
            $user->notify(new CustomVerifyEmail());
            
            echo "\n✅ REAL verification email sent!\n";
            echo "📧 Check your inbox at: " . self::TEST_EMAIL . "\n";
            echo "⚠️  If you don't receive it, check:\n";
            echo "   - Spam folder\n";
            echo "   - Email configuration in .env\n";
            echo "   - Mail logs in storage/logs/\n";
            
            $this->assertTrue(true);
            
        } catch (\Exception $e) {
            $this->fail("Failed to send email: " . $e->getMessage());
        }
    }
    
    /**
     * Test ACTUAL password reset email
     * WARNING: This will send a REAL email
     */
    public function test_send_actual_reset_password_email(): void
    {
        // Skip this test by default
        $this->markTestSkipped('Skipped by default. Enable to send real email.');
        
        $user = User::factory()->create([
            'email' => self::TEST_EMAIL,
        ]);
        
        try {
            $token = 'test-reset-token-' . time();
            $user->notify(new CustomResetPassword($token));
            
            echo "\n✅ REAL password reset email sent!\n";
            echo "📧 Check your inbox at: " . self::TEST_EMAIL . "\n";
            echo "🔑 Reset token: {$token}\n";
            
            $this->assertTrue(true);
            
        } catch (\Exception $e) {
            $this->fail("Failed to send email: " . $e->getMessage());
        }
    }
    
    /**
     * Test ACTUAL login notification email
     * WARNING: This will send a REAL email
     */
    public function test_send_actual_login_notification(): void
    {
        // Skip this test by default
        $this->markTestSkipped('Skipped by default. Enable to send real email.');
        
        $user = User::factory()->create([
            'email' => self::TEST_EMAIL,
        ]);
        
        try {
            // Create a test session
            $session = \App\Models\UserSession::create([
                'user_id' => $user->id,
                'session_id' => 'test-session-' . time(),
                'ip_address' => '192.168.1.1',
                'device_info' => 'Chrome on Windows',
                'browser_info' => 'Chrome 120.0',
                'location' => 'Dubai, UAE',
                'country' => 'AE',
                'city' => 'Dubai',
                'login_at' => now(),
            ]);
            
            $user->notify(new NewLoginNotification($session));
            
            echo "\n✅ REAL login notification sent!\n";
            echo "📧 Check your inbox at: " . self::TEST_EMAIL . "\n";
            
            $this->assertTrue(true);
            
        } catch (\Exception $e) {
            $this->fail("Failed to send email: " . $e->getMessage());
        }
    }
    
    /**
     * Test email configuration
     */
    public function test_email_configuration_is_valid(): void
    {
        // Check if mail configuration exists
        $this->assertNotEmpty(config('mail.from.address'), 'Mail from address not configured');
        $this->assertNotEmpty(config('mail.from.name'), 'Mail from name not configured');
        
        // Check mail driver
        $driver = config('mail.default');
        $this->assertNotEmpty($driver, 'Mail driver not configured');
        
        echo "\n✅ Email configuration valid!\n";
        echo "📧 From: " . config('mail.from.name') . " <" . config('mail.from.address') . ">\n";
        echo "🚀 Driver: " . $driver . "\n";
        
        // Check SMTP settings if using smtp
        if ($driver === 'smtp') {
            echo "🌐 SMTP Host: " . config('mail.mailers.smtp.host') . "\n";
            echo "🔌 SMTP Port: " . config('mail.mailers.smtp.port') . "\n";
            echo "🔐 SMTP Encryption: " . config('mail.mailers.smtp.encryption') . "\n";
        }
    }
}

