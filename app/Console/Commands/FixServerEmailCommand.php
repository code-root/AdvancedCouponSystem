<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixServerEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:fix-server 
                            {--service=sendgrid : Email service to use (sendgrid, mailgun, gmail)}
                            {--force : Force overwrite existing configuration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quick fix for server email authentication issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Quick Server Email Fix');
        $this->line('');

        $service = $this->option('service');
        $force = $this->option('force');

        // Step 1: Clear all caches
        $this->clearCaches();

        // Step 2: Fix .env configuration
        $this->fixEnvConfiguration($service, $force);

        // Step 3: Test configuration
        $this->testConfiguration();

        $this->info('✅ Server email fix completed!');
        $this->line('');
        $this->warn('📝 Next steps:');
        $this->line('   1. Update credentials in .env file');
        $this->line('   2. Run: php artisan email:server-test');
        $this->line('   3. Test email sending');

        return 0;
    }

    /**
     * Clear all caches
     */
    private function clearCaches(): void
    {
        $this->info('🧹 Clearing caches...');

        $commands = [
            'config:clear',
            'cache:clear',
            'view:clear',
            'route:clear',
        ];

        foreach ($commands as $command) {
            $this->call($command);
        }

        $this->info('✅ Caches cleared');
    }

    /**
     * Fix .env configuration
     */
    private function fixEnvConfiguration(string $service, bool $force): void
    {
        $this->info("🔧 Fixing .env configuration for {$service}...");

        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            $this->error('❌ .env file not found!');
            $this->warn('💡 Creating new .env file...');
            $this->createNewEnvFile($service);
            return;
        }

        $envContent = File::get($envPath);
        $newConfig = $this->getServiceConfiguration($service);

        // Update or add mail configuration
        foreach ($newConfig as $key => $value) {
            $pattern = "/^{$key}=.*$/m";
            if (preg_match($pattern, $envContent)) {
                // Update existing
                $envContent = preg_replace($pattern, "{$key}={$value}", $envContent);
            } else {
                // Add new
                $envContent .= "\n{$key}={$value}";
            }
        }

        File::put($envPath, $envContent);
        $this->info('✅ .env configuration updated');
    }

    /**
     * Create new .env file
     */
    private function createNewEnvFile(string $service): void
    {
        $envPath = base_path('.env');
        $config = $this->getServiceConfiguration($service);

        $envContent = "# Advanced Coupon System - Server Configuration\n";
        $envContent .= "# Generated on " . now()->format('Y-m-d H:i:s') . "\n\n";

        // Add basic app configuration
        $envContent .= "APP_NAME=\"Advanced Coupon System\"\n";
        $envContent .= "APP_ENV=production\n";
        $envContent .= "APP_KEY=base64:your-app-key-here\n";
        $envContent .= "APP_DEBUG=false\n";
        $envContent .= "APP_URL=https://trakifi.com\n\n";

        // Add mail configuration
        foreach ($config as $key => $value) {
            $envContent .= "{$key}={$value}\n";
        }

        // Add additional configurations
        $envContent .= "\n# Additional configurations\n";
        $envContent .= "LOG_CHANNEL=stack\n";
        $envContent .= "LOG_LEVEL=error\n";
        $envContent .= "DB_CONNECTION=mysql\n";
        $envContent .= "DB_HOST=127.0.0.1\n";
        $envContent .= "DB_PORT=3306\n";
        $envContent .= "DB_DATABASE=your_database\n";
        $envContent .= "DB_USERNAME=your_username\n";
        $envContent .= "DB_PASSWORD=your_password\n\n";

        File::put($envPath, $envContent);
        $this->info('✅ New .env file created');
    }

    /**
     * Get service configuration
     */
    private function getServiceConfiguration(string $service): array
    {
        switch ($service) {
            case 'sendgrid':
                return [
                    'MAIL_MAILER' => 'sendgrid',
                    'MAIL_HOST' => 'smtp.sendgrid.net',
                    'MAIL_PORT' => '587',
                    'MAIL_USERNAME' => 'apikey',
                    'MAIL_PASSWORD' => 'SG.your-sendgrid-api-key-here',
                    'MAIL_ENCRYPTION' => 'tls',
                    'MAIL_FROM_ADDRESS' => 'info@trakifi.com',
                    'MAIL_FROM_NAME' => '"Trakifi"',
                    'MAIL_TIMEOUT' => '60',
                    'MAIL_VERIFY_PEER' => 'false',
                    'MAIL_ALLOW_SELF_SIGNED' => 'true',
                    'SENDGRID_API_KEY' => 'SG.your-sendgrid-api-key-here',
                ];

            case 'mailgun':
                return [
                    'MAIL_MAILER' => 'mailgun',
                    'MAIL_HOST' => 'smtp.mailgun.org',
                    'MAIL_PORT' => '587',
                    'MAIL_USERNAME' => 'postmaster@yourdomain.mailgun.org',
                    'MAIL_PASSWORD' => 'your-mailgun-password',
                    'MAIL_ENCRYPTION' => 'tls',
                    'MAIL_FROM_ADDRESS' => 'info@trakifi.com',
                    'MAIL_FROM_NAME' => '"Trakifi"',
                    'MAIL_TIMEOUT' => '60',
                    'MAIL_VERIFY_PEER' => 'false',
                    'MAIL_ALLOW_SELF_SIGNED' => 'true',
                    'MAILGUN_USERNAME' => 'postmaster@yourdomain.mailgun.org',
                    'MAILGUN_PASSWORD' => 'your-mailgun-password',
                ];

            case 'gmail':
            default:
                return [
                    'MAIL_MAILER' => 'gmail',
                    'MAIL_HOST' => 'smtp.gmail.com',
                    'MAIL_PORT' => '587',
                    'MAIL_USERNAME' => 'info@trakifi.com',
                    'MAIL_PASSWORD' => 'your-gmail-app-password',
                    'MAIL_ENCRYPTION' => 'tls',
                    'MAIL_FROM_ADDRESS' => 'info@trakifi.com',
                    'MAIL_FROM_NAME' => '"Trakifi"',
                    'MAIL_TIMEOUT' => '60',
                    'MAIL_VERIFY_PEER' => 'false',
                    'MAIL_ALLOW_SELF_SIGNED' => 'true',
                ];
        }
    }

    /**
     * Test configuration
     */
    private function testConfiguration(): void
    {
        $this->info('🧪 Testing configuration...');

        // Re-cache config
        $this->call('config:cache');

        // Test basic configuration
        $driver = config('mail.default');
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        if (!$driver || !$fromAddress || !$fromName) {
            $this->error('❌ Basic mail configuration missing');
            return;
        }

        $this->info('✅ Basic configuration looks good');
        $this->line('');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Driver', $driver],
                ['From Address', $fromAddress],
                ['From Name', $fromName],
            ]
        );

        // Check mailer configuration
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

        if (!$mailerConfig['username'] || !$mailerConfig['password']) {
            $this->warn('⚠️  SMTP credentials need to be updated');
            $this->line('');
            $this->warn('📝 Update these in your .env file:');
            $this->line('   - MAIL_USERNAME');
            $this->line('   - MAIL_PASSWORD');
            if ($driver === 'sendgrid') {
                $this->line('   - SENDGRID_API_KEY');
            }
        } else {
            $this->info('✅ SMTP configuration looks good');
        }
    }
}


