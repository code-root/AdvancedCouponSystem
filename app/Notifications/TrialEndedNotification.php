<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialEndedNotification extends Notification
{
    use Queueable;

    protected $subscription;

    /**
     * Create a new notification instance.
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $plan = $this->subscription->plan;
        $trialEndDate = $this->subscription->ends_at->format('M d, Y');

        return (new MailMessage)
            ->subject('Your trial has ended')
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your free trial for the {$plan->name} plan has ended on {$trialEndDate}.")
            ->line("To continue using our service and access all features, please subscribe to a plan.")
            ->action('Subscribe Now', route('subscriptions.plans'))
            ->line('We hope you enjoyed your trial and look forward to serving you!')
            ->line('If you have any questions, please contact our support team.')
            ->salutation('Best regards, The Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $plan = $this->subscription->plan;
        $trialEndDate = $this->subscription->ends_at->format('M d, Y');

        return [
            'type' => 'trial_ended',
            'title' => 'Trial Ended',
            'message' => "Your free trial for the {$plan->name} plan has ended on {$trialEndDate}. Subscribe now to continue using our service.",
            'subscription_id' => $this->subscription->id,
            'plan_id' => $plan->id,
            'trial_end_date' => $trialEndDate,
            'action_url' => route('subscriptions.plans'),
            'action_text' => 'Subscribe Now',
        ];
    }
}