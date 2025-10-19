<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;
use Carbon\Carbon;

class NetworkSession extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'network_name',
        'session_key',
        'session_data',
        'expires_at',
    ];

    protected $casts = [
        'session_data' => 'array',
        'expires_at' => 'datetime',
    ];

    /**
     * Store session data
     */
    public static function storeSession(string $networkName, string $sessionKey, array $sessionData, int $expiresInMinutes = 30): self
    {
        // Clean up expired sessions first
        self::cleanupExpiredSessions();
        
        // Delete existing session if exists
        self::where('network_name', $networkName)
            ->where('session_key', $sessionKey)
            ->delete();
        
        // Create new session
        return self::create([
            'network_name' => $networkName,
            'session_key' => $sessionKey,
            'session_data' => $sessionData,
            'expires_at' => Carbon::now()->addMinutes($expiresInMinutes),
        ]);
    }

    /**
     * Get session data
     */
    public static function getSession(string $networkName, string $sessionKey): ?array
    {
        $session = self::where('network_name', $networkName)
            ->where('session_key', $sessionKey)
            ->where('expires_at', '>', Carbon::now())
            ->first();
        
        return $session ? $session->session_data : null;
    }

    /**
     * Check if session is valid
     */
    public static function isSessionValid(string $networkName, string $sessionKey): bool
    {
        return self::where('network_name', $networkName)
            ->where('session_key', $sessionKey)
            ->where('expires_at', '>', Carbon::now())
            ->exists();
    }

    /**
     * Delete session
     */
    public static function deleteSession(string $networkName, string $sessionKey): bool
    {
        return self::where('network_name', $networkName)
            ->where('session_key', $sessionKey)
            ->delete() > 0;
    }

    /**
     * Clean up expired sessions
     */
    public static function cleanupExpiredSessions(): int
    {
        return self::where('expires_at', '<=', Carbon::now())->delete();
    }

    /**
     * Get session info for debugging
     */
    public static function getSessionInfo(string $networkName, string $sessionKey): array
    {
        $session = self::where('network_name', $networkName)
            ->where('session_key', $sessionKey)
            ->first();
        
        if (!$session) {
            return [
                'exists' => false,
                'is_valid' => false,
                'expires_at' => null,
                'session_age' => null,
            ];
        }
        
        $isValid = $session->expires_at > Carbon::now();
        $sessionAge = $isValid ? Carbon::now()->diffInSeconds($session->created_at) : null;
        
        return [
            'exists' => true,
            'is_valid' => $isValid,
            'expires_at' => $session->expires_at,
            'session_age' => $sessionAge,
            'created_at' => $session->created_at,
        ];
    }

    /**
     * Get all active sessions for a network
     */
    public static function getActiveSessions(string $networkName): array
    {
        return self::where('network_name', $networkName)
            ->where('expires_at', '>', Carbon::now())
            ->get()
            ->toArray();
    }
}