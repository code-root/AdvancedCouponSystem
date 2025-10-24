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
        'trial_days' => 'integer',
        'max_networks' => 'integer',
        'daily_sync_limit' => 'integer',
        'monthly_sync_limit' => 'integer',
        'orders_cap' => 'integer',
        'sync_window_size' => 'integer',
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
            $features = json_decode($value, true);
            // Merge with database columns to ensure all features are available
            return array_merge($this->getDefaultFeatures(), $features);
        }
        
        // Otherwise, build features from existing columns
        return $this->getDefaultFeatures();
    }
    
    /**
     * Get default features from database columns.
     */
    private function getDefaultFeatures()
    {
        return [
            // Network and Campaign limits
            'networks_limit' => $this->max_networks ?? 0,
            'campaigns_limit' => -1, // Unlimited by default
            
            // Sync limits
            'syncs_per_month' => $this->monthly_sync_limit ?? -1,
            'syncs_per_day' => $this->daily_sync_limit ?? -1,
            'sync_frequency' => $this->sync_window_size . ' ' . $this->sync_window_unit,
            
            // Business limits
            'orders_limit' => $this->orders_cap ?? -1, // Monthly orders limit
            'revenue_limit' => $this->revenue_cap ?? -1, // Monthly revenue limit
            
            // Feature flags
            'export_data' => true, // Enable for all plans
            'api_access' => false, // Disable by default
            'priority_support' => $this->is_popular ?? false, // Based on popularity
            'advanced_analytics' => false, // Disable by default
            
            // Additional plan info
            'trial_days' => $this->trial_days ?? 0,
            'billing_cycle' => $this->billing_cycle ?? 'monthly',
            'currency' => $this->currency ?? 'USD',
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




