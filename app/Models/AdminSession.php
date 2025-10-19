<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AdminSession extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'admin_id',
        'session_id',
        'ip_address',
        'user_agent',
        'device_name',
        'platform',
        'browser',
        'login_at',
        'last_activity_at',
        'logout_at',
        'is_active',
        'location',
        'meta',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'logout_at' => 'datetime',
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    /**
     * Get the admin that owns the session.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Scope a query to only include active sessions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include sessions from today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('login_at', today());
    }

    /**
     * Scope a query to only include sessions from this week.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('login_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope a query to only include sessions from this month.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('login_at', now()->month)
                    ->whereYear('login_at', now()->year);
    }

    /**
     * Get the session duration in minutes.
     */
    public function getDurationAttribute(): int
    {
        $endTime = $this->logout_at ?? now();
        return $this->login_at->diffInMinutes($endTime);
    }

    /**
     * Get the session duration in human readable format.
     */
    public function getDurationHumanAttribute(): string
    {
        $endTime = $this->logout_at ?? now();
        return $this->login_at->diffForHumans($endTime, true);
    }

    /**
     * Check if the session is expired.
     */
    public function isExpired(): bool
    {
        if (!$this->is_active) {
            return true;
        }

        // Session expires after 8 hours of inactivity
        $expiryTime = $this->last_activity_at->addHours(8);
        return now()->isAfter($expiryTime);
    }

    /**
     * Mark session as inactive.
     */
    public function markInactive(): void
    {
        $this->update([
            'is_active' => false,
            'logout_at' => now(),
        ]);
    }

    /**
     * Update last activity timestamp.
     */
    public function updateActivity(): void
    {
        $this->update([
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Get active sessions count for an admin.
     */
    public static function getActiveCountForAdmin(int $adminId): int
    {
        return static::where('admin_id', $adminId)
                    ->where('is_active', true)
                    ->count();
    }

    /**
     * Get total sessions count for an admin.
     */
    public static function getTotalCountForAdmin(int $adminId): int
    {
        return static::where('admin_id', $adminId)->count();
    }

    /**
     * Get sessions statistics.
     */
    public static function getStatistics(): array
    {
        $today = today();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        return [
            'total_sessions' => static::count(),
            'active_sessions' => static::where('is_active', true)->count(),
            'sessions_today' => static::whereDate('login_at', $today)->count(),
            'sessions_this_week' => static::where('login_at', '>=', $thisWeek)->count(),
            'sessions_this_month' => static::where('login_at', '>=', $thisMonth)->count(),
            'unique_admins_today' => static::whereDate('login_at', $today)->distinct('admin_id')->count(),
            'unique_admins_this_week' => static::where('login_at', '>=', $thisWeek)->distinct('admin_id')->count(),
            'unique_admins_this_month' => static::where('login_at', '>=', $thisMonth)->distinct('admin_id')->count(),
        ];
    }
}

