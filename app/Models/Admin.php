<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    /**
     * Get the site settings created by this admin.
     */
    public function createdSiteSettings()
    {
        return $this->hasMany(SiteSetting::class, 'created_by');
    }

    /**
     * Get the site settings updated by this admin.
     */
    public function updatedSiteSettings()
    {
        return $this->hasMany(SiteSetting::class, 'updated_by');
    }

    /**
     * Check if admin is active.
     */
    public function isActive(): bool
    {
        return $this->active;
    }
}