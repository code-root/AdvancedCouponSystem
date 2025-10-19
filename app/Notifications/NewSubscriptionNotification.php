<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewSubscriptionNotification extends Notification implements ShouldQueue
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
            ->subject('New Subscription Created')
            ->greeting('Hello Admin!')
            ->line('A new subscription has been created.')
            ->line('User: ' . $this->subscription->user->name)
            ->line('Plan: ' . $this->subscription->plan->name)
            ->line('Amount: $' . number_format($this->subscription->plan->price, 2))
            ->line('Status: ' . ucfirst($this->subscription->status))
            ->action('View Subscription', route('admin.subscriptions.show', $this->subscription->id))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'new_subscription',
            'title' => 'New Subscription Created',
            'message' => 'User ' . $this->subscription->user->name . ' subscribed to ' . $this->subscription->plan->name,
            'data' => [
                'subscription_id' => $this->subscription->id,
                'user_id' => $this->subscription->user_id,
                'user_name' => $this->subscription->user->name,
                'plan_name' => $this->subscription->plan->name,
                'amount' => $this->subscription->plan->price,
                'status' => $this->subscription->status,
                'created_at' => $this->subscription->created_at,
            ],
            'icon' => 'ti ti-user-plus',
            'color' => 'success',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'new_subscription',
            'title' => 'New Subscription Created',
            'message' => 'User ' . $this->subscription->user->name . ' subscribed to ' . $this->subscription->plan->name,
            'data' => [
                'subscription_id' => $this->subscription->id,
                'user_id' => $this->subscription->user_id,
                'user_name' => $this->subscription->user->name,
                'plan_name' => $this->subscription->plan->name,
                'amount' => $this->subscription->plan->price,
                'status' => $this->subscription->status,
                'created_at' => $this->subscription->created_at,
            ],
            'icon' => 'ti ti-user-plus',
            'color' => 'success',
        ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'new_subscription',
            'title' => 'New Subscription Created',
            'message' => 'User ' . $this->subscription->user->name . ' subscribed to ' . $this->subscription->plan->name,
            'data' => [
                'subscription_id' => $this->subscription->id,
                'user_id' => $this->subscription->user_id,
                'user_name' => $this->subscription->user->name,
                'plan_name' => $this->subscription->plan->name,
                'amount' => $this->subscription->plan->price,
                'status' => $this->subscription->status,
                'created_at' => $this->subscription->created_at,
            ],
            'icon' => 'ti ti-user-plus',
            'color' => 'success',
        ];
    }
}