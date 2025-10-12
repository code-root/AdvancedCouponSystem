<?php

namespace App\Http\Controllers;

use App\Models\UserSession;
use App\Events\SessionTerminated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SessionController extends Controller
{
    /**
     * Display all user sessions
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get all sessions for the user
        $sessions = UserSession::where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get();

        // Separate current and other sessions
        $currentSession = $sessions->firstWhere('session_id', session()->getId());
        $otherSessions = $sessions->where('session_id', '!=', session()->getId());

        // Statistics
        $stats = [
            'total_sessions' => $sessions->count(),
            'active_sessions' => $sessions->where('is_active', true)->count(),
            'online_sessions' => $sessions->filter->isOnline()->count(),
            'devices' => $sessions->where('is_active', true)->groupBy('device_type')->map->count(),
            'locations' => $sessions->where('is_active', true)->groupBy('country')->map->count(),
        ];

        return view('dashboard.sessions.index', compact('currentSession', 'otherSessions', 'stats'));
    }

    /**
     * Get sessions data (AJAX)
     */
    public function getData(Request $request)
    {
        $query = UserSession::where('user_id', Auth::id())
            ->orderBy('last_activity', 'desc');

        // Filter by status
        if ($request->status === 'active') {
            $query->active();
        } elseif ($request->status === 'inactive') {
            $query->inactive();
        }

        // Filter by device type
        if ($request->device_type) {
            $query->where('device_type', $request->device_type);
        }

        // Search by IP or location
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('ip_address', 'like', '%' . $request->search . '%')
                  ->orWhere('city', 'like', '%' . $request->search . '%')
                  ->orWhere('country', 'like', '%' . $request->search . '%');
            });
        }

        $sessions = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $sessions,
        ]);
    }

    /**
     * Show session details
     */
    public function show(Request $request, $id)
    {
        $session = UserSession::where('user_id', Auth::id())
            ->findOrFail($id);

        // If AJAX request, return JSON
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'session' => $session,
            ]);
        }

        return view('dashboard.sessions.show', compact('session'));
    }

    /**
     * Logout a specific session
     */
    public function destroy($id)
    {
        $session = UserSession::where('user_id', Auth::id())
            ->findOrFail($id);

        // Check if this is the current session
        if ($session->isCurrent()) {
            // If deleting current session, perform actual logout
            $session->markAsInactive('self_logout');
            
            // Logout user
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
                'is_current' => true,
                'redirect' => route('login'),
            ]);
        }

        // Broadcast event BEFORE terminating (so the user can receive it)
        broadcast(new SessionTerminated($session, 'forced'))->toOthers();
        
        // Mark other session as inactive
        $session->markAsInactive('forced');
        
        // Invalidate the actual session file to force logout
        $this->invalidateSessionFile($session->session_id);

        return response()->json([
            'success' => true,
            'message' => 'Session terminated and user logged out successfully',
            'is_current' => false,
        ]);
    }

    /**
     * Logout all other sessions except current
     */
    public function destroyOthers()
    {
        $currentSessionId = session()->getId();

        // Get all other sessions
        $otherSessions = UserSession::where('user_id', Auth::id())
            ->where('session_id', '!=', $currentSessionId)
            ->where('is_active', true)
            ->get();

        // Invalidate each session
        foreach ($otherSessions as $session) {
            // Broadcast event BEFORE terminating
            broadcast(new SessionTerminated($session, 'forced_by_user'))->toOthers();
            
            // Mark as inactive
            $session->markAsInactive('forced_by_user');
            
            // Try to delete actual session file
            $this->invalidateSessionFile($session->session_id);
        }

        return response()->json([
            'success' => true,
            'message' => 'All other sessions have been terminated',
            'count' => $otherSessions->count(),
        ]);
    }
    
    /**
     * Invalidate session file
     */
    private function invalidateSessionFile(string $sessionId): void
    {
        try {
            // Get session driver
            $driver = config('session.driver');
            
            if ($driver === 'file') {
                $sessionPath = storage_path('framework/sessions/' . $sessionId);
                if (file_exists($sessionPath)) {
                    @unlink($sessionPath);
                }
            } elseif ($driver === 'database') {
                DB::table(config('session.table', 'sessions'))
                    ->where('id', $sessionId)
                    ->delete();
            }
        } catch (\Exception $e) {
            // Log error but don't fail
            Log::warning('Failed to invalidate session file: ' . $e->getMessage());
        }
    }

    /**
     * Cleanup expired sessions
     */
    public function cleanup()
    {
        $expired = UserSession::where('user_id', Auth::id())
            ->expired()
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'logout_reason' => 'expired',
            ]);

        return response()->json([
            'success' => true,
            'message' => "Cleaned up {$expired} expired session(s)",
            'count' => $expired,
        ]);
    }

    /**
     * Update session heartbeat (called every minute from frontend)
     */
    public function heartbeat(Request $request)
    {
        $sessionId = session()->getId();
        
        $session = UserSession::where('session_id', $sessionId)
            ->where('user_id', Auth::id())
            ->first();
        
        if ($session) {
            $session->updateHeartbeat();
            
            return response()->json([
                'success' => true,
                'is_online' => true,
                'last_heartbeat' => $session->last_heartbeat,
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Session not found',
        ], 404);
    }
    
    /**
     * Get session statistics
     */
    public function statistics()
    {
        $userId = Auth::id();

        $stats = [
            // Overall stats
            'total_sessions' => UserSession::where('user_id', $userId)->count(),
            'active_sessions' => UserSession::where('user_id', $userId)->active()->count(),
            'inactive_sessions' => UserSession::where('user_id', $userId)->inactive()->count(),
            
            // By device type
            'by_device' => UserSession::where('user_id', $userId)
                ->active()
                ->selectRaw('device_type, COUNT(*) as count')
                ->groupBy('device_type')
                ->get(),
            
            // By country
            'by_country' => UserSession::where('user_id', $userId)
                ->active()
                ->selectRaw('country, country_code, COUNT(*) as count')
                ->groupBy('country', 'country_code')
                ->orderByDesc('count')
                ->limit(5)
                ->get(),
            
            // By browser
            'by_browser' => UserSession::where('user_id', $userId)
                ->active()
                ->selectRaw('browser, COUNT(*) as count')
                ->groupBy('browser')
                ->orderByDesc('count')
                ->get(),
            
            // Recent logins
            'recent_logins' => UserSession::where('user_id', $userId)
                ->orderBy('login_at', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}

