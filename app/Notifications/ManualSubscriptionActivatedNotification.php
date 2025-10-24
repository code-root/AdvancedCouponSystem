<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ManualSubscriptionActivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subscription;
    protected $activatedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Subscription $subscription, string $activatedBy = 'Admin')
    {
        $this->subscription = $subscription;
        $this->activatedBy = $activatedBy;
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
            ->subject('Subscription Manually Activated')
            ->greeting('Hello Admin!')
            ->line('A subscription has been manually activated.')
            ->line('User: ' . $this->subscription->user->name)
            ->line('Plan: ' . $this->subscription->plan->name)
            ->line('Activated By: ' . $this->activatedBy)
            ->line('Activation Date: ' . now()->format('M d, Y H:i:s'))
            ->line('Note: This subscription was activated without payment gateway.')
            ->action('View Subscription', route('admin.subscriptions.show', $this->subscription->id))
            ->line('Thank you for using our application!');
        return 0;
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'manual_subscription_activated',
            'title' => 'Subscription Manually Activated',
            'message' => 'User ' . $this->subscription->user->name . '\'s ' . $this->subscription->plan->name . ' subscription was manually activated by ' . $this->activatedBy,
            'data' => [
                'subscription_id' => $this->subscription->id,
                'user_id' => $this->subscription->user_id,
                'user_name' => $this->subscription->user->name,
                'plan_name' => $this->subscription->plan->name,
                'activated_by' => $this->activatedBy,
                'activation_date' => now(),
                'is_manual' => true,
            ],
            'icon' => 'ti ti-hand-click',
            'color' => 'primary',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'manual_subscription_activated',
            'title' => 'Subscription Manually Activated',
            'message' => 'User ' . $this->subscription->user->name . '\'s ' . $this->subscription->plan->name . ' subscription was manually activated by ' . $this->activatedBy,
            'data' => [
                'subscription_id' => $this->subscription->id,
                'user_id' => $this->subscription->user_id,
                'user_name' => $this->subscription->user->name,
                'plan_name' => $this->subscription->plan->name,
                'activated_by' => $this->activatedBy,
                'activation_date' => now(),
                'is_manual' => true,
            ],
            'icon' => 'ti ti-hand-click',
            'color' => 'primary',
        ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'manual_subscription_activated',
            'title' => 'Subscription Manually Activated',
            'message' => 'User ' . $this->subscription->user->name . '\'s ' . $this->subscription->plan->name . ' subscription was manually activated by ' . $this->activatedBy,
            'data' => [
                'subscription_id' => $this->subscription->id,
                'user_id' => $this->subscription->user_id,
                'user_name' => $this->subscription->user->name,
                'plan_name' => $this->subscription->plan->name,
                'activated_by' => $this->activatedBy,
                'activation_date' => now(),
                'is_manual' => true,
            ],
            'icon' => 'ti ti-hand-click',
            'color' => 'primary',
        ];
    }
}