<?php

namespace App\Http\Controllers\admin\roles;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function __construct()
    {
        // Middleware is now handled in routes or individual methods
        // This constructor can be used for other initialization if needed
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $permissionsData = [];
        $resourceSlugs = [
            'dashboard',
            'users',
            'roles--permissions',
            'permissions',
            'static-pages',
            'properties',
            'articles',
            'leads',
            'statuses',
            'lead-trackings',
            'property-visits',
            'property-inquiries',
            'property-stats',
            'settings',
            'site-settings',
            'developers',
            'amenities',
            'cities',
            'languages',
            'property-types',
            'property-statuses',
        ];

        // Loop through each model (main menu items)
        foreach ($resourceSlugs as $model) {
            $permissions = ['layout', 'view', 'create', 'write', 'delete'];
            $modelPermissions = [];

            // Handle permissions for each menu item
            foreach ($permissions as $permission) {
                $menuSlug = strtolower(str_replace([' ', '&', '?'], ['-', '', ''], $model));
                $permissionName = $permission . '-' . $menuSlug;

                // Search for the permission in the database
                $existingPermission = Permission::where('name', $permissionName)->where('guard_name', 'admin')->first();

                // If the permission exists, add its details to the array
                if ($existingPermission) {
                    $modelPermissions[] = [
                        'name' => $existingPermission->name,
                        'id' => $existingPermission->id,
                        'description' => $permission, // Add description for the permission
                        'isChecked' => true, // Default to checked
                    ];
                } else {
                    // If the permission doesn't exist, add it with a default 'isChecked' value
                    $modelPermissions[] = [
                        'id' => '',
                        'name' => $permissionName,
                        'description' => $permission, // Permission description
                        'isChecked' => false, // Default to unchecked
                    ];
                }
            }
            // Add the permission data for each model
            $permissionsData[$model] = $modelPermissions;
        }

        // Get all roles with users count
        $roles = Role::withCount('users')->orderBy('id', 'DESC')->get();

        // Get statistics
        $stats = [
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'users_with_roles' => User::whereHas('roles')->count(),
            'admin_users' => \App\Models\Admin::count(),
        ];

        // Return the view with the data
        return view('admin.roles.index', compact('roles', 'permissionsData', 'stats'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Get permissions grouped by resource
        $permissions = Permission::orderBy('name')->get();
        $permissionsData = [];
        
        foreach ($permissions as $permission) {
            $parts = explode('-', $permission->name);
            $group = $parts[0] ?? 'other';
            $permissionsData[$group][] = $permission;
        }
        
        return view('admin.roles.create', compact('permissionsData'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Accept both modal and standard create form
        $name = $request->input('modalRoleName', $request->input('name'));

        $this->validate($request, [
            // Validate either modalRoleName or name
            $request->has('modalRoleName') ? 'modalRoleName' : 'name' => 'required|string|max:255|unique:roles,name',
        ]);

        $role = Role::create(['name' => $name, 'guard_name' => 'admin']);

        // Collect permissions from either modal (permissions[id] => on) or form (permission[] => ids or names)
        $toSync = [];

        if ($request->filled('permissions')) {
            // Modal checkboxes: keys are IDs
            $ids = array_keys($request->input('permissions', []));
            $toSync = Permission::whereIn('id', $ids)->get();
        } elseif ($request->filled('permission')) {
            $permInput = $request->input('permission', []);
            // Determine if IDs or names were sent
            $allNumeric = collect($permInput)->every(fn($v) => is_numeric($v));
            if ($allNumeric) {
                $toSync = Permission::whereIn('id', $permInput)->get();
            } else {
                $toSync = Permission::whereIn('name', $permInput)->get();
            }
        }

        if (!empty($toSync)) {
            $role->syncPermissions($toSync);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role = Role::find($id);
        $rolePermissions = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
            ->where("role_has_permissions.role_id",$id)
            ->get();

        return view('admin.roles.show',compact('role','rolePermissions'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $role = Role::findOrFail($id);
        
        // Get permissions grouped by resource
        $permissions = Permission::orderBy('name')->get();
        $permissionsData = [];
        
        foreach ($permissions as $permission) {
            $parts = explode('-', $permission->name);
            $group = $parts[0] ?? 'other';
            $permissionsData[$group][] = $permission;
        }

        return view('admin.roles.edit', compact('role', 'permissionsData'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'],
        ]);

        if (isset($validated['permissions'])) {
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        
        // Prevent deletion of super-admin role
        if ($role->name === 'super-admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete super-admin role'
            ], 403);
        }
        
        // Check if role has users
        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete role that has assigned users'
            ], 403);
        }
        
        $role->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully'
        ]);
    }
}
