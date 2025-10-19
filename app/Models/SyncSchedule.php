<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use App\Traits\Auditable;

class SyncSchedule extends Model
{
    use Auditable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'network_ids',
        'sync_type',
        'interval_minutes',
        'max_runs_per_day',
        'runs_today',
        'last_run_at',
        'next_run_at',
        'is_active',
        'date_range_type',
        'custom_date_from',
        'custom_date_to',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'network_ids' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'custom_date_from' => 'date',
        'custom_date_to' => 'date',
        'settings' => 'array',
        'runs_today' => 'integer',
        'interval_minutes' => 'integer',
        'max_runs_per_day' => 'integer',
    ];

    /**
     * Get the user that owns the sync schedule.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sync logs for this schedule.
     */
    public function syncLogs(): HasMany
    {
        return $this->hasMany(SyncLog::class);
    }

    /**
     * Get the networks for this schedule.
     */
    public function networks()
    {
        return Network::whereIn('id', $this->network_ids ?? [])->get();
    }

    /**
     * Check if schedule can run now.
     */
    public function canRun(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->runs_today >= $this->max_runs_per_day) {
            return false;
        }

        if ($this->next_run_at && Carbon::now()->lt($this->next_run_at)) {
            return false;
        }

        return true;
    }

    /**
     * Calculate next run time.
     */
    public function calculateNextRunTime(): Carbon
    {
        return Carbon::now()->addMinutes($this->interval_minutes);
    }

    /**
     * Increment runs counter.
     */
    public function incrementRunsToday(): void
    {
        $this->increment('runs_today');
    }

    /**
     * Reset daily counters.
     */
    public function resetDailyCounters(): void
    {
        $this->update(['runs_today' => 0]);
    }

    /**
     * Get date range based on type.
     */
    public function getDateRange(): array
    {
        return match($this->date_range_type) {
            'today' => [
                'from' => Carbon::today()->format('Y-m-d'),
                'to' => Carbon::today()->format('Y-m-d'),
            ],
            'yesterday' => [
                'from' => Carbon::yesterday()->format('Y-m-d'),
                'to' => Carbon::yesterday()->format('Y-m-d'),
            ],
            'last_7_days' => [
                'from' => Carbon::now()->subDays(7)->format('Y-m-d'),
                'to' => Carbon::today()->format('Y-m-d'),
            ],
            'last_30_days' => [
                'from' => Carbon::now()->subDays(30)->format('Y-m-d'),
                'to' => Carbon::today()->format('Y-m-d'),
            ],
            'current_month' => [
                'from' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                'to' => Carbon::today()->format('Y-m-d'),
            ],
            'previous_month' => [
                'from' => Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d'),
                'to' => Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d'),
            ],
            'custom' => [
                'from' => $this->custom_date_from?->format('Y-m-d') ?? Carbon::today()->format('Y-m-d'),
                'to' => $this->custom_date_to?->format('Y-m-d') ?? Carbon::today()->format('Y-m-d'),
            ],
            default => [
                'from' => Carbon::today()->format('Y-m-d'),
                'to' => Carbon::today()->format('Y-m-d'),
            ],
        };
    }

    /**
     * Scope for active schedules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for schedules ready to run.
     */
    public function scopeReadyToRun($query)
    {
        return $query->active()
            ->where(function($q) {
                $q->whereNull('next_run_at')
                  ->orWhere('next_run_at', '<=', Carbon::now());
            })
            ->whereRaw('runs_today < max_runs_per_day');
    }
}
