<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    
    protected $fillable = [
        'campaign_id',
        'code',
        'description',
        'status',
        'discount_value',
        'discount_type',
        'expires_at',
        'usage_limit',
        'used_count',
        'created_by',
        'updated_by',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'expires_at' => 'date',
        'discount_value' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
    ];

    /**
     * Get the campaign that owns the coupon.
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the purchases for the coupon.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Check if coupon is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if coupon is used.
     */
    public function isUsed(): bool
    {
        return $this->status === 'used';
    }

    /**
     * Check if coupon is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' || 
               ($this->expires_at && $this->expires_at->isPast());
    }

    /**
     * Check if coupon is invalid.
     */
    public function isInvalid(): bool
    {
        return $this->status === 'invalid';
    }

    /**
     * Check if coupon has reached usage limit.
     */
    public function hasReachedUsageLimit(): bool
    {
        if (!$this->usage_limit) {
            return false;
        }

        return $this->used_count >= $this->usage_limit;
    }

    /**
     * Check if coupon is available for use.
     */
    public function isAvailable(): bool
    {
        return $this->isActive() && 
               !$this->isExpired() && 
               !$this->hasReachedUsageLimit();
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
        
        if ($this->hasReachedUsageLimit()) {
            $this->update(['status' => 'used']);
        }
    }

    /**
     * Get metadata by key.
     */
    public function getMetadata(string $key, $default = null)
    {
        $metadata = $this->metadata ?? [];
        return $metadata[$key] ?? $default;
    }

    /**
     * Get total purchases for the coupon.
     */
    public function getTotalPurchases(): int
    {
        return $this->purchases()->sum('quantity');
    }

    /**
     * Get total revenue for the coupon.
     */
    public function getTotalRevenue(): float
    {
        return $this->purchases()->sum('revenue');
    }

    /**
     * Get total revenue for the coupon.
     */
    public function getTotalSalesAmount(): float
    {
        return $this->purchases()->sum('sales_amount');
    }
}

