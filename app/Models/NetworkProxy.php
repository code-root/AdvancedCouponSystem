<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class NetworkProxy extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'host', 'port', 'username', 'password', 'scheme', 'network', 'is_active', 'fail_count', 'last_used_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function toGuzzleProxy(): string
    {
        $auth = '';
        if (!empty($this->username) && !empty($this->password)) {
            $auth = $this->username . ':' . $this->password . '@';
        }
        return $this->scheme . '://' . $auth . $this->host . ':' . $this->port;
    }

    public function toGuzzleProxyArray(): array
    {
        $proxy = $this->toGuzzleProxy();
        return [
            'http' => $proxy,
            'https' => $proxy,
        ];
    }

    public function scopeActiveForNetwork($query, string $network)
    {
        return $query->where('network', $network)->where('is_active', true);
    }

    public function markFailure(int $threshold = 3): void
    {
        $this->fail_count = ($this->fail_count ?? 0) + 1;
        if ($this->fail_count >= $threshold) {
            $this->is_active = false;
        }
        $this->save();
    }
}
