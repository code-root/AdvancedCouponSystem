<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class SyncLog extends Model
{
    use Auditable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sync_schedule_id',
        'user_id',
        'network_id',
        'sync_type',
        'status',
        'started_at',
        'completed_at',
        'duration_seconds',
        'records_synced',
        'campaigns_count',
        'coupons_count',
        'purchases_count',
        'error_message',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration_seconds' => 'integer',
        'records_synced' => 'integer',
        'campaigns_count' => 'integer',
        'coupons_count' => 'integer',
        'purchases_count' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the sync schedule that owns the log.
     */
    public function syncSchedule(): BelongsTo
    {
        return $this->belongsTo(SyncSchedule::class);
    }

    /**
     * Get the user that owns the log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the network for this log.
     */
    public function network(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Network::class);
    }

    /**
     * Scope for completed logs.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed logs.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for processing logs.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Check if log is manual sync.
     */
    public function isManual(): bool
    {
        return $this->sync_schedule_id === null;
    }

    /**
     * Mark as processing.
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark as completed.
     */
    public function markAsCompleted(array $data = []): void
    {
        $this->update(array_merge([
            'status' => 'completed',
            'completed_at' => now(),
            'duration_seconds' => now()->diffInSeconds($this->started_at),
        ], $data));
    }

    /**
     * Mark as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'duration_seconds' => now()->diffInSeconds($this->started_at),
            'error_message' => $errorMessage,
        ]);
    }
}
