<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdminAjaxTest extends TestCase
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
    public function admin_can_update_general_settings_via_ajax()
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson(route('admin.settings.general.save.ajax'), [
            'site_name' => 'Test Site',
            'site_url' => 'https://test.com',
            'timezone' => 'UTC',
            'locale' => 'en',
            'maintenance_mode' => false,
            'registration_enabled' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'General settings updated successfully'
        ]);

        $this->assertDatabaseHas('site_settings', [
            'key' => 'site_name',
            'value' => 'Test Site',
        ]);
    }

    /** @test */
    public function admin_can_update_smtp_settings_via_ajax()
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson(route('admin.settings.smtp.save.ajax'), [
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.test.com',
            'mail_port' => 587,
            'mail_username' => 'test@test.com',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@test.com',
            'mail_from_name' => 'Test Site',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'SMTP settings updated successfully'
        ]);
    }

    /** @test */
    public function admin_can_test_email_via_ajax()
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson(route('admin.settings.smtp.test-email'), [
            'test_email' => 'test@example.com',
            'test_subject' => 'Test Email',
            'test_message' => 'This is a test email message.',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Test email sent successfully!'
        ]);
    }

    /** @test */
    public function admin_can_get_user_stats_via_ajax()
    {
        $this->actingAs($this->admin, 'admin');

        // Create test users
        User::factory()->count(5)->create();

        $response = $this->getJson(route('admin.user-management.stats', 1));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'total_campaigns',
                'total_purchases',
                'total_revenue',
                'avg_order_value',
                'conversion_rate',
            ]
        ]);
    }

    /** @test */
    public function admin_can_bulk_delete_users_via_ajax()
    {
        $this->actingAs($this->admin, 'admin');

        // Create test users
        $users = User::factory()->count(3)->create();

        $response = $this->postJson(route('admin.ajax.bulk.delete'), [
            'ids' => $users->pluck('id')->toArray(),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('users', [
            'id' => $users->first()->id,
        ]);
    }

    /** @test */
    public function admin_can_bulk_export_users_via_ajax()
    {
        $this->actingAs($this->admin, 'admin');

        // Create test users
        $users = User::factory()->count(3)->create();

        $response = $this->postJson(route('admin.ajax.bulk.export'), [
            'ids' => $users->pluck('id')->toArray(),
            'format' => 'csv',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'ID',
                    'Name',
                    'Email',
                    'Status',
                    'Created At',
                ]
            ]
        ]);
    }

    /** @test */
    public function admin_can_bulk_update_user_status_via_ajax()
    {
        $this->actingAs($this->admin, 'admin');

        // Create test users
        $users = User::factory()->count(3)->create();

        $response = $this->postJson(route('admin.ajax.bulk.status-update'), [
            'ids' => $users->pluck('id')->toArray(),
            'status' => 'inactive',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        foreach ($users as $user) {
            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'status' => 'inactive',
            ]);
        }
    }

    /** @test */
    public function ajax_requests_require_authentication()
    {
        $response = $this->postJson(route('admin.settings.general.save.ajax'), [
            'site_name' => 'Test Site',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function ajax_requests_require_valid_csrf_token()
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson(route('admin.settings.general.save.ajax'), [
            'site_name' => 'Test Site',
        ], [
            'X-CSRF-TOKEN' => 'invalid-token',
        ]);

        $response->assertStatus(419);
    }

    /** @test */
    public function ajax_requests_return_json_response()
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson(route('admin.settings.general.save.ajax'), [
            'site_name' => 'Test Site',
        ]);

        $response->assertHeader('Content-Type', 'application/json');
    }

    /** @test */
    public function ajax_requests_handle_validation_errors()
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson(route('admin.settings.general.save.ajax'), [
            'site_name' => '', // Required field
            'site_url' => 'invalid-url', // Invalid URL
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['site_name', 'site_url']);
    }

    /** @test */
    public function ajax_requests_respect_rate_limiting()
    {
        $this->actingAs($this->admin, 'admin');

        // Make multiple requests to trigger rate limiting
        for ($i = 0; $i < 105; $i++) {
            $response = $this->postJson(route('admin.ajax.bulk.delete'), [
                'ids' => [],
            ]);
        }

        $response->assertStatus(429);
        $response->assertJson([
            'success' => false,
            'message' => 'Too many requests. Please try again in',
        ]);
    }
}

