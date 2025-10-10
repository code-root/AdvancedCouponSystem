<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Network extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'logo_url',
        'api_url',
        'auth_url',
        'callback_url',
        'token',
        'client_id',
        'client_secret',
        'contact_id',
        'agency_id',
        'credentials',
        'api_settings',
        'is_active',
        'is_connected',
        'last_sync',
        'supported_features',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'credentials' => 'array',
        'api_settings' => 'array',
        'supported_features' => 'array',
        'is_active' => 'boolean',
        'is_connected' => 'boolean',
        'last_sync' => 'datetime',
    ];

    /**
     * Get the country for the network.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the network connections for the network.
     */
    public function connections()
    {
        return $this->hasMany(NetworkConnection::class);
    }

    /**
     * Get the network data for the network.
     */
    public function data()
    {
        return $this->hasMany(NetworkData::class);
    }

    /**
     * Get the campaigns for the network.
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * Get the purchases for the network.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get the network data for the network.
     */
    public function networkData()
    {
        return $this->hasMany(NetworkData::class);
    }

    /**
     * Get users connected to this network.
     */
    public function connectedUsers()
    {
        return $this->belongsToMany(User::class, 'network_connections')
            ->wherePivot('is_connected', true)
            ->withPivot(['connection_name', 'connected_at', 'last_sync']);
    }

    /**
     * Check if network supports a specific feature.
     */
    public function supportsFeature(string $feature): bool
    {
        $features = $this->supported_features ?? [];
        return in_array($feature, $features);
    }

    /**
     * Get API setting by key.
     */
    public function getApiSetting(string $key, $default = null)
    {
        $settings = $this->api_settings ?? [];
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
     * Check if network is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if network is connected.
     */
    public function isConnected(): bool
    {
        return $this->is_connected;
    }
}