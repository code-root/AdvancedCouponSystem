<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\UserSession;

class NewSessionCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $session;

    /**
     * Create a new event instance.
     */
    public function __construct(UserSession $session)
    {
        $this->session = $session;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->session->user_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'session.created';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'session' => [
                'id' => $this->session->id,
                'device' => $this->session->device_info,
                'browser' => $this->session->browser_info,
                'location' => $this->session->location,
                'ip_address' => $this->session->ip_address,
                'country' => $this->session->country,
                'city' => $this->session->city,
                'login_at' => $this->session->login_at?->toIso8601String(),
                'device_icon' => $this->session->device_icon,
            ],
            'message' => "تم تسجيل الدخول من جهاز {$this->session->device_info} في {$this->session->city}, {$this->session->country}",
        ];
    }
}
