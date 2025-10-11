<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\UserSession;

class NewLoginNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $session;

    /**
     * Create a new notification instance.
     */
    public function __construct(UserSession $session)
    {
        $this->session = $session;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification (for database).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'تسجيل دخول جديد',
            'message' => "تم تسجيل الدخول من جهاز {$this->session->device_info}",
            'session_id' => $this->session->id,
            'device' => $this->session->device_info,
            'browser' => $this->session->browser_info,
            'location' => $this->session->location,
            'ip_address' => $this->session->ip_address,
            'country' => $this->session->country,
            'city' => $this->session->city,
            'login_at' => $this->session->login_at?->toIso8601String(),
            'icon' => 'ti-login',
            'type' => 'login',
            'color' => 'success',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'تسجيل دخول جديد',
            'message' => "تم تسجيل الدخول من جهاز {$this->session->device_info}",
            'session_id' => $this->session->id,
            'device' => $this->session->device_info,
            'browser' => $this->session->browser_info,
            'location' => $this->session->location,
            'ip_address' => $this->session->ip_address,
            'country' => $this->session->country,
            'city' => $this->session->city,
            'login_at' => $this->session->login_at?->toIso8601String(),
            'icon' => 'ti-login',
            'type' => 'login',
            'color' => 'success',
        ]);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Login Detected')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new login was detected on your account.')
            ->line('**Device:** ' . $this->session->device_info)
            ->line('**Browser:** ' . $this->session->browser_info)
            ->line('**Location:** ' . $this->session->location)
            ->line('**IP Address:** ' . $this->session->ip_address)
            ->line('**Time:** ' . $this->session->login_at?->format('M d, Y H:i:s'))
            ->line('If this was not you, please secure your account immediately.')
            ->action('View Sessions', route('sessions.index'));
    }
}
