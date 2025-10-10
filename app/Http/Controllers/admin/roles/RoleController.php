<?php

namespace App\Http\Controllers\admin\roles;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-roles--permissions')->only(['index', 'show']);
        $this->middleware('permission:create-roles--permissions')->only(['create', 'store']);
        $this->middleware('permission:write-roles--permissions')->only(['edit', 'update', 'destroy']);
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

        // Get all roles with users
        $rolesWithUsers = Role::with('users')->get();

        // Get all roles ordered by ID in descending order
        $roles = Role::orderBy('id', 'DESC')->get();

        // Return the view with the data
        return view('admin.roles.index', compact('roles', 'permissionsData', 'rolesWithUsers'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $permission = Permission::get();
        return view('admin.roles.create',compact('permission'));
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

        return [
            'msg' => 'Role added successfully'
        ];
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
        $role = Role::find($id);
        $permission = Permission::orderBy('name')->get();
        // Group permissions by resource part (after first '-') for organized UI
        $groupedPermissions = [];
        foreach ($permission as $perm) {
            $parts = explode('-', $perm->name, 2);
            $resource = $parts[1] ?? $parts[0];
            $groupedPermissions[$resource][] = $perm;
        }
        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id",$id)
            ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
            ->all();

        return view('admin.roles.edit',compact('role','permission','rolePermissions','groupedPermissions'));
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
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'permission' => 'required|array|min:1',
            'permission.*' => 'integer|exists:permissions,id',
        ]);

        $role = Role::find($id);
        $role->name = $request->input('name');
        $role->guard_name = 'admin';
        $role->save();

        $permIds = $request->input('permission', []);
        $permissions = Permission::whereIn('id', $permIds)->get();
        $role->syncPermissions($permissions);
        return [
            'msg' => 'Role updated successfully'
        ];
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::table("roles")->where('id',$id)->delete();
        return redirect()->back()
                        ->with('success','Role deleted successfully');
    }
}
