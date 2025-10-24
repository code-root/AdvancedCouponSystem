<?php

namespace App\Models;

use App\Events\SubscriptionCreated;
use App\Events\SubscriptionCancelled;
use App\Events\SubscriptionUpgraded;
use App\Events\SubscriptionUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Subscription extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'cancelled_at',
        'billing_interval',
        'gateway',
        'gateway_customer_id',
        'gateway_subscription_id',
        'latest_invoice_url',
        'paid_until',
        'meta',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'paid_until' => 'datetime',
        'meta' => 'array',
        'user_id' => 'integer',
        'plan_id' => 'integer',
    ];

    /**
     * The event map for the model.
     */
    protected $dispatchesEvents = [
        'created' => SubscriptionCreated::class,
        'updated' => SubscriptionUpdated::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}




