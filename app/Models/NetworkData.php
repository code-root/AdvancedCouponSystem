<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NetworkData extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'broker_id',
        'user_id',
        'data_type',
        'data',
        'data_date',
        'synced_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'data_date' => 'date',
        'synced_at' => 'datetime',
    ];

    /**
     * Get the broker that owns the data.
     */
    public function broker()
    {
        return $this->belongsTo(Broker::class);
    }

    /**
     * Get the user that owns the data.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get data by key.
     */
    public function getData(string $key, $default = null)
    {
        $data = $this->data ?? [];
        return $data[$key] ?? $default;
    }

    /**
     * Scope a query to only include data of a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('data_type', $type);
    }

    /**
     * Scope a query to only include data for a specific date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('data_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include recent data.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('data_date', '>=', now()->subDays($days));
    }
}

