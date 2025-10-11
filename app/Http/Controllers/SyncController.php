<?php

namespace App\Http\Controllers;

use App\Models\SyncSchedule;
use App\Models\SyncLog;
use App\Models\Network;
use App\Models\NetworkConnection;
use App\Jobs\ProcessNetworkSync;
use App\Services\DataSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SyncController extends Controller
{
    protected $syncService;

    public function __construct(DataSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Display sync schedules index.
     */
    public function schedulesIndex()
    {
        $schedules = SyncSchedule::where('user_id', Auth::id())
            ->with('user')
            ->latest()
            ->paginate(15);

        return view('sync.schedules.index', compact('schedules'));
    }

    /**
     * Show create schedule form.
     */
    public function schedulesCreate()
    {
        $networks = NetworkConnection::where('user_id', Auth::id())
            ->where('is_connected', true)
            ->with('network')
            ->get()
            ->pluck('network');

        return view('sync.schedules.create', compact('networks'));
    }

    /**
     * Store a new schedule.
     */
    public function schedulesStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'network_ids' => 'required|array|min:1',
            'network_ids.*' => 'exists:networks,id',
            'sync_type' => 'required|in:campaigns,coupons,purchases,all',
            'interval_minutes' => 'required|integer|min:10',
            'max_runs_per_day' => 'required|integer|min:1|max:1440',
            'date_range_type' => 'required|in:today,yesterday,last_7_days,last_30_days,current_month,previous_month,custom',
            'custom_date_from' => 'required_if:date_range_type,custom|nullable|date',
            'custom_date_to' => 'required_if:date_range_type,custom|nullable|date|after_or_equal:custom_date_from',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $schedule = SyncSchedule::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'network_ids' => $request->network_ids,
            'sync_type' => $request->sync_type,
            'interval_minutes' => (int) $request->interval_minutes,
            'max_runs_per_day' => (int) $request->max_runs_per_day,
            'date_range_type' => $request->date_range_type,
            'custom_date_from' => $request->custom_date_from,
            'custom_date_to' => $request->custom_date_to,
            'is_active' => $request->boolean('is_active', true),
            'next_run_at' => now()->addMinutes((int) $request->interval_minutes),
        ]);

        return redirect()->route('sync.schedules.index')
            ->with('success', 'Sync schedule created successfully!');
    }

    /**
     * Show edit schedule form.
     */
    public function schedulesEdit($id)
    {
        $schedule = SyncSchedule::where('user_id', Auth::id())->findOrFail($id);
        
        $networks = NetworkConnection::where('user_id', Auth::id())
            ->where('is_connected', true)
            ->with('network')
            ->get()
            ->pluck('network');

        return view('sync.schedules.edit', compact('schedule', 'networks'));
    }

    /**
     * Update a schedule.
     */
    public function schedulesUpdate(Request $request, $id)
    {
        $schedule = SyncSchedule::where('user_id', Auth::id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'network_ids' => 'required|array|min:1',
            'network_ids.*' => 'exists:networks,id',
            'sync_type' => 'required|in:campaigns,coupons,purchases,all',
            'interval_minutes' => 'required|integer|min:10',
            'max_runs_per_day' => 'required|integer|min:1|max:1440',
            'date_range_type' => 'required|in:today,yesterday,last_7_days,last_30_days,current_month,previous_month,custom',
            'custom_date_from' => 'required_if:date_range_type,custom|nullable|date',
            'custom_date_to' => 'required_if:date_range_type,custom|nullable|date|after_or_equal:custom_date_from',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $schedule->update([
            'name' => $request->name,
            'network_ids' => $request->network_ids,
            'sync_type' => $request->sync_type,
            'interval_minutes' => (int) $request->interval_minutes,
            'max_runs_per_day' => (int) $request->max_runs_per_day,
            'date_range_type' => $request->date_range_type,
            'custom_date_from' => $request->custom_date_from,
            'custom_date_to' => $request->custom_date_to,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('sync.schedules.index')
            ->with('success', 'Sync schedule updated successfully!');
    }

    /**
     * Delete a schedule.
     */
    public function schedulesDestroy($id)
    {
        $schedule = SyncSchedule::where('user_id', Auth::id())->findOrFail($id);
        $schedule->delete();

        return redirect()->route('sync.schedules.index')
            ->with('success', 'Sync schedule deleted successfully!');
    }

    /**
     * Toggle schedule active status.
     */
    public function schedulesToggle($id)
    {
        $schedule = SyncSchedule::where('user_id', Auth::id())->findOrFail($id);
        $schedule->update(['is_active' => !$schedule->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $schedule->is_active,
            'message' => 'Schedule ' . ($schedule->is_active ? 'activated' : 'deactivated') . ' successfully',
        ]);
    }

    /**
     * Run a schedule manually.
     */
    public function schedulesRunNow($id)
    {
        $schedule = SyncSchedule::where('user_id', Auth::id())->findOrFail($id);

        if (!$schedule->canRun() && $schedule->runs_today >= $schedule->max_runs_per_day) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule has reached maximum runs for today',
            ], 400);
        }

        $networkIds = $schedule->network_ids ?? [];
        $dispatched = 0;

        foreach ($networkIds as $networkId) {
            $syncLog = SyncLog::create([
                'sync_schedule_id' => $schedule->id,
                'user_id' => Auth::id(),
                'network_id' => $networkId,
                'sync_type' => $schedule->sync_type,
                'status' => 'pending',
            ]);

            ProcessNetworkSync::dispatch($syncLog->id, $schedule->id);
            $dispatched++;
        }

        return response()->json([
            'success' => true,
            'message' => "Dispatched {$dispatched} sync job(s) successfully",
        ]);
    }

    /**
     * Show quick sync page.
     */
    public function quickSyncPage()
    {
        $user = Auth::user();
        $user_id = $user->id;
        $network_ids = NetworkConnection::where('user_id', $user_id)
            ->where('is_connected', true)
            ->pluck('network_id');
        $networks = Network::whereIn('id', $network_ids)->get();

        return view('sync.quick-sync', compact('networks'));
    }

    /**
     * Manual sync - no schedule.
     */
    public function manualSync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'network_ids' => 'required|array|min:1',
            'network_ids.*' => 'exists:networks,id',
            'sync_type' => 'required|in:campaigns,coupons,purchases,all',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $networkIds = $request->network_ids;
        $dispatched = 0;

        foreach ($networkIds as $networkId) {
            // Verify user has connection to this network
            $connection = NetworkConnection::where('user_id', Auth::id())
                ->where('network_id', $networkId)
                ->where('is_connected', true)
                ->first();

            if (!$connection) {
                continue;
            }

            $syncLog = SyncLog::create([
                'sync_schedule_id' => null,
                'user_id' => Auth::id(),
                'network_id' => $networkId,
                'sync_type' => $request->sync_type,
                'status' => 'pending',
                'metadata' => [
                    'date_from' => $request->date_from,
                    'date_to' => $request->date_to,
                    'manual' => true,
                ],
            ]);

            ProcessNetworkSync::dispatch($syncLog->id);
            $dispatched++;
        }

        return response()->json([
            'success' => true,
            'message' => "Dispatched {$dispatched} sync job(s) successfully",
            'dispatched' => $dispatched,
        ]);
    }

    /**
     * Display sync logs.
     */
    public function logsIndex(Request $request)
    {
        $query = SyncLog::where('user_id', Auth::id())
            ->with(['network', 'syncSchedule', 'user']);

        // Apply filters
        if ($request->has('network_id') && $request->network_id) {
            $query->where('network_id', $request->network_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('schedule_id') && $request->schedule_id) {
            $query->where('sync_schedule_id', $request->schedule_id);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->latest()->paginate(20);

        // Get filter options
        $user = Auth::user();
        $user_id = $user->id;
        $network_ids = NetworkConnection::where('user_id', $user_id)->where('is_connected', true)->pluck('network_id');
        $networks = Network::whereIn('id', $network_ids)->get();
       
        $schedules = SyncSchedule::where('user_id', Auth::id())->get();

        // If AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $logs,
            ]);
        }

        return view('sync.logs.index', compact('logs', 'networks', 'schedules'));
    }

    /**
     * Show sync log details.
     */
    public function logsShow($id)
    {
        $log = SyncLog::where('user_id', Auth::id())
            ->with(['network', 'syncSchedule', 'user'])
            ->findOrFail($id);

        return view('sync.logs.show', compact('log'));
    }

    /**
     * Display settings page.
     */
    public function settingsIndex()
    {
        return view('sync.settings.index');
    }
}
