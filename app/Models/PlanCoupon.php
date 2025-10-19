<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class PlanCoupon extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'code',
        'type',
        'value',
        'max_redemptions',
        'redemptions_count',
        'expires_at',
        'active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'expires_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function redemptions()
    {
        return $this->hasMany(PlanCouponRedemption::class);
    }
}




