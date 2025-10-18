<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:setup 
                            {--service= : Email service (gmail, sendgrid, mailgun)}
                            {--force : Overwrite existing .env file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup email configuration and create .env file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Advanced Coupon System - Email Setup');
        $this->line('');

        // Check if .env exists
        $envPath = base_path('.env');
        if (File::exists($envPath) && !$this->option('force')) {
            if (!$this->confirm('⚠️  .env file already exists. Overwrite?')) {
                $this->info('Setup cancelled.');
                return 0;
            }
        }

        // Select email service
        $service = $this->option('service') ?? $this->choice(
            'Select email service',
            ['gmail', 'sendgrid', 'mailgun', 'custom'],
            'gmail'
        );

        // Get email configuration
        $config = $this->getEmailConfiguration($service);

        // Create .env file
        $this->createEnvFile($config);

        $this->info('✅ Email setup completed!');
        $this->line('');
        $this->warn('📝 Next steps:');
        $this->line('   1. Edit .env file with your actual credentials');
        $this->line('   2. Run: php artisan email:test');
        $this->line('   3. Test email sending');

        return 0;
    }

    /**
     * Get email configuration based on service
     */
    private function getEmailConfiguration(string $service): array
    {
        $baseConfig = [
            'APP_NAME' => '"Advanced Coupon System"',
            'APP_ENV' => 'local',
            'APP_KEY' => 'base64:your-app-key-here',
            'APP_DEBUG' => 'true',
            'APP_URL' => 'http://localhost',
            'LOG_CHANNEL' => 'stack',
            'LOG_LEVEL' => 'debug',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => '/Users/mo/Documents/project/AdvancedCouponSystem/database/database.sqlite',
            'BROADCAST_DRIVER' => 'log',
            'CACHE_DRIVER' => 'file',
            'FILESYSTEM_DISK' => 'local',
            'QUEUE_CONNECTION' => 'sync',
            'SESSION_DRIVER' => 'file',
            'SESSION_LIFETIME' => '120',
        ];

        switch ($service) {
            case 'gmail':
                return array_merge($baseConfig, [
                    'MAIL_MAILER' => 'gmail',
                    'MAIL_HOST' => 'smtp.gmail.com',
                    'MAIL_PORT' => '587',
                    'MAIL_USERNAME' => 'info@trakifi.com',
                    'MAIL_PASSWORD' => 'your-gmail-app-password-here',
                    'MAIL_ENCRYPTION' => 'tls',
                    'MAIL_FROM_ADDRESS' => 'info@trakifi.com',
                    'MAIL_FROM_NAME' => '"Trakifi"',
                    'MAIL_TIMEOUT' => '60',
                    'MAIL_VERIFY_PEER' => 'false',
                    'MAIL_ALLOW_SELF_SIGNED' => 'true',
                ]);

            case 'sendgrid':
                return array_merge($baseConfig, [
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
                ]);

            case 'mailgun':
                return array_merge($baseConfig, [
                    'MAIL_MAILER' => 'mailgun',
                    'MAIL_HOST' => 'smtp.mailgun.org',
                    'MAIL_PORT' => '587',
                    'MAIL_USERNAME' => 'postmaster@yourdomain.mailgun.org',
                    'MAIL_PASSWORD' => 'your-mailgun-password-here',
                    'MAIL_ENCRYPTION' => 'tls',
                    'MAIL_FROM_ADDRESS' => 'info@trakifi.com',
                    'MAIL_FROM_NAME' => '"Trakifi"',
                    'MAIL_TIMEOUT' => '60',
                    'MAIL_VERIFY_PEER' => 'false',
                    'MAIL_ALLOW_SELF_SIGNED' => 'true',
                    'MAILGUN_USERNAME' => 'postmaster@yourdomain.mailgun.org',
                    'MAILGUN_PASSWORD' => 'your-mailgun-password-here',
                ]);

            default:
                return array_merge($baseConfig, [
                    'MAIL_MAILER' => 'smtp',
                    'MAIL_HOST' => 'smtp.example.com',
                    'MAIL_PORT' => '587',
                    'MAIL_USERNAME' => 'your-email@example.com',
                    'MAIL_PASSWORD' => 'your-password',
                    'MAIL_ENCRYPTION' => 'tls',
                    'MAIL_FROM_ADDRESS' => 'info@trakifi.com',
                    'MAIL_FROM_NAME' => '"Trakifi"',
                    'MAIL_TIMEOUT' => '60',
                    'MAIL_VERIFY_PEER' => 'false',
                    'MAIL_ALLOW_SELF_SIGNED' => 'true',
                ]);
        }
    }

    /**
     * Create .env file
     */
    private function createEnvFile(array $config): void
    {
        $envPath = base_path('.env');
        
        $envContent = "# Advanced Coupon System Environment Configuration\n";
        $envContent .= "# Generated on " . now()->format('Y-m-d H:i:s') . "\n\n";

        // Add configuration
        foreach ($config as $key => $value) {
            $envContent .= "{$key}={$value}\n";
        }

        // Add additional configurations
        $envContent .= "\n# Additional configurations\n";
        $envContent .= "AWS_ACCESS_KEY_ID=\n";
        $envContent .= "AWS_SECRET_ACCESS_KEY=\n";
        $envContent .= "AWS_DEFAULT_REGION=us-east-1\n";
        $envContent .= "AWS_BUCKET=\n";
        $envContent .= "AWS_USE_PATH_STYLE_ENDPOINT=false\n\n";
        $envContent .= "PUSHER_APP_ID=\n";
        $envContent .= "PUSHER_APP_KEY=\n";
        $envContent .= "PUSHER_APP_SECRET=\n";
        $envContent .= "PUSHER_HOST=\n";
        $envContent .= "PUSHER_PORT=443\n";
        $envContent .= "PUSHER_SCHEME=https\n";
        $envContent .= "PUSHER_APP_CLUSTER=mt1\n\n";
        $envContent .= "VITE_PUSHER_APP_KEY=\"\${PUSHER_APP_KEY}\"\n";
        $envContent .= "VITE_PUSHER_HOST=\"\${PUSHER_HOST}\"\n";
        $envContent .= "VITE_PUSHER_PORT=\"\${PUSHER_PORT}\"\n";
        $envContent .= "VITE_PUSHER_SCHEME=\"\${PUSHER_SCHEME}\"\n";
        $envContent .= "VITE_PUSHER_APP_CLUSTER=\"\${PUSHER_APP_CLUSTER}\"\n";

        File::put($envPath, $envContent);

        $this->info("📝 Created .env file at: {$envPath}");
    }
}