<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test 
                            {email? : Email address to send test to}
                            {--driver= : Mail driver to test (gmail, sendgrid, mailgun)}
                            {--config : Show current email configuration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration and send test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Advanced Coupon System - Email Test Tool');
        $this->line('');

        // Show configuration if requested
        if ($this->option('config')) {
            $this->showConfiguration();
            return;
        }

        // Get email address
        $email = $this->argument('email') ?? $this->ask('Enter email address to send test to');
        
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('❌ Invalid email address');
            return 1;
        }

        // Get driver
        $driver = $this->option('driver') ?? $this->choice(
            'Select mail driver to test',
            ['gmail', 'sendgrid', 'mailgun', 'current'],
            'current'
        );

        if ($driver !== 'current') {
            Config::set('mail.default', $driver);
            $this->info("🔄 Switched to {$driver} driver");
        }

        // Test configuration
        if (!$this->testConfiguration()) {
            return 1;
        }

        // Send test email
        if ($this->confirm('Send test email?', true)) {
            $this->sendTestEmail($email);
        }

        return 0;
    }

    /**
     * Show current email configuration
     */
    private function showConfiguration(): void
    {
        $this->info('📧 Current Email Configuration:');
        $this->line('');

        $driver = config('mail.default');
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        $this->table(
            ['Setting', 'Value'],
            [
                ['Driver', $driver],
                ['From Address', $fromAddress],
                ['From Name', $fromName],
            ]
        );

        if ($driver === 'gmail') {
            $this->showGmailConfig();
        } elseif ($driver === 'sendgrid') {
            $this->showSendGridConfig();
        } elseif ($driver === 'mailgun') {
            $this->showMailgunConfig();
        } else {
            $this->showSmtpConfig();
        }
    }

    /**
     * Show Gmail configuration
     */
    private function showGmailConfig(): void
    {
        $this->line('');
        $this->info('📮 Gmail SMTP Configuration:');
        
        $this->table(
            ['Setting', 'Value'],
            [
                ['Host', 'smtp.gmail.com'],
                ['Port', '587'],
                ['Encryption', 'TLS'],
                ['Username', config('mail.mailers.gmail.username') ?: 'Not set'],
                ['Password', config('mail.mailers.gmail.password') ? '***' : 'Not set'],
            ]
        );

        $this->line('');
        $this->warn('⚠️  For Gmail, you need to:');
        $this->line('   1. Enable 2-Factor Authentication');
        $this->line('   2. Generate App Password: https://myaccount.google.com/apppasswords');
        $this->line('   3. Use App Password (not regular password)');
    }

    /**
     * Show SendGrid configuration
     */
    private function showSendGridConfig(): void
    {
        $this->line('');
        $this->info('📮 SendGrid Configuration:');
        
        $this->table(
            ['Setting', 'Value'],
            [
                ['Host', 'smtp.sendgrid.net'],
                ['Port', '587'],
                ['Encryption', 'TLS'],
                ['Username', 'apikey'],
                ['API Key', config('mail.mailers.sendgrid.password') ? '***' : 'Not set'],
            ]
        );

        $this->line('');
        $this->warn('⚠️  For SendGrid, you need to:');
        $this->line('   1. Sign up at: https://sendgrid.com');
        $this->line('   2. Create API Key in Dashboard');
        $this->line('   3. Set SENDGRID_API_KEY in .env');
    }

    /**
     * Show Mailgun configuration
     */
    private function showMailgunConfig(): void
    {
        $this->line('');
        $this->info('📮 Mailgun Configuration:');
        
        $this->table(
            ['Setting', 'Value'],
            [
                ['Host', 'smtp.mailgun.org'],
                ['Port', '587'],
                ['Encryption', 'TLS'],
                ['Username', config('mail.mailers.mailgun.username') ?: 'Not set'],
                ['Password', config('mail.mailers.mailgun.password') ? '***' : 'Not set'],
            ]
        );

        $this->line('');
        $this->warn('⚠️  For Mailgun, you need to:');
        $this->line('   1. Sign up at: https://mailgun.com');
        $this->line('   2. Get SMTP credentials from Domain Settings');
        $this->line('   3. Set MAILGUN_USERNAME and MAILGUN_PASSWORD in .env');
    }

    /**
     * Show SMTP configuration
     */
    private function showSmtpConfig(): void
    {
        $this->line('');
        $this->info('📮 SMTP Configuration:');
        
        $this->table(
            ['Setting', 'Value'],
            [
                ['Host', config('mail.mailers.smtp.host') ?: 'Not set'],
                ['Port', config('mail.mailers.smtp.port') ?: 'Not set'],
                ['Encryption', config('mail.mailers.smtp.encryption') ?: 'Not set'],
                ['Username', config('mail.mailers.smtp.username') ?: 'Not set'],
                ['Password', config('mail.mailers.smtp.password') ? '***' : 'Not set'],
            ]
        );
    }

    /**
     * Test email configuration
     */
    private function testConfiguration(): bool
    {
        $this->info('🔍 Testing email configuration...');

        $driver = config('mail.default');
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        // Check basic configuration
        if (!$fromAddress || !$fromName) {
            $this->error('❌ From address or name not configured');
            return false;
        }

        // Check driver-specific configuration
        $mailerConfig = config("mail.mailers.{$driver}");
        if (!$mailerConfig) {
            $this->error("❌ Mailer '{$driver}' not configured");
            return false;
        }

        // Check credentials
        if (!$mailerConfig['username'] || !$mailerConfig['password']) {
            $this->error('❌ SMTP username or password not configured');
            $this->line('');
            $this->warn('💡 Add these to your .env file:');
            $this->line('   MAIL_USERNAME=your-email@example.com');
            $this->line('   MAIL_PASSWORD=your-password');
            return false;
        }

        $this->info('✅ Configuration looks good!');
        $this->line('');
        return true;
    }

    /**
     * Send test email
     */
    private function sendTestEmail(string $email): void
    {
        $this->info("📤 Sending test email to: {$email}");

        try {
            Mail::raw('This is a test email from Advanced Coupon System! 🎉', function ($message) use ($email) {
                $message->to($email)
                        ->subject('🧪 Test Email - ' . now()->format('Y-m-d H:i:s'));
            });

            $this->info('✅ Test email sent successfully!');
            $this->line('');
            $this->warn('📧 Check your inbox (and spam folder)');
            $this->line('   If you don\'t receive it, check:');
            $this->line('   - Email configuration in .env');
            $this->line('   - Mail logs in storage/logs/');
            $this->line('   - SMTP server settings');

        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());
            $this->line('');
            $this->warn('💡 Common solutions:');
            $this->line('   1. Check SMTP credentials');
            $this->line('   2. For Gmail: Use App Password');
            $this->line('   3. Check firewall/network settings');
            $this->line('   4. Try different mail service');
        }
    }
}