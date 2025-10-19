<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErrorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'message',
        'context',
        'file',
        'line',
        'trace',
        'url',
        'method',
        'ip_address',
        'user_agent',
        'user_id',
        'session_id',
        'request_id',
        'extra_data',
        'is_resolved',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
        'occurrence_count',
        'last_occurred_at',
    ];

    protected $casts = [
        'context' => 'array',
        'extra_data' => 'array',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
        'last_occurred_at' => 'datetime',
    ];

    /**
     * Get the user who encountered the error.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who resolved the error.
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'resolved_by');
    }

    /**
     * Scope for unresolved errors.
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Scope for resolved errors.
     */
    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    /**
     * Scope for errors by level.
     */
    public function scopeByLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope for errors by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for recent errors.
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope for frequent errors.
     */
    public function scopeFrequent($query, int $minOccurrences = 5)
    {
        return $query->where('occurrence_count', '>=', $minOccurrences);
    }

    /**
     * Mark error as resolved.
     */
    public function markAsResolved(int $resolvedBy, string $notes = null): void
    {
        $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => $resolvedBy,
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Increment occurrence count.
     */
    public function incrementOccurrence(): void
    {
        $this->increment('occurrence_count');
        $this->update(['last_occurred_at' => now()]);
    }

    /**
     * Get formatted context data.
     */
    public function getFormattedContextAttribute(): string
    {
        if (empty($this->context)) {
            return 'No context data';
        }

        return json_encode($this->context, JSON_PRETTY_PRINT);
    }

    /**
     * Get formatted extra data.
     */
    public function getFormattedExtraDataAttribute(): string
    {
        if (empty($this->extra_data)) {
            return 'No extra data';
        }

        return json_encode($this->extra_data, JSON_PRETTY_PRINT);
    }

    /**
     * Get error severity color.
     */
    public function getSeverityColorAttribute(): string
    {
        return match($this->level) {
            'error' => 'danger',
            'warning' => 'warning',
            'info' => 'info',
            'debug' => 'secondary',
            default => 'light',
        };
    }

    /**
     * Get error severity icon.
     */
    public function getSeverityIconAttribute(): string
    {
        return match($this->level) {
            'error' => 'ti ti-alert-circle',
            'warning' => 'ti ti-alert-triangle',
            'info' => 'ti ti-info-circle',
            'debug' => 'ti ti-bug',
            default => 'ti ti-help-circle',
        };
    }

    /**
     * Get truncated message.
     */
    public function getTruncatedMessageAttribute(): string
    {
        return strlen($this->message) > 100 
            ? substr($this->message, 0, 100) . '...' 
            : $this->message;
    }

    /**
     * Get time since last occurrence.
     */
    public function getTimeSinceLastOccurrenceAttribute(): string
    {
        if (!$this->last_occurred_at) {
            return 'Never';
        }

        return $this->last_occurred_at->diffForHumans();
    }

    /**
     * Get time since creation.
     */
    public function getTimeSinceCreatedAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Check if error is recent.
     */
    public function isRecent(int $hours = 24): bool
    {
        return $this->created_at->isAfter(now()->subHours($hours));
    }

    /**
     * Check if error is frequent.
     */
    public function isFrequent(int $minOccurrences = 5): bool
    {
        return $this->occurrence_count >= $minOccurrences;
    }

    /**
     * Get error statistics.
     */
    public static function getStatistics(): array
    {
        $total = self::count();
        $unresolved = self::unresolved()->count();
        $resolved = self::resolved()->count();
        $recent = self::recent(24)->count();
        $frequent = self::frequent(5)->count();

        $byLevel = self::selectRaw('level, COUNT(*) as count')
            ->groupBy('level')
            ->pluck('count', 'level')
            ->toArray();

        return [
            'total' => $total,
            'unresolved' => $unresolved,
            'resolved' => $resolved,
            'recent' => $recent,
            'frequent' => $frequent,
            'by_level' => $byLevel,
            'resolution_rate' => $total > 0 ? round(($resolved / $total) * 100, 2) : 0,
        ];
    }
}