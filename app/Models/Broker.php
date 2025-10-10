<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Broker extends Model
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
     * Get the country for the broker.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the broker connections for the broker.
     */
    public function connections()
    {
        return $this->hasMany(BrokerConnection::class);
    }

    /**
     * Get the broker data for the broker.
     */
    public function data()
    {
        return $this->hasMany(BrokerData::class);
    }

    /**
     * Get the campaigns for the broker.
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * Get the purchases for the broker.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get the broker data for the broker.
     */
    public function brokerData()
    {
        return $this->hasMany(BrokerData::class);
    }

    /**
     * Get users connected to this broker.
     */
    public function connectedUsers()
    {
        return $this->belongsToMany(User::class, 'broker_connections')
            ->wherePivot('is_connected', true)
            ->withPivot(['connection_name', 'connected_at', 'last_sync']);
    }

    /**
     * Check if broker supports a specific feature.
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
     * Check if broker is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if broker is connected.
     */
    public function isConnected(): bool
    {
        return $this->is_connected;
    }
}

