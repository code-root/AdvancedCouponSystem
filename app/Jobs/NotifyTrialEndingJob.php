<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class NotifyTrialEndingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting trial ending notification job');

        try {
            // Notify users whose trial ends in 3 days
            $this->notifyTrialEndingIn3Days();

            // Notify users whose trial ends in 1 day
            $this->notifyTrialEndingIn1Day();

            // Notify users whose trial has ended
            $this->notifyTrialEnded();

            Log::info('Trial ending notification job completed successfully');
        } catch (\Exception $e) {
            Log::error('Trial ending notification job failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Notify users whose trial ends in 3 days
     */
    private function notifyTrialEndingIn3Days(): void
    {
        $threeDaysFromNow = Carbon::now()->addDays(3)->endOfDay();

        $subscriptions = Subscription::where('status', 'trial')
            ->where('ends_at', '<=', $threeDaysFromNow)
            ->where('ends_at', '>', Carbon::now()->addDays(2)->startOfDay())
            ->with('user', 'plan')
            ->get();

        foreach ($subscriptions as $subscription) {
            $this->sendTrialEndingNotification($subscription, 3);
        }

        Log::info("Sent 3-day trial ending notifications to {$subscriptions->count()} users");
    }

    /**
     * Notify users whose trial ends in 1 day
     */
    private function notifyTrialEndingIn1Day(): void
    {
        $oneDayFromNow = Carbon::now()->addDay()->endOfDay();

        $subscriptions = Subscription::where('status', 'trial')
            ->where('ends_at', '<=', $oneDayFromNow)
            ->where('ends_at', '>', Carbon::now()->endOfDay())
            ->with('user', 'plan')
            ->get();

        foreach ($subscriptions as $subscription) {
            $this->sendTrialEndingNotification($subscription, 1);
        }

        Log::info("Sent 1-day trial ending notifications to {$subscriptions->count()} users");
    }

    /**
     * Notify users whose trial has ended
     */
    private function notifyTrialEnded(): void
    {
        $subscriptions = Subscription::where('status', 'trial')
            ->where('ends_at', '<=', Carbon::now())
            ->with('user', 'plan')
            ->get();

        foreach ($subscriptions as $subscription) {
            $this->sendTrialEndedNotification($subscription);
            
            // Update subscription status to expired
            $subscription->update(['status' => 'expired']);
        }

        Log::info("Sent trial ended notifications to {$subscriptions->count()} users");
    }

    /**
     * Send trial ending notification
     */
    private function sendTrialEndingNotification(Subscription $subscription, int $daysLeft): void
    {
        $user = $subscription->user;
        $plan = $subscription->plan;

        try {
            // Send email notification
            Mail::send('emails.trial-ending', [
                'user' => $user,
                'plan' => $plan,
                'subscription' => $subscription,
                'daysLeft' => $daysLeft,
                'trialEndDate' => $subscription->ends_at->format('M d, Y'),
            ], function ($message) use ($user, $daysLeft) {
                $message->to($user->email, $user->name)
                    ->subject("Your trial ends in {$daysLeft} day" . ($daysLeft > 1 ? 's' : ''));
            });

            // Create notification record
            $user->notify(new \App\Notifications\TrialEndingNotification($subscription, $daysLeft));

            Log::info("Sent trial ending notification to user {$user->id} ({$user->email}) - {$daysLeft} days left");
        } catch (\Exception $e) {
            Log::error("Failed to send trial ending notification to user {$user->id}: " . $e->getMessage());
        }
    }

    /**
     * Send trial ended notification
     */
    private function sendTrialEndedNotification(Subscription $subscription): void
    {
        $user = $subscription->user;
        $plan = $subscription->plan;

        try {
            // Send email notification
            Mail::send('emails.trial-ended', [
                'user' => $user,
                'plan' => $plan,
                'subscription' => $subscription,
                'trialEndDate' => $subscription->ends_at->format('M d, Y'),
            ], function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('Your trial has ended');
            });

            // Create notification record
            $user->notify(new \App\Notifications\TrialEndedNotification($subscription));

            Log::info("Sent trial ended notification to user {$user->id} ({$user->email})");
        } catch (\Exception $e) {
            Log::error("Failed to send trial ended notification to user {$user->id}: " . $e->getMessage());
        }
    }
}