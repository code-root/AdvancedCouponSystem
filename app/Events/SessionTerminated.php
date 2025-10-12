<?php

namespace App\Events;

use App\Models\UserSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionTerminated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $session;
    public $userId;
    public $reason;

    /**
     * Create a new event instance.
     */
    public function __construct(UserSession $session, string $reason = 'forced')
    {
        $this->session = $session;
        $this->userId = $session->user_id;
        $this->reason = $reason;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId),
            new PrivateChannel('session.' . $this->session->session_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'session.terminated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->session->id,
            'device_session_id' => $this->session->session_id,
            'reason' => $this->reason,
            'message' => 'Your session has been terminated',
            'device' => $this->session->device_info,
            'browser' => $this->session->browser_info,
            'location' => $this->session->location,
            'terminated_at' => now()->toIso8601String(),
        ];
    }
}

