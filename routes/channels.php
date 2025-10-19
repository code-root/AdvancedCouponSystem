<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Private channel for user notifications and session updates
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private channel for specific session (for real-time logout)
Broadcast::channel('session.{sessionId}', function ($user, $sessionId) {
    // User can only listen to their own sessions
    return session()->getId() === $sessionId;
});

// Private channel for admin notifications
Broadcast::channel('admin.{id}', function ($admin, $id) {
    return (int) $admin->id === (int) $id;
});

// Public channel for admin sessions (admin only)
Broadcast::channel('admin-sessions', function ($admin) {
    return $admin instanceof \App\Models\Admin;
});

// Public channel for subscription updates (admin only)
Broadcast::channel('subscription-updates', function ($admin) {
    return $admin instanceof \App\Models\Admin;
});

// Private channel for user subscription updates
Broadcast::channel('user-subscription.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

