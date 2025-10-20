<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Plan extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'name',
        'description',
        'price',
        'billing_cycle',
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
        'is_popular',
        'features',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'price' => 'decimal:2',
        'revenue_cap' => 'decimal:2',
        'sync_allowed_from_time' => 'datetime:H:i:s',
        'sync_allowed_to_time' => 'datetime:H:i:s',
        'features' => 'array',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    
    /**
     * Get features attribute with fallback to database columns.
     */
    public function getFeaturesAttribute($value)
    {
        // If features JSON exists, use it
        if ($value) {
            return json_decode($value, true);
        }
        
        // Otherwise, build features from existing columns
        return [
            'networks_limit' => $this->max_networks ?? 0,
            'campaigns_limit' => -1, // Unlimited by default
            'syncs_per_month' => $this->monthly_sync_limit ?? -1,
            'orders_limit' => $this->orders_cap ?? -1, // Monthly orders limit
            'revenue_limit' => $this->revenue_cap ?? -1, // Monthly revenue limit
            'export_data' => true, // Enable for all plans
            'api_access' => false, // Disable by default
            'priority_support' => false, // Disable by default
            'advanced_analytics' => false, // Disable by default
        ];
    }
    
    /**
     * Set features attribute.
     */
    public function setFeaturesAttribute($value)
    {
        $this->attributes['features'] = is_array($value) ? json_encode($value) : $value;
    }
}




