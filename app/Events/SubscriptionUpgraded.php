<?php

namespace App\Events;

use App\Models\Subscription;
use App\Models\Plan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionUpgraded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $subscription;
    public $oldPlan;
    public $newPlan;

    /**
     * Create a new event instance.
     */
    public function __construct(Subscription $subscription, Plan $oldPlan, Plan $newPlan)
    {
        $this->subscription = $subscription;
        $this->oldPlan = $oldPlan;
        $this->newPlan = $newPlan;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('subscription-updates'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'type' => 'subscription_upgraded',
            'subscription' => [
                'id' => $this->subscription->id,
                'user_id' => $this->subscription->user_id,
                'user_name' => $this->subscription->user->name,
                'old_plan_name' => $this->oldPlan->name,
                'old_plan_price' => $this->oldPlan->price,
                'new_plan_name' => $this->newPlan->name,
                'new_plan_price' => $this->newPlan->price,
                'upgrade_date' => now(),
            ],
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'subscription.upgraded';
    }
}