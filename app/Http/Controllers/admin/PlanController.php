<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::orderBy('price')->paginate(20);
        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'trial_days' => 'required|integer|min:0',
            'max_networks' => 'required|integer|min:0',
            'daily_sync_limit' => 'nullable|integer|min:0',
            'monthly_sync_limit' => 'nullable|integer|min:0',
            'revenue_cap' => 'nullable|numeric|min:0',
            'orders_cap' => 'nullable|integer|min:0',
            'sync_window_unit' => 'required|in:minute,hour,day',
            'sync_window_size' => 'required|integer|min:1',
            'sync_allowed_from_time' => 'nullable|date_format:H:i',
            'sync_allowed_to_time' => 'nullable|date_format:H:i',
            'is_active' => 'boolean',
        ]);

        Plan::create($validated);
        return redirect()->route('admin.plans.index')->with('success', 'Plan created successfully');
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'trial_days' => 'required|integer|min:0',
            'max_networks' => 'required|integer|min:0',
            'daily_sync_limit' => 'nullable|integer|min:0',
            'monthly_sync_limit' => 'nullable|integer|min:0',
            'revenue_cap' => 'nullable|numeric|min:0',
            'orders_cap' => 'nullable|integer|min:0',
            'sync_window_unit' => 'required|in:minute,hour,day',
            'sync_window_size' => 'required|integer|min:1',
            'sync_allowed_from_time' => 'nullable|date_format:H:i',
            'sync_allowed_to_time' => 'nullable|date_format:H:i',
            'is_active' => 'boolean',
        ]);

        $plan->update($validated);
        return redirect()->route('admin.plans.index')->with('success', 'Plan updated successfully');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        return redirect()->route('admin.plans.index')->with('success', 'Plan deleted successfully');
    }
}


