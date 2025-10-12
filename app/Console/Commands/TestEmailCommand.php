<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email : The email address to send test email to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to verify SMTP configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('Sending test email to: ' . $email);
        $this->info('Using mailer: ' . config('mail.default'));
        $this->info('SMTP Host: ' . config('mail.mailers.smtp.host'));
        $this->info('SMTP Port: ' . config('mail.mailers.smtp.port'));
        $this->info('From: ' . config('mail.from.address'));
        
        $this->newLine();
        
        try {
            Mail::raw('This is a test email from AdvancedCouponSystem. If you receive this, your email configuration is working correctly!', function ($message) use ($email) {
                $message->to($email)
                    ->subject('Test Email - AdvancedCouponSystem')
                    ->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            $this->info('✓ Test email sent successfully!');
            $this->info('Check your inbox at: ' . $email);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('✗ Failed to send test email!');
            $this->error('Error: ' . $e->getMessage());
            
            $this->newLine();
            $this->warn('Troubleshooting tips:');
            $this->line('1. Check your .env file for correct SMTP settings');
            $this->line('2. Verify MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD');
            $this->line('3. Make sure MAIL_ENCRYPTION is set correctly (tls or ssl)');
            $this->line('4. Check if your email provider allows SMTP access');
            $this->line('5. Verify FROM email is authorized to send from this domain');
            
            return Command::FAILURE;
        }
    }
}
