<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'created_by',
        'parent_user_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
        ];
    }

    /**
     * Get the network connections for the user
     */
    public function networkConnections()
    {
        return $this->hasMany(NetworkConnection::class);
    }

    /**
     * Get the networks connected to this user
     */
    public function connectedNetworks()
    {
        return $this->belongsToMany(Network::class, 'network_connections')
            ->withPivot(['connection_name', 'status', 'is_connected', 'connected_at'])
            ->withTimestamps();
    }

    /**
     * Check if user is connected to a specific network
     */
    public function isConnectedToNetwork($networkId): bool
    {
        return $this->networkConnections()
            ->where('network_id', $networkId)
            ->where('is_connected', true)
            ->exists();
    }

    /**
     * Get user's active network connections count
     */
    public function getActiveNetworkConnectionsCount(): int
    {
        return $this->networkConnections()
            ->where('is_connected', true)
            ->count();
    }
    
    /**
     * Get the campaigns for the user
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }
    
    /**
     * Get the purchases for the user
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
    
    /**
     * Get the coupons for the user through campaigns
     */
    public function coupons()
    {
        return $this->hasManyThrough(Coupon::class, Campaign::class);
    }
    
    /**
     * Get the user who created this user
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the parent user (main account)
     */
    public function parentUser()
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }
    
    /**
     * Get the users created by this user
     */
    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }
    
    /**
     * Get the sub-users under this user
     */
    public function subUsers()
    {
        return $this->hasMany(User::class, 'parent_user_id');
    }
    
    /**
     * Check if user is a sub-user
     */
    public function isSubUser(): bool
    {
        return !is_null($this->parent_user_id);
    }
    
    /**
     * Get the main parent user (root)
     */
    public function getMainParent()
    {
        return $this->parent_user_id ? User::find($this->parent_user_id) : $this;
    }
}
