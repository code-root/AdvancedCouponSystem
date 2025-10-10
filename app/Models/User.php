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
     * Get the broker connections for the user
     */
    public function brokerConnections()
    {
        return $this->hasMany(BrokerConnection::class);
    }

    /**
     * Get the brokers connected to this user
     */
    public function connectedBrokers()
    {
        return $this->belongsToMany(Broker::class, 'broker_connections')
            ->withPivot(['connection_name', 'status', 'is_connected', 'connected_at'])
            ->withTimestamps();
    }

    /**
     * Check if user is connected to a specific broker
     */
    public function isConnectedToBroker($brokerId): bool
    {
        return $this->brokerConnections()
            ->where('broker_id', $brokerId)
            ->where('is_connected', true)
            ->exists();
    }

    /**
     * Get user's active broker connections count
     */
    public function getActiveBrokerConnectionsCount(): int
    {
        return $this->brokerConnections()
            ->where('is_connected', true)
            ->count();
    }
}
