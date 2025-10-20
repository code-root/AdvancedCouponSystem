<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdminPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create permissions
        $this->manageUsersPermission = Permission::create([
            'name' => 'manage-users',
            'guard_name' => 'admin'
        ]);

        $this->manageRolesPermission = Permission::create([
            'name' => 'manage-roles',
            'guard_name' => 'admin'
        ]);

        $this->viewReportsPermission = Permission::create([
            'name' => 'view-reports',
            'guard_name' => 'admin'
        ]);

        // Create roles
        $this->superAdminRole = Role::create([
            'name' => 'super-admin',
            'guard_name' => 'admin'
        ]);

        $this->adminManagerRole = Role::create([
            'name' => 'admin-manager',
            'guard_name' => 'admin'
        ]);

        $this->contentManagerRole = Role::create([
            'name' => 'content-manager',
            'guard_name' => 'admin'
        ]);

        // Assign permissions to roles
        $this->superAdminRole->givePermissionTo([
            $this->manageUsersPermission,
            $this->manageRolesPermission,
            $this->viewReportsPermission,
        ]);

        $this->adminManagerRole->givePermissionTo([
            $this->manageUsersPermission,
            $this->manageRolesPermission,
        ]);

        $this->contentManagerRole->givePermissionTo([
            $this->viewReportsPermission,
        ]);

        // Create test admins
        $this->superAdmin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => Hash::make('password'),
            'active' => true,
        ]);
        $this->superAdmin->assignRole($this->superAdminRole);

        $this->adminManager = Admin::create([
            'name' => 'Admin Manager',
            'email' => 'manager@test.com',
            'password' => Hash::make('password'),
            'active' => true,
        ]);
        $this->adminManager->assignRole($this->adminManagerRole);

        $this->contentManager = Admin::create([
            'name' => 'Content Manager',
            'email' => 'content@test.com',
            'password' => Hash::make('password'),
            'active' => true,
        ]);
        $this->contentManager->assignRole($this->contentManagerRole);
    }

    /** @test */
    public function super_admin_can_access_all_admin_features()
    {
        $this->actingAs($this->superAdmin, 'admin');

        // Test user management access
        $response = $this->get(route('admin.user-management.index'));
        $response->assertStatus(200);

        // Test role management access
        $response = $this->get(route('admin.roles.index'));
        $response->assertStatus(200);

        // Test reports access
        $response = $this->get(route('admin.reports.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_manager_can_access_user_and_role_management()
    {
        $this->actingAs($this->adminManager, 'admin');

        // Test user management access
        $response = $this->get(route('admin.user-management.index'));
        $response->assertStatus(200);

        // Test role management access
        $response = $this->get(route('admin.roles.index'));
        $response->assertStatus(200);

        // Test reports access (should be denied)
        $response = $this->get(route('admin.reports.index'));
        $response->assertStatus(403);
    }

    /** @test */
    public function content_manager_can_only_access_reports()
    {
        $this->actingAs($this->contentManager, 'admin');

        // Test user management access (should be denied)
        $response = $this->get(route('admin.user-management.index'));
        $response->assertStatus(403);

        // Test role management access (should be denied)
        $response = $this->get(route('admin.roles.index'));
        $response->assertStatus(403);

        // Test reports access
        $response = $this->get(route('admin.reports.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_create_role_with_permissions()
    {
        $this->actingAs($this->superAdmin, 'admin');

        $response = $this->post(route('admin.roles.store'), [
            'name' => 'test-role',
            'display_name' => 'Test Role',
            'description' => 'A test role',
            'permissions' => [$this->manageUsersPermission->id],
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success', 'Role created successfully');

        $this->assertDatabaseHas('roles', [
            'name' => 'test-role',
            'guard_name' => 'admin',
        ]);

        $role = Role::where('name', 'test-role')->first();
        $this->assertTrue($role->hasPermissionTo($this->manageUsersPermission));
    }

    /** @test */
    public function admin_cannot_create_role_without_permission()
    {
        $this->actingAs($this->contentManager, 'admin');

        $response = $this->post(route('admin.roles.store'), [
            'name' => 'test-role',
            'display_name' => 'Test Role',
            'description' => 'A test role',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_assign_roles_to_other_admins()
    {
        $this->actingAs($this->superAdmin, 'admin');

        $newAdmin = Admin::create([
            'name' => 'New Admin',
            'email' => 'new@test.com',
            'password' => Hash::make('password'),
            'active' => true,
        ]);

        $response = $this->put(route('admin.admin-users.assign-roles', $newAdmin->id), [
            'roles' => [$this->contentManagerRole->id],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Roles assigned successfully'
        ]);

        $this->assertTrue($newAdmin->hasRole($this->contentManagerRole));
    }

    /** @test */
    public function admin_cannot_assign_super_admin_role()
    {
        $this->actingAs($this->adminManager, 'admin');

        $newAdmin = Admin::create([
            'name' => 'New Admin',
            'email' => 'new@test.com',
            'password' => Hash::make('password'),
            'active' => true,
        ]);

        $response = $this->put(route('admin.admin-users.assign-roles', $newAdmin->id), [
            'roles' => [$this->superAdminRole->id],
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_toggle_user_status()
    {
        $this->actingAs($this->superAdmin, 'admin');

        $user = User::factory()->create();

        $response = $this->post(route('admin.user-management.toggle-status', $user->id), [
            'status' => 'inactive',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'User status updated successfully',
            'new_status' => 'inactive'
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'inactive',
        ]);
    }

    /** @test */
    public function admin_cannot_delete_super_admin_role()
    {
        $this->actingAs($this->superAdmin, 'admin');

        $response = $this->delete(route('admin.roles.destroy', $this->superAdminRole->id));

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Cannot delete super-admin role'
        ]);
    }

    /** @test */
    public function admin_cannot_delete_role_with_assigned_users()
    {
        $this->actingAs($this->superAdmin, 'admin');

        $newAdmin = Admin::create([
            'name' => 'New Admin',
            'email' => 'new@test.com',
            'password' => Hash::make('password'),
            'active' => true,
        ]);
        $newAdmin->assignRole($this->contentManagerRole);

        $response = $this->delete(route('admin.roles.destroy', $this->contentManagerRole->id));

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Cannot delete role that has assigned users'
        ]);
    }

    /** @test */
    public function admin_can_export_roles()
    {
        $this->actingAs($this->superAdmin, 'admin');

        $response = $this->get(route('admin.roles.export'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'name',
                    'display_name',
                    'description',
                    'permissions',
                    'users_count',
                    'created_at',
                ]
            ]
        ]);
    }
}


