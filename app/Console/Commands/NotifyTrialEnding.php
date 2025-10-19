<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewLoginNotification; // placeholder notification
use Carbon\Carbon;

class NotifyTrialEnding extends Command
{
    protected $signature = 'subs:notify-trial-ending {days=2}';
    protected $description = 'Notify users whose trials are ending soon';

    public function handle(): int
    {
        $days = (int) $this->argument('days');
        $threshold = Carbon::now()->addDays($days);

        $subs = Subscription::where('status', 'trialing')
            ->whereNotNull('trial_ends_at')
            ->whereBetween('trial_ends_at', [Carbon::now(), $threshold])
            ->with('user')
            ->get();

        $count = 0;
        foreach ($subs as $sub) {
            if ($sub->user) {
                // Use existing notification as placeholder; you can create a dedicated one
                Notification::send($sub->user, new NewLoginNotification([ 'message' => 'Your trial ends soon' ]));
                $count++;
            }
        }

        $this->info("Notified {$count} users");
        return self::SUCCESS;
    }
}


