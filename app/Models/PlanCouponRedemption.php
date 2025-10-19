<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanCouponRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_coupon_id',
        'user_id',
        'subscription_id',
        'discount_applied',
        'redeemed_at',
    ];

    protected $casts = [
        'discount_applied' => 'decimal:2',
        'redeemed_at' => 'datetime',
    ];

    public function coupon()
    {
        return $this->belongsTo(PlanCoupon::class, 'plan_coupon_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}




