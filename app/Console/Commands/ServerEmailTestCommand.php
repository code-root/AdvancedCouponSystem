<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use App\Models\User;

class ServerEmailTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:server-test 
                            {email? : Email address to send test to}
                            {--fix : Try to fix common server issues}
                            {--sendgrid : Switch to SendGrid}
                            {--mailgun : Switch to Mailgun}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration on server and fix common issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🖥️  Server Email Test & Fix Tool');
        $this->line('');

        // Check if we're on server
        if (!$this->isServerEnvironment()) {
            $this->warn('⚠️  This command is designed for server environments');
            if (!$this->confirm('Continue anyway?')) {
                return 0;
            }
        }

        // Auto-fix if requested
        if ($this->option('fix')) {
            $this->autoFixCommonIssues();
        }

        // Switch to SendGrid if requested
        if ($this->option('sendgrid')) {
            $this->switchToSendGrid();
        }

        // Switch to Mailgun if requested
        if ($this->option('mailgun')) {
            $this->switchToMailgun();
        }

        // Test current configuration
        $this->testCurrentConfiguration();

        // Test email sending
        $email = $this->argument('email') ?? $this->ask('Enter email address to test');
        if ($email) {
            $this->testEmailSending($email);
        }

        // Test verification email
        $this->testVerificationEmail();

        return 0;
    }

    /**
     * Check if we're in server environment
     */
    private function isServerEnvironment(): bool
    {
        // Check for common server indicators
        $indicators = [
            'production' => config('app.env') === 'production',
            'server_path' => str_contains(__DIR__, '/var/www/') || str_contains(__DIR__, '/home/'),
            'no_gui' => !isset($_SERVER['DISPLAY']),
        ];

        return in_array(true, $indicators);
    }

    /**
     * Auto-fix common server issues
     */
    private function autoFixCommonIssues(): void
    {
        $this->info('🔧 Auto-fixing common server issues...');

        // Clear all caches
        $this->call('config:clear');
        $this->call('cache:clear');
        $this->call('view:clear');
        $this->call('route:clear');

        // Re-cache config
        $this->call('config:cache');

        $this->info('✅ Caches cleared and rebuilt');

        // Check and fix .env issues
        $this->fixEnvIssues();

        // Check PHP extensions
        $this->checkPhpExtensions();
    }

    /**
     * Fix common .env issues
     */
    private function fixEnvIssues(): void
    {
        $envPath = base_path('.env');
        
        if (!file_exists($envPath)) {
            $this->error('❌ .env file not found!');
            $this->warn('💡 Run: php artisan email:setup --service=sendgrid');
            return;
        }

        $envContent = file_get_contents($envPath);
        $issues = [];

        // Check for common issues
        if (str_contains($envContent, 'MAIL_PASSWORD=""')) {
            $issues[] = 'MAIL_PASSWORD is empty';
        }

        if (str_contains($envContent, 'MAIL_USERNAME=""')) {
            $issues[] = 'MAIL_USERNAME is empty';
        }

        if (str_contains($envContent, 'MAIL_MAILER=smtp') && !str_contains($envContent, 'MAIL_ENCRYPTION')) {
            $issues[] = 'MAIL_ENCRYPTION not set for SMTP';
        }

        if (empty($issues)) {
            $this->info('✅ .env file looks good');
        } else {
            $this->warn('⚠️  Found .env issues:');
            foreach ($issues as $issue) {
                $this->line("   - {$issue}");
            }
            $this->warn('💡 Consider running: php artisan email:setup --service=sendgrid');
        }
    }

    /**
     * Check PHP extensions
     */
    private function checkPhpExtensions(): void
    {
        $required = ['openssl', 'curl', 'mbstring'];
        $missing = [];

        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }

        if (empty($missing)) {
            $this->info('✅ Required PHP extensions are loaded');
        } else {
            $this->error('❌ Missing PHP extensions: ' . implode(', ', $missing));
            $this->warn('💡 Install missing extensions on your server');
        }
    }

    /**
     * Switch to SendGrid
     */
    private function switchToSendGrid(): void
    {
        $this->info('🔄 Switching to SendGrid...');

        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        // Update mail configuration
        $replacements = [
            'MAIL_MAILER=smtp' => 'MAIL_MAILER=sendgrid',
            'MAIL_HOST=smtp.gmail.com' => 'MAIL_HOST=smtp.sendgrid.net',
            'MAIL_USERNAME=info@trakifi.com' => 'MAIL_USERNAME=apikey',
            'MAIL_PASSWORD="gpag evdp tazg gjjr"' => 'MAIL_PASSWORD=SG.your-sendgrid-api-key-here',
        ];

        foreach ($replacements as $search => $replace) {
            $envContent = str_replace($search, $replace, $envContent);
        }

        // Add SendGrid API key if not exists
        if (!str_contains($envContent, 'SENDGRID_API_KEY')) {
            $envContent .= "\nSENDGRID_API_KEY=SG.your-sendgrid-api-key-here\n";
        }

        file_put_contents($envPath, $envContent);

        $this->info('✅ Switched to SendGrid configuration');
        $this->warn('⚠️  Don\'t forget to:');
        $this->line('   1. Get SendGrid API key from https://sendgrid.com');
        $this->line('   2. Update SENDGRID_API_KEY in .env');
        $this->line('   3. Run: php artisan config:clear');
    }

    /**
     * Switch to Mailgun
     */
    private function switchToMailgun(): void
    {
        $this->info('🔄 Switching to Mailgun...');

        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        // Update mail configuration
        $replacements = [
            'MAIL_MAILER=smtp' => 'MAIL_MAILER=mailgun',
            'MAIL_HOST=smtp.gmail.com' => 'MAIL_HOST=smtp.mailgun.org',
            'MAIL_USERNAME=info@trakifi.com' => 'MAIL_USERNAME=postmaster@yourdomain.mailgun.org',
            'MAIL_PASSWORD="gpag evdp tazg gjjr"' => 'MAIL_PASSWORD=your-mailgun-password',
        ];

        foreach ($replacements as $search => $replace) {
            $envContent = str_replace($search, $replace, $envContent);
        }

        // Add Mailgun credentials if not exists
        if (!str_contains($envContent, 'MAILGUN_USERNAME')) {
            $envContent .= "\nMAILGUN_USERNAME=postmaster@yourdomain.mailgun.org\n";
        }
        if (!str_contains($envContent, 'MAILGUN_PASSWORD')) {
            $envContent .= "\nMAILGUN_PASSWORD=your-mailgun-password\n";
        }

        file_put_contents($envPath, $envContent);

        $this->info('✅ Switched to Mailgun configuration');
        $this->warn('⚠️  Don\'t forget to:');
        $this->line('   1. Get Mailgun credentials from https://mailgun.com');
        $this->line('   2. Update MAILGUN_USERNAME and MAILGUN_PASSWORD in .env');
        $this->line('   3. Run: php artisan config:clear');
    }

    /**
     * Test current configuration
     */
    private function testCurrentConfiguration(): void
    {
        $this->info('🔍 Testing current email configuration...');

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

        // Test mailer configuration
        $mailerConfig = config("mail.mailers.{$driver}");
        if (!$mailerConfig) {
            $this->error("❌ Mailer '{$driver}' not configured");
            return;
        }

        $this->table(
            ['SMTP Setting', 'Value'],
            [
                ['Host', $mailerConfig['host'] ?? 'Not set'],
                ['Port', $mailerConfig['port'] ?? 'Not set'],
                ['Encryption', $mailerConfig['encryption'] ?? 'Not set'],
                ['Username', $mailerConfig['username'] ?? 'Not set'],
                ['Password', $mailerConfig['password'] ? '***' : 'Not set'],
            ]
        );

        // Check for common issues
        if (!$mailerConfig['username'] || !$mailerConfig['password']) {
            $this->error('❌ SMTP credentials not configured');
            $this->warn('💡 Run: php artisan email:server-test --sendgrid');
        } else {
            $this->info('✅ Configuration looks good');
        }
    }

    /**
     * Test email sending
     */
    private function testEmailSending(string $email): void
    {
        $this->info("📤 Testing email sending to: {$email}");

        try {
            Mail::raw('Server email test from Advanced Coupon System! 🖥️', function ($message) use ($email) {
                $message->to($email)
                        ->subject('🖥️ Server Email Test - ' . now()->format('Y-m-d H:i:s'));
            });

            $this->info('✅ Test email sent successfully!');
            $this->line('');
            $this->warn('📧 Check your inbox (and spam folder)');

        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());
            $this->line('');
            $this->warn('💡 Try these solutions:');
            $this->line('   1. Run: php artisan email:server-test --sendgrid');
            $this->line('   2. Or: php artisan email:server-test --mailgun');
            $this->line('   3. Check server logs: tail -f storage/logs/laravel.log');
        }
    }

    /**
     * Test verification email
     */
    private function testVerificationEmail(): void
    {
        $this->info('🔐 Testing email verification...');

        try {
            $user = User::first();
            if (!$user) {
                $this->warn('⚠️  No users found to test verification email');
                return;
            }

            $user->sendEmailVerificationNotification();
            $this->info("✅ Verification email sent to: {$user->email}");

        } catch (\Exception $e) {
            $this->error('❌ Failed to send verification email: ' . $e->getMessage());
            $this->line('');
            $this->warn('💡 This is likely the same issue you\'re experiencing');
            $this->warn('💡 Try: php artisan email:server-test --sendgrid');
        }
    }
}


