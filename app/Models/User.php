<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
}
