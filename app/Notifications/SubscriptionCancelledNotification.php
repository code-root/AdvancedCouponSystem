<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class SubscriptionCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subscription;
    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Subscription $subscription, string $reason = null)
    {
        $this->subscription = $subscription;
        $this->reason = $reason;
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
        $message = (new MailMessage)
            ->subject('Subscription Cancelled')
            ->greeting('Hello Admin!')
            ->line('A subscription has been cancelled.')
            ->line('User: ' . $this->subscription->user->name)
            ->line('Plan: ' . $this->subscription->plan->name)
            ->line('Cancelled At: ' . now()->format('M d, Y H:i:s'));

        if ($this->reason) {
            $message->line('Reason: ' . $this->reason);
        }

        $message->action('View Subscription', route('admin.subscriptions.show', $this->subscription->id))
            ->line('Thank you for using our application!');

        return $message;
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'subscription_cancelled',
            'title' => 'Subscription Cancelled',
            'message' => 'User ' . $this->subscription->user->name . ' cancelled their ' . $this->subscription->plan->name . ' subscription',
            'data' => [
                'subscription_id' => $this->subscription->id,
                'user_id' => $this->subscription->user_id,
                'user_name' => $this->subscription->user->name,
                'plan_name' => $this->subscription->plan->name,
                'reason' => $this->reason,
                'cancelled_at' => now(),
            ],
            'icon' => 'ti ti-user-x',
            'color' => 'warning',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'subscription_cancelled',
            'title' => 'Subscription Cancelled',
            'message' => 'User ' . $this->subscription->user->name . ' cancelled their ' . $this->subscription->plan->name . ' subscription',
            'data' => [
                'subscription_id' => $this->subscription->id,
                'user_id' => $this->subscription->user_id,
                'user_name' => $this->subscription->user->name,
                'plan_name' => $this->subscription->plan->name,
                'reason' => $this->reason,
                'cancelled_at' => now(),
            ],
            'icon' => 'ti ti-user-x',
            'color' => 'warning',
        ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'subscription_cancelled',
            'title' => 'Subscription Cancelled',
            'message' => 'User ' . $this->subscription->user->name . ' cancelled their ' . $this->subscription->plan->name . ' subscription',
            'data' => [
                'subscription_id' => $this->subscription->id,
                'user_id' => $this->subscription->user_id,
                'user_name' => $this->subscription->user->name,
                'plan_name' => $this->subscription->plan->name,
                'reason' => $this->reason,
                'cancelled_at' => now(),
            ],
            'icon' => 'ti ti-user-x',
            'color' => 'warning',
        ];
    }
}