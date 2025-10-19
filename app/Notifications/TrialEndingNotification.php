<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialEndingNotification extends Notification
{
    use Queueable;

    protected $subscription;
    protected $daysLeft;

    /**
     * Create a new notification instance.
     */
    public function __construct(Subscription $subscription, int $daysLeft)
    {
        $this->subscription = $subscription;
        $this->daysLeft = $daysLeft;
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
            ->subject("Your trial ends in {$this->daysLeft} day" . ($this->daysLeft > 1 ? 's' : ''))
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your free trial for the {$plan->name} plan will end in {$this->daysLeft} day" . ($this->daysLeft > 1 ? 's' : '') . " on {$trialEndDate}.")
            ->line("To continue using our service, please subscribe to a plan before your trial expires.")
            ->action('Subscribe Now', route('subscriptions.plans'))
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
            'type' => 'trial_ending',
            'title' => 'Trial Ending Soon',
            'message' => "Your free trial for the {$plan->name} plan ends in {$this->daysLeft} day" . ($this->daysLeft > 1 ? 's' : '') . " on {$trialEndDate}.",
            'subscription_id' => $this->subscription->id,
            'plan_id' => $plan->id,
            'days_left' => $this->daysLeft,
            'trial_end_date' => $trialEndDate,
            'action_url' => route('subscriptions.plans'),
            'action_text' => 'Subscribe Now',
        ];
    }
}