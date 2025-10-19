<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiringSoonNotification extends Notification implements ShouldQueue
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
     */
    public function via($notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Subscription Expiring Soon')
            ->greeting('Hello Admin!')
            ->line('A subscription is expiring soon.')
            ->line('User: ' . $this->subscription->user->name)
            ->line('Plan: ' . $this->subscription->plan->name)
            ->line('Expires: ' . $this->subscription->ends_at->format('M d, Y'))
            ->line('Days Left: ' . $this->daysLeft)
            ->action('View Subscription', route('admin.subscriptions.show', $this->subscription->id))
            ->line('Consider reaching out to the user for renewal.')
            ->line('Thank you for using our application!');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'subscription_expiring_soon',
            'title' => 'Subscription Expiring Soon',
            'message' => 'User ' . $this->subscription->user->name . '\'s ' . $this->subscription->plan->name . ' subscription expires in ' . $this->daysLeft . ' days',
            'data' => [
                'subscription_id' => $this->subscription->id,
                'user_id' => $this->subscription->user_id,
                'user_name' => $this->subscription->user->name,
                'plan_name' => $this->subscription->plan->name,
                'expires_at' => $this->subscription->ends_at,
                'days_left' => $this->daysLeft,
            ],
            'icon' => 'ti ti-clock',
            'color' => 'warning',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'subscription_expiring_soon',
            'title' => 'Subscription Expiring Soon',
            'message' => 'User ' . $this->subscription->user->name . '\'s ' . $this->subscription->plan->name . ' subscription expires in ' . $this->daysLeft . ' days',
            'data' => [
                'subscription_id' => $this->subscription->id,
                'user_id' => $this->subscription->user_id,
                'user_name' => $this->subscription->user->name,
                'plan_name' => $this->subscription->plan->name,
                'expires_at' => $this->subscription->ends_at,
                'days_left' => $this->daysLeft,
            ],
            'icon' => 'ti ti-clock',
            'color' => 'warning',
        ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'subscription_expiring_soon',
            'title' => 'Subscription Expiring Soon',
            'message' => 'User ' . $this->subscription->user->name . '\'s ' . $this->subscription->plan->name . ' subscription expires in ' . $this->daysLeft . ' days',
            'data' => [
                'subscription_id' => $this->subscription->id,
                'user_id' => $this->subscription->user_id,
                'user_name' => $this->subscription->user->name,
                'plan_name' => $this->subscription->plan->name,
                'expires_at' => $this->subscription->ends_at,
                'days_left' => $this->daysLeft,
            ],
            'icon' => 'ti ti-clock',
            'color' => 'warning',
        ];
    }
}