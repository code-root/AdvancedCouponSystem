<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test admin
        $this->admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'active' => true,
        ]);
    }

    /** @test */
    public function admin_can_view_login_page()
    {
        $response = $this->get(route('admin.login'));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.login');
    }

    /** @test */
    public function admin_can_login_with_valid_credentials()
    {
        $response = $this->post(route('admin.post-login'), [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated('admin');
    }

    /** @test */
    public function admin_cannot_login_with_invalid_credentials()
    {
        $response = $this->post(route('admin.post-login'), [
            'email' => 'admin@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    /** @test */
    public function inactive_admin_cannot_login()
    {
        $this->admin->update(['active' => false]);

        $response = $this->post(route('admin.post-login'), [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    /** @test */
    public function admin_can_logout()
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    /** @test */
    public function authenticated_admin_can_access_dashboard()
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    /** @test */
    public function unauthenticated_admin_cannot_access_dashboard()
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    /** @test */
    public function admin_login_requires_email()
    {
        $response = $this->post(route('admin.post-login'), [
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function admin_login_requires_password()
    {
        $response = $this->post(route('admin.post-login'), [
            'email' => 'admin@test.com',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function admin_login_requires_valid_email_format()
    {
        $response = $this->post(route('admin.post-login'), [
            'email' => 'invalid-email',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }
}


