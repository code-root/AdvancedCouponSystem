<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserSession extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'device_type',
        'device_name',
        'platform',
        'browser',
        'browser_version',
        'country',
        'country_code',
        'region',
        'city',
        'timezone',
        'latitude',
        'longitude',
        'referrer_url',
        'landing_page',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'payload',
        'last_activity',
        'last_heartbeat',
        'login_at',
        'expires_at',
        'is_active',
        'is_online',
        'logout_reason',
    ];

    protected $casts = [
        'last_activity' => 'datetime',
        'last_heartbeat' => 'datetime',
        'login_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'is_online' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Get the user that owns the session
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if session is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        
        return Carbon::now()->greaterThan($this->expires_at);
    }

    /**
     * Check if session is current
     */
    public function isCurrent(): bool
    {
        return $this->session_id === session()->getId();
    }

    /**
     * Mark session as inactive
     */
    public function markAsInactive(string $reason = 'manual'): void
    {
        $this->update([
            'is_active' => false,
            'logout_reason' => $reason,
        ]);
    }

    /**
     * Get formatted device info
     */
    public function getDeviceInfoAttribute(): string
    {
        $parts = array_filter([
            $this->device_name,
            $this->platform,
        ]);
        
        return implode(' - ', $parts) ?: 'Unknown Device';
    }

    /**
     * Get formatted location
     */
    public function getLocationAttribute(): string
    {
        $parts = array_filter([
            $this->city,
            $this->region,
            $this->country,
        ]);
        
        return implode(', ', $parts) ?: 'Unknown Location';
    }

    /**
     * Get browser with version
     */
    public function getBrowserInfoAttribute(): string
    {
        if (!$this->browser) {
            return 'Unknown Browser';
        }
        
        $version = $this->browser_version ? ' ' . $this->browser_version : '';
        return $this->browser . $version;
    }

    /**
     * Get session duration
     */
    public function getDurationAttribute(): string
    {
        if (!$this->login_at) {
            return 'Unknown';
        }
        
        $endTime = $this->is_active ? Carbon::now() : ($this->updated_at ?? Carbon::now());
        
        return $this->login_at->diffForHumans($endTime, true);
    }

    /**
     * Get device icon class
     */
    public function getDeviceIconAttribute(): string
    {
        return match($this->device_type) {
            'mobile' => 'ti-device-mobile',
            'tablet' => 'ti-device-tablet',
            'desktop' => 'ti-device-desktop',
            default => 'ti-devices',
        };
    }

    /**
     * Scope active sessions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope inactive sessions
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope expired sessions
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', Carbon::now());
    }
    
    /**
     * Scope online sessions (heartbeat in last 5 minutes)
     */
    public function scopeOnline($query)
    {
        return $query->where('is_online', true)
            ->where('is_active', true)
            ->where('last_heartbeat', '>=', Carbon::now()->subMinutes(5));
    }
    
    /**
     * Check if session is online (last heartbeat within 5 minutes)
     */
    public function isOnline(): bool
    {
        if (!$this->is_active || !$this->is_online) {
            return false;
        }
        
        if (!$this->last_heartbeat) {
            return false;
        }
        
        return Carbon::now()->diffInMinutes($this->last_heartbeat) <= 5;
    }
    
    /**
     * Update heartbeat
     */
    public function updateHeartbeat(): void
    {
        $this->update([
            'last_heartbeat' => now(),
            'is_online' => true,
            'last_activity' => now(),
        ]);
    }
}

