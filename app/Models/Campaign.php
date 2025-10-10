<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'network_id',
        'user_id',
        'network_campaign_id',
        'name',
        'description',
        'logo_url',
        'advertiser_name',
        'advertiser_id',
        'campaign_type',
        'status',
        'created_by',
        'updated_by',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Get the network that owns the campaign.
     */
    public function network()
    {
        return $this->belongsTo(Network::class);
    }

    /**
     * Get the user that owns the campaign.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the coupons for the campaign.
     */
    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    /**
     * Get the purchases for the campaign.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Check if campaign is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if campaign is paused.
     */
    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    /**
     * Check if campaign is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    /**
     * Get setting by key.
     */
    public function getSetting(string $key, $default = null)
    {
        $settings = $this->settings ?? [];
        return $settings[$key] ?? $default;
    }

    /**
     * Get total purchases for the campaign.
     */
    public function getTotalPurchases(): int
    {
        return $this->purchases()->sum('quantity');
    }

    /**
     * Get total revenue for the campaign.
     */
    public function getTotalRevenue(): float
    {
        return $this->purchases()->sum('revenue');
    }

    /**
     * Get total commission for the campaign.
     */
    public function getTotalCommission(): float
    {
        return $this->purchases()->sum('commission');
    }
}

