<?php

namespace App\Notifications;

use App\Models\Subscription;
use App\Models\Plan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class SubscriptionUpgradedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subscription;
    protected $oldPlan;
    protected $newPlan;

    /**
     * Create a new notification instance.
     */
    public function __construct(Subscription $subscription, Plan $oldPlan, Plan $newPlan)
    {
        $this->subscription = $subscription;
        $this->oldPlan = $oldPlan;
        $this->newPlan = $newPlan;
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
            ->subject('Subscription Upgraded')
            ->greeting('Hello Admin!')
            ->line('A subscription has been upgraded.')
            ->line('User: ' . $this->subscription->user->name)
            ->line('From: ' . $this->oldPlan->name . ' ($' . number_format($this->oldPlan->price, 2) . ')')
            ->line('To: ' . $this->newPlan->name . ' ($' . number_format($this->newPlan->price, 2) . ')')
            ->line('Upgrade Date: ' . now()->format('M d, Y H:i:s'))
            ->action('View Subscription', route('admin.subscriptions.show', $this->subscription->id))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'subscription_upgraded',
            'title' => 'Subscription Upgraded',
            'message' => 'User ' . $this->subscription->user->name . ' upgraded from ' . $this->oldPlan->name . ' to ' . $this->newPlan->name,
            'data' => [
                'subscription_id' => $this->subscription->id,
                'user_id' => $this->subscription->user_id,
                'user_name' => $this->subscription->user->name,
                'old_plan_name' => $this->oldPlan->name,
                'old_plan_price' => $this->oldPlan->price,
                'new_plan_name' => $this->newPlan->name,
                'new_plan_price' => $this->newPlan->price,
                'upgrade_date' => now(),
            ],
            'icon' => 'ti ti-arrow-up',
            'color' => 'info',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'subscription_upgraded',
            'title' => 'Subscription Upgraded',
            'message' => 'User ' . $this->subscription->user->name . ' upgraded from ' . $this->oldPlan->name . ' to ' . $this->newPlan->name,
            'data' => [
                'subscription_id' => $this->subscription->id,
                'user_id' => $this->subscription->user_id,
                'user_name' => $this->subscription->user->name,
                'old_plan_name' => $this->oldPlan->name,
                'old_plan_price' => $this->oldPlan->price,
                'new_plan_name' => $this->newPlan->name,
                'new_plan_price' => $this->newPlan->price,
                'upgrade_date' => now(),
            ],
            'icon' => 'ti ti-arrow-up',
            'color' => 'info',
        ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'subscription_upgraded',
            'title' => 'Subscription Upgraded',
            'message' => 'User ' . $this->subscription->user->name . ' upgraded from ' . $this->oldPlan->name . ' to ' . $this->newPlan->name,
            'data' => [
                'subscription_id' => $this->subscription->id,
                'user_id' => $this->subscription->user_id,
                'user_name' => $this->subscription->user->name,
                'old_plan_name' => $this->oldPlan->name,
                'old_plan_price' => $this->oldPlan->price,
                'new_plan_name' => $this->newPlan->name,
                'new_plan_price' => $this->newPlan->price,
                'upgrade_date' => now(),
            ],
            'icon' => 'ti ti-arrow-up',
            'color' => 'info',
        ];
    }
}