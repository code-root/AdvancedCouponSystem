<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'currency',
        'trial_days',
        'max_networks',
        'daily_sync_limit',
        'monthly_sync_limit',
        'revenue_cap',
        'orders_cap',
        'sync_window_unit',
        'sync_window_size',
        'sync_allowed_from_time',
        'sync_allowed_to_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'revenue_cap' => 'decimal:2',
        'sync_allowed_from_time' => 'datetime:H:i:s',
        'sync_allowed_to_time' => 'datetime:H:i:s',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}




