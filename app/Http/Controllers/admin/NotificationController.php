<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index(Request $request)
    {
        // Get notifications from database notifications table
        $query = DB::table('notifications')
            ->select('id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at', 'created_at')
            ->where('notifiable_type', 'App\\Models\\Admin')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', 'like', '%' . $request->type . '%');
        }

        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->whereNull('read_at');
            } elseif ($request->status === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        if ($request->filled('priority')) {
            $query->whereJsonContains('data->priority', $request->priority);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $notifications = $query->paginate(25);

        // Get statistics
        $stats = $this->getNotificationStatistics();

        return view('admin.notifications.index', compact('notifications', 'stats'));
    }

    /**
     * Display the specified notification.
     */
    public function show($id)
    {
        $notification = DB::table('notifications')
            ->where('id', $id)
            ->where('notifiable_type', 'App\\Models\\Admin')
            ->first();

        if (!$notification) {
            abort(404);
        }

        // Mark as read if not already read
        if (!$notification->read_at) {
            DB::table('notifications')
                ->where('id', $id)
                ->update(['read_at' => now()]);
        }

        return view('admin.notifications.show', compact('notification'));
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead($id)
    {
        try {
            DB::table('notifications')
                ->where('id', $id)
                ->where('notifiable_type', 'App\\Models\\Admin')
                ->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read'
            ], 500);
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        try {
            DB::table('notifications')
                ->where('notifiable_type', 'App\\Models\\Admin')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read'
            ], 500);
        }
    }

    /**
     * Remove the specified notification.
     */
    public function destroy($id)
    {
        try {
            DB::table('notifications')
                ->where('id', $id)
                ->where('notifiable_type', 'App\\Models\\Admin')
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification'
            ], 500);
        }
    }

    /**
     * Clear all notifications.
     */
    public function clearAll()
    {
        try {
            DB::table('notifications')
                ->where('notifiable_type', 'App\\Models\\Admin')
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'All notifications cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear all notifications'
            ], 500);
        }
    }

    /**
     * Get notification statistics.
     */
    private function getNotificationStatistics(): array
    {
        return Cache::remember('admin_notification_statistics', 300, function () {
            $baseQuery = DB::table('notifications')
                ->where('notifiable_type', 'App\\Models\\Admin');

            return [
                'total_notifications' => $baseQuery->count(),
                'unread_notifications' => $baseQuery->whereNull('read_at')->count(),
                'today_notifications' => $baseQuery->whereDate('created_at', today())->count(),
                'week_notifications' => $baseQuery->where('created_at', '>=', now()->subWeek())->count(),
            ];
        });
    }
}