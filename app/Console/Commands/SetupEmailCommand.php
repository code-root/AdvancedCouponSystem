<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interactive SMTP email configuration setup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('===========================================');
        $this->info('   Email Configuration Setup Wizard');
        $this->info('===========================================');
        $this->newLine();
        
        $this->warn('This wizard will help you configure your email settings.');
        $this->warn('You will need your SMTP credentials from your email provider.');
        $this->newLine();
        
        // Get current .env path
        $envPath = base_path('.env');
        
        if (!file_exists($envPath)) {
            $this->error('.env file not found!');
            $this->info('Please copy .env.example to .env first.');
            return Command::FAILURE;
        }
        
        // Ask for configuration
        $mailer = $this->choice('Mail Driver', ['smtp', 'log', 'sendmail'], 0);
        
        if ($mailer === 'smtp') {
            $host = $this->ask('SMTP Host (e.g., mail.yourdomain.com)', 'smtp.gmail.com');
            $port = $this->ask('SMTP Port', '587');
            $encryption = $this->choice('Encryption', ['tls', 'ssl'], 0);
            $username = $this->ask('SMTP Username (email address)');
            $password = $this->secret('SMTP Password');
            $fromAddress = $this->ask('From Email Address', $username);
            $fromName = $this->ask('From Name', config('app.name'));
            
            // Update .env file
            $this->updateEnv([
                'MAIL_MAILER' => $mailer,
                'MAIL_HOST' => $host,
                'MAIL_PORT' => $port,
                'MAIL_USERNAME' => $username,
                'MAIL_PASSWORD' => $password,
                'MAIL_ENCRYPTION' => $encryption,
                'MAIL_FROM_ADDRESS' => $fromAddress,
                'MAIL_FROM_NAME' => '"' . $fromName . '"',
            ]);
            
            $this->newLine();
            $this->info('✓ Email configuration updated successfully!');
            $this->newLine();
            
            // Ask if user wants to test
            if ($this->confirm('Would you like to send a test email?', true)) {
                $testEmail = $this->ask('Enter test email address', $fromAddress);
                $this->call('mail:test', ['email' => $testEmail]);
            }
            
        } else {
            $this->updateEnv(['MAIL_MAILER' => $mailer]);
            $this->info('✓ Mail driver set to: ' . $mailer);
        }
        
        $this->newLine();
        $this->info('Configuration complete!');
        
        return Command::SUCCESS;
    }
    
    /**
     * Update .env file with new values
     */
    protected function updateEnv(array $data)
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);
        
        foreach ($data as $key => $value) {
            // Check if key exists
            if (preg_match("/^{$key}=.*/m", $envContent)) {
                // Update existing key
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $envContent
                );
            } else {
                // Add new key
                $envContent .= "\n{$key}={$value}";
            }
        }
        
        file_put_contents($envPath, $envContent);
    }
}
