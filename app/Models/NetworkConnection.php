<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class NetworkConnection extends Model
{
    use HasFactory, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'network_id',
        'connection_name',
        'status',
        'is_connected',
        'connected_at',
        'last_sync',
        'client_id',
        'client_secret',
        'token',
        'contact_id',
        'api_endpoint',
        'credentials',
        'api_settings',
        'sync_settings',
        'error_log',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_connected' => 'boolean',
        'connected_at' => 'datetime',
        'last_sync' => 'datetime',
        'credentials' => 'array',
        'api_settings' => 'array',
        'sync_settings' => 'array',
        'error_log' => 'array',
    ];

    /**
     * Get the user that owns the network connection.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the network that this connection belongs to.
     */
    public function network()
    {
        return $this->belongsTo(Network::class);
    }

    /**
     * Scope to get only connected networks.
     */
    public function scopeConnected($query)
    {
        return $query->where('is_connected', true);
    }

    /**
     * Scope to get only active connections.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if connection is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->is_connected;
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
     * Get API setting by key.
     */
    public function getApiSetting(string $key, $default = null)
    {
        $settings = $this->api_settings ?? [];
        return $settings[$key] ?? $default;
    }

    /**
     * Get sync setting by key.
     */
    public function getSyncSetting(string $key, $default = null)
    {
        $settings = $this->sync_settings ?? [];
        return $settings[$key] ?? $default;
    }

    /**
     * Log an error.
     */
    public function logError(string $error, array $context = []): void
    {
        $errorLog = $this->error_log ?? [];
        $errorLog[] = [
            'error' => $error,
            'context' => $context,
            'timestamp' => now()->toISOString(),
        ];
        
        $this->update(['error_log' => $errorLog]);
    }

    /**
     * Clear error log.
     */
    public function clearErrors(): void
    {
        $this->update(['error_log' => []]);
    }
}