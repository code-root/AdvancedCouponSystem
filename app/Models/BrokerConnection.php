<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrokerConnection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'broker_id',
        'connection_name',
        'client_id',
        'client_secret',
        'token',
        'contact_id',
        'api_endpoint',
        'status',
        'access_token',
        'refresh_token',
        'credentials',
        'settings',
        'is_active',
        'is_connected',
        'connected_at',
        'last_sync',
        'expires_at',
        'error_message',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'credentials' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_connected' => 'boolean',
        'connected_at' => 'datetime',
        'last_sync' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns the connection.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the broker for the connection.
     */
    public function broker()
    {
        return $this->belongsTo(Broker::class);
    }

    /**
     * Check if connection is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if connection is connected.
     */
    public function isConnected(): bool
    {
        return $this->is_connected;
    }

    /**
     * Check if token is expired.
     */
    public function isTokenExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Get setting by key.
     */
    public function getSetting(string $key, $default = null)
    {
        $settings = $this->settings ?? [];
        return $settings[$key] ?? $default;
    }

    /**
     * Get credential by key.
     */
    public function getCredential(string $key, $default = null)
    {
        $credentials = $this->credentials ?? [];
        return $credentials[$key] ?? $default;
    }

    /**
     * Update connection status.
     */
    public function updateConnectionStatus(bool $connected, string $errorMessage = null): void
    {
        $this->update([
            'is_connected' => $connected,
            'connected_at' => $connected ? now() : null,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Update last sync time.
     */
    public function updateLastSync(): void
    {
        $this->update(['last_sync' => now()]);
    }
}

