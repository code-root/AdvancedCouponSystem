<?php

namespace App\Events;

use App\Models\Subscription;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionCancelled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $subscription;
    public $reason;

    /**
     * Create a new event instance.
     */
    public function __construct(Subscription $subscription, string $reason = null)
    {
        $this->subscription = $subscription;
        $this->reason = $reason;
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
            'type' => 'subscription_cancelled',
            'subscription' => [
                'id' => $this->subscription->id,
                'user_id' => $this->subscription->user_id,
                'user_name' => $this->subscription->user->name,
                'plan_name' => $this->subscription->plan->name,
                'plan_price' => $this->subscription->plan->price,
                'status' => $this->subscription->status,
                'cancelled_at' => now(),
                'reason' => $this->reason,
            ],
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'subscription.cancelled';
    }
}