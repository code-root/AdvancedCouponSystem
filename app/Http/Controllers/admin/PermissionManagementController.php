<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class PermissionManagementController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index()
    {
        $permissions = Permission::where('guard_name', 'admin')
            ->withCount('roles')
            ->orderBy('name')
            ->get();

        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * AJAX list permissions.
     */
    public function listAjax(Request $request)
    {
        $query = Permission::where('guard_name', 'admin')->withCount('roles');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by group
        if ($request->filled('group')) {
            $query->where('name', 'like', $request->group . '-%');
        }

        $permissions = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    /**
     * AJAX search permissions.
     */
    public function searchAjax(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2'
        ]);

        $permissions = Permission::where('guard_name', 'admin')
            ->where('name', 'like', "%{$request->q}%")
            ->orWhere('description', 'like', "%{$request->q}%")
            ->limit(10)
            ->get(['id', 'name', 'description']);

        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    /**
     * Store a newly created permission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'description' => 'nullable|string|max:500',
        ]);

        $permission = Permission::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'guard_name' => 'admin'
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Permission created successfully',
                'data' => $permission
            ]);
        }

        return back()->with('success', 'Permission created successfully');
    }

    /**
     * Update the specified permission.
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::where('guard_name', 'admin')->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
            'description' => 'nullable|string|max:500',
        ]);

        $permission->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Permission updated successfully',
                'data' => $permission
            ]);
        }

        return back()->with('success', 'Permission updated successfully');
    }

    /**
     * Remove the specified permission.
     */
    public function destroy($id)
    {
        $permission = Permission::where('guard_name', 'admin')->findOrFail($id);
        
        // Check if permission is assigned to any roles
        if ($permission->roles()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete permission that is assigned to roles'
            ], 403);
        }
        
        $permission->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Permission deleted successfully'
        ]);
    }

    /**
     * Get permission groups.
     */
    public function getGroups()
    {
        $groups = Permission::where('guard_name', 'admin')
            ->selectRaw('SUBSTRING_INDEX(name, "-", 1) as group_name')
            ->groupBy('group_name')
            ->orderBy('group_name')
            ->pluck('group_name');

        return response()->json([
            'success' => true,
            'data' => $groups
        ]);
    }

    /**
     * Bulk create permissions.
     */
    public function bulkCreate(Request $request)
    {
        $validated = $request->validate([
            'permissions' => 'required|array|min:1',
            'permissions.*.name' => 'required|string|max:255',
            'permissions.*.description' => 'nullable|string|max:500',
        ]);

        $created = [];
        $errors = [];

        foreach ($validated['permissions'] as $permissionData) {
            try {
                $permission = Permission::create([
                    'name' => $permissionData['name'],
                    'description' => $permissionData['description'] ?? null,
                    'guard_name' => 'admin'
                ]);
                $created[] = $permission;
            } catch (\Exception $e) {
                $errors[] = [
                    'name' => $permissionData['name'],
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($created) . ' permissions created successfully',
            'created' => $created,
            'errors' => $errors
        ]);
    }
}

