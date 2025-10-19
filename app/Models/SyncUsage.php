<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'period',
        'window_start',
        'window_end',
        'sync_count',
        'revenue_sum',
        'orders_count',
    ];

    protected $casts = [
        'window_start' => 'datetime',
        'window_end' => 'datetime',
        'revenue_sum' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}




