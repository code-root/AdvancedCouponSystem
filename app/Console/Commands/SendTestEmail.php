<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use App\Notifications\CustomResetPassword;

class SendTestEmail extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'email:test 
                            {email? : The email address to send test email to}
                            {--type=verify : Type of email (verify, reset, login, simple)}';

    /**
     * The console command description.
     */
    protected $description = 'Send a test email to verify email configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $type = $this->option('type');
        
        // If no email provided, ask for it
        if (!$email) {
            $email = $this->ask('Enter the email address to send test email to');
        }
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('❌ Invalid email address!');
            return 1;
        }
        
        $this->info("📧 Preparing to send test email to: {$email}");
        $this->info("📝 Email type: {$type}");
        $this->newLine();
        
        try {
            switch ($type) {
                case 'verify':
                    $this->sendVerificationEmail($email);
                    break;
                    
                case 'reset':
                    $this->sendResetPasswordEmail($email);
                    break;
                    
                case 'login':
                    $this->sendLoginNotification($email);
                    break;
                    
                case 'simple':
                default:
                    $this->sendSimpleTestEmail($email);
                    break;
            }
            
            $this->newLine();
            $this->info('✅ Email sent successfully!');
            $this->info('📬 Check your inbox at: ' . $email);
            $this->newLine();
            $this->warn('⚠️  If you don\'t receive the email:');
            $this->line('   - Check spam/junk folder');
            $this->line('   - Verify .env email configuration');
            $this->line('   - Check logs: tail -f storage/logs/laravel.log');
            $this->newLine();
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email!');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            $this->warn('💡 Troubleshooting:');
            $this->line('   - Check .env file for mail configuration');
            $this->line('   - Verify SMTP credentials');
            $this->line('   - Check logs: storage/logs/laravel.log');
            
            return 1;
        }
    }
    
    /**
     * Send verification email
     */
    private function sendVerificationEmail(string $email): void
    {
        $this->info('📨 Sending verification email...');
        
        // Create or find user
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => null,
            ]
        );
        
        $user->notify(new CustomVerifyEmail());
        
        $this->line('   → Verification email queued');
    }
    
    /**
     * Send password reset email
     */
    private function sendResetPasswordEmail(string $email): void
    {
        $this->info('📨 Sending password reset email...');
        
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );
        
        $token = 'test-reset-' . time();
        $user->notify(new CustomResetPassword($token));
        
        $this->line('   → Password reset email queued');
        $this->line('   → Reset token: ' . $token);
    }
    
    /**
     * Send login notification
     */
    private function sendLoginNotification(string $email): void
    {
        $this->info('📨 Sending login notification...');
        
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );
        
        // Create test session
        $session = \App\Models\UserSession::create([
            'user_id' => $user->id,
            'session_id' => 'test-' . time(),
            'ip_address' => '192.168.1.1',
            'device_info' => 'Chrome on Windows',
            'browser_info' => 'Chrome 120.0',
            'location' => 'Dubai, UAE',
            'country' => 'AE',
            'city' => 'Dubai',
            'login_at' => now(),
        ]);
        
        $user->notify(new \App\Notifications\NewLoginNotification($session));
        
        $this->line('   → Login notification queued');
    }
    
    /**
     * Send simple test email
     */
    private function sendSimpleTestEmail(string $email): void
    {
        $this->info('📨 Sending simple test email...');
        
        Mail::raw('This is a test email from Advanced Coupon System.

The email system is configured correctly and working!

Current Time: ' . now()->format('Y-m-d H:i:s') . '
Server: ' . config('app.url') . '

If you received this email, your email configuration is working perfectly!

---
Advanced Coupon System
' . config('app.name'), function($message) use ($email) {
            $message->to($email)
                    ->subject('🧪 Test Email - ' . config('app.name'));
        });
        
        $this->line('   → Simple test email sent');
    }
}

