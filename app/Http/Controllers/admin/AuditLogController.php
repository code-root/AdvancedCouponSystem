<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AuditLogController extends Controller
{
    /**
     * Display audit logs with filters.
     */
    public function index(Request $request)
    {
        try {
            $query = AdminAuditLog::with(['admin:id,name,email']);

            // Apply filters
            if ($request->filled('admin_id')) {
                $query->where('admin_id', $request->admin_id);
            }
            if ($request->filled('action')) {
                $query->where('action', $request->action);
            }
            if ($request->filled('model_type')) {
                $query->where('model_type', $request->model_type);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('action', 'like', "%{$search}%")
                      ->orWhereHas('admin', function ($adminQuery) use ($search) {
                          $adminQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            $logs = $query->orderByDesc('created_at')->paginate(50);
            $stats = $this->getAuditLogStatistics();
            $admins = Cache::remember('admins_for_audit_filter', 300, function () {
                return Admin::select('id', 'name', 'email')->orderBy('name')->get();
            });

            return view('admin.audit-logs.index', compact('logs', 'stats', 'admins'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load audit logs: ' . $e->getMessage());
        }
    }

    /**
     * Display audit log details.
     */
    public function show($id)
    {
        try {
            $log = AdminAuditLog::with(['admin', 'model'])->findOrFail($id);
            return view('admin.audit-logs.show', compact('log'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load audit log details: ' . $e->getMessage());
        }
    }

    /**
     * Export audit logs.
     */
    public function export(Request $request)
    {
        try {
            $query = AdminAuditLog::with(['admin']);

            // Apply same filters as index
            if ($request->filled('admin_id')) {
                $query->where('admin_id', $request->admin_id);
            }
            if ($request->filled('action')) {
                $query->where('action', $request->action);
            }
            if ($request->filled('model_type')) {
                $query->where('model_type', $request->model_type);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $logs = $query->orderByDesc('created_at')->get();

            $filename = 'audit_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($logs) {
                $file = fopen('php://output', 'w');
                
                // Headers
                fputcsv($file, [
                    'ID', 'Admin', 'Action', 'Description', 'Model Type', 'Model ID',
                    'IP Address', 'User Agent', 'URL', 'Method', 'Created At'
                ]);

                // Data
                foreach ($logs as $log) {
                    fputcsv($file, [
                        $log->id,
                        $log->admin->name ?? 'N/A',
                        $log->action,
                        $log->description,
                        $log->model_type,
                        $log->model_id,
                        $log->ip_address,
                        $log->user_agent,
                        $log->url,
                        $log->method,
                        $log->created_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to export audit logs: ' . $e->getMessage());
        }
    }

    /**
     * Get audit log statistics.
     */
    private function getAuditLogStatistics()
    {
        return Cache::remember('audit_log_statistics', 300, function () {
            $today = today();
            $thisMonth = now()->month;
            $thisYear = now()->year;

            return [
                'total_logs' => AdminAuditLog::count(),
                'logs_today' => AdminAuditLog::whereDate('created_at', $today)->count(),
                'logs_this_month' => AdminAuditLog::whereMonth('created_at', $thisMonth)
                    ->whereYear('created_at', $thisYear)->count(),
                'unique_admins' => AdminAuditLog::distinct('admin_id')->count(),
                'most_active_admin' => AdminAuditLog::selectRaw('admin_id, COUNT(*) as count')
                    ->with('admin:id,name')
                    ->groupBy('admin_id')
                    ->orderByDesc('count')
                    ->first(),
                'top_actions' => AdminAuditLog::selectRaw('action, COUNT(*) as count')
                    ->groupBy('action')
                    ->orderByDesc('count')
                    ->limit(5)
                    ->get(),
            ];
        });
    }
}