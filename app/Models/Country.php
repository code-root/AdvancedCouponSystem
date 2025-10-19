<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Country extends Model
{
    use HasFactory, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'code_3',
        'currency_code',
        'phone_code',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the purchases for the country.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'country_code', 'code');
    }

    /**
     * Check if country is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get total purchases for the country.
     */
    public function getTotalPurchases(): int
    {
        return $this->purchases()->count();
    }

    /**
     * Get total revenue for the country.
     */
    public function getTotalRevenue(): float
    {
        return $this->purchases()->sum('revenue');
    }

    /**
     * Get total revenue for the country.
     */
    public function getTotalSalesAmount(): float
    {
        return $this->purchases()->sum('sales_amount');
    }
}
