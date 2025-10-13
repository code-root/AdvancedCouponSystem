<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'coupon_id',
        'purchase_type',
        'campaign_id',
        'network_id',
        'user_id',
        'order_id',
        'network_order_id',
        'order_value',
        'commission',
        'revenue',
        'quantity',
        'currency',
        'country_code',
        'customer_type',
        'status',
        'order_date',
        'purchase_date',
        'created_by',
        'updated_by',
        'last_updated',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'order_value' => 'decimal:2',
        'commission' => 'decimal:2',
        'revenue' => 'decimal:2',
        'quantity' => 'integer',
        'order_date' => 'date',
        'purchase_date' => 'date',
        'last_updated' => 'datetime',
        'purchase_type' => 'string',
    ];

    /**
     * Get the coupon that owns the purchase.
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the campaign that owns the purchase.
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the network that owns the purchase.
     */
    public function network()
    {
        return $this->belongsTo(Network::class);
    }

    /**
     * Get the user that owns the purchase.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the country for the purchase.
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }

    /**
     * Check if purchase is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if purchase is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if purchase is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if purchase is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
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
     * Get commission rate as percentage.
     */
    public function getCommissionRate(): float
    {
        if ($this->order_value == 0) {
            return 0;
        }

        return ($this->commission / $this->order_value) * 100;
    }

    /**
     * Get revenue rate as percentage.
     */
    public function getRevenueRate(): float
    {
        if ($this->order_value == 0) {
            return 0;
        }

        return ($this->revenue / $this->order_value) * 100;
    }

    /**
     * Check if purchase is coupon-based.
     */
    public function isCoupon(): bool
    {
        return $this->purchase_type === 'coupon';
    }

    /**
     * Check if purchase is link-based.
     */
    public function isLink(): bool
    {
        return $this->purchase_type === 'link';
    }

    /**
     * Get purchase type badge.
     */
    public function getPurchaseTypeBadge(): string
    {
        if ($this->isCoupon()) {
            return '<span class="badge bg-info-subtle text-info"><i class="ti ti-ticket me-1"></i>Coupon</span>';
        } else {
            return '<span class="badge bg-warning-subtle text-warning"><i class="ti ti-link me-1"></i>Direct Link</span>';
        }
    }
}

