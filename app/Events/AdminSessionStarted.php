<?php

namespace App\Events;

use App\Models\Admin;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminSessionStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $admin;
    public $sessionData;

    /**
     * Create a new event instance.
     */
    public function __construct(Admin $admin, array $sessionData = [])
    {
        $this->admin = $admin;
        $this->sessionData = $sessionData;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('admin-sessions'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'type' => 'admin_session_started',
            'admin' => [
                'id' => $this->admin->id,
                'name' => $this->admin->name,
                'email' => $this->admin->email,
            ],
            'session' => [
                'ip_address' => $this->sessionData['ip_address'] ?? null,
                'user_agent' => $this->sessionData['user_agent'] ?? null,
                'device_type' => $this->sessionData['device_type'] ?? null,
                'device_name' => $this->sessionData['device_name'] ?? null,
                'platform' => $this->sessionData['platform'] ?? null,
                'browser' => $this->sessionData['browser'] ?? null,
                'city' => $this->sessionData['city'] ?? null,
                'country' => $this->sessionData['country'] ?? null,
                'started_at' => now(),
            ],
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'admin.session.started';
    }
}