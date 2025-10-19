<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSession;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminSessionController extends Controller
{
    /**
     * Display a listing of admin sessions.
     */
    public function index(Request $request)
    {
        try {
            $query = AdminSession::with('admin:id,name,email');

            // Apply filters
            if ($request->filled('admin_id')) {
                $query->where('admin_id', $request->admin_id);
            }
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('login_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('login_at', '<=', $request->date_to);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('ip_address', 'like', "%{$search}%")
                      ->orWhere('device_name', 'like', "%{$search}%")
                      ->orWhere('platform', 'like', "%{$search}%")
                      ->orWhere('browser', 'like', "%{$search}%")
                      ->orWhereHas('admin', function ($q2) use ($search) {
                          $q2->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            $sessions = $query->orderByDesc('login_at')->paginate(20);
            $stats = $this->getSessionStatistics();
            $admins = Cache::remember('active_admins_list', 300, function () {
                return Admin::select('id', 'name')->orderBy('name')->get();
            });

            return view('admin.sessions.index', compact('sessions', 'stats', 'admins'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load admin sessions: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified admin session.
     */
    public function show($id)
    {
        try {
            $session = AdminSession::with('admin')->findOrFail($id);
            return view('admin.sessions.show', compact('session'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load session details: ' . $e->getMessage());
        }
    }

    /**
     * Terminate a specific admin session.
     */
    public function terminate(Request $request, $id)
    {
        try {
            $session = AdminSession::findOrFail($id);
            
            if (!$session->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session is already inactive.',
                ], 400);
            }

            $session->markInactive();

            // Clear cache
            Cache::forget('admin_session_statistics');

            return response()->json([
                'success' => true,
                'message' => 'Session terminated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to terminate session: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Terminate all sessions for a specific admin.
     */
    public function terminateAllForAdmin(Request $request, $adminId)
    {
        try {
            $count = AdminSession::where('admin_id', $adminId)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'logout_at' => now(),
                ]);

            // Clear cache
            Cache::forget('admin_session_statistics');

            return response()->json([
                'success' => true,
                'message' => "{$count} session(s) terminated successfully.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to terminate sessions: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display admin session statistics.
     */
    public function statistics()
    {
        try {
            $stats = $this->getSessionStatistics();
            $dailyData = $this->getDailySessionData();
            $adminActivity = $this->getAdminActivityData();
            $deviceStats = $this->getDeviceStatistics();
            $browserStats = $this->getBrowserStatistics();

            return view('admin.sessions.statistics', compact(
                'stats',
                'dailyData',
                'adminActivity',
                'deviceStats',
                'browserStats'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load session statistics: ' . $e->getMessage());
        }
    }

    /**
     * Get session statistics.
     */
    private function getSessionStatistics()
    {
        return Cache::remember('admin_session_statistics', 300, function () {
            return AdminSession::getStatistics();
        });
    }

    /**
     * Get daily session data for charts.
     */
    private function getDailySessionData()
    {
        return Cache::remember('daily_admin_session_data', 300, function () {
            $data = AdminSession::select(
                    DB::raw('DATE(login_at) as date'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('COUNT(DISTINCT admin_id) as unique_admins')
                )
                ->where('login_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return $data->map(function ($item) {
                return [
                    'date' => \Carbon\Carbon::parse($item->date)->format('M d'),
                    'count' => $item->count,
                    'unique_admins' => $item->unique_admins,
                ];
            });
        });
    }

    /**
     * Get admin activity data.
     */
    private function getAdminActivityData()
    {
        return Cache::remember('admin_activity_data', 300, function () {
            return AdminSession::select(
                    'admin_id',
                    DB::raw('COUNT(*) as total_sessions'),
                    DB::raw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_sessions'),
                    DB::raw('MAX(login_at) as last_login')
                )
                ->with('admin:id,name')
                ->groupBy('admin_id')
                ->orderByDesc('total_sessions')
                ->limit(10)
                ->get();
        });
    }

    /**
     * Get device statistics.
     */
    private function getDeviceStatistics()
    {
        return Cache::remember('device_statistics', 300, function () {
            return AdminSession::select('device_name', DB::raw('COUNT(*) as count'))
                ->whereNotNull('device_name')
                ->groupBy('device_name')
                ->orderByDesc('count')
                ->get();
        });
    }

    /**
     * Get browser statistics.
     */
    private function getBrowserStatistics()
    {
        return Cache::remember('browser_statistics', 300, function () {
            return AdminSession::select('browser', DB::raw('COUNT(*) as count'))
                ->whereNotNull('browser')
                ->groupBy('browser')
                ->orderByDesc('count')
                ->get();
        });
    }

    /**
     * Get current user's active sessions.
     */
    public function mySessions()
    {
        try {
            $admin = auth()->guard('admin')->user();
            $sessions = AdminSession::where('admin_id', $admin->id)
                ->orderByDesc('login_at')
                ->paginate(10);

            return view('admin.sessions.my-sessions', compact('sessions'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load your sessions: ' . $e->getMessage());
        }
    }

    /**
     * Terminate current user's specific session.
     */
    public function terminateMy($id)
    {
        try {
            $admin = auth()->guard('admin')->user();
            $session = AdminSession::where('admin_id', $admin->id)->findOrFail($id);
            
            if (!$session->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session is already inactive.',
                ], 400);
            }

            $session->markInactive();

            return response()->json([
                'success' => true,
                'message' => 'Session terminated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to terminate session: ' . $e->getMessage(),
            ], 500);
        }
    }
}

