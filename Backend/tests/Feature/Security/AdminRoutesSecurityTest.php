<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AdminRoutesSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed necessary data if needed, but for auth checks, generic hits should suffice
    }

    /** @test */
    public function unauthenticated_user_cannot_access_dashboard_overview()
    {
        $response = $this->getJson('/api/dashboard/home');
        $response->assertStatus(401);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_report_users_index()
    {
        $response = $this->getJson('/api/report-users');
        $response->assertStatus(401);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_report_properties_index()
    {
        $response = $this->getJson('/api/report-properties');
        $response->assertStatus(401);
    }

    /** @test */
    public function regular_user_cannot_access_dashboard_overview()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->getJson('/api/dashboard/home');
        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_access_report_users_index()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->getJson('/api/report-users');
        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_access_reason_report_list()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->getJson('/api/reason-report');
        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_can_access_reason_report_property_reasons()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->getJson('/api/reason-report/property');
        // It might return 201 or 200 depending on implementation, but NOT 401/403
        $this->assertNotEquals(401, $response->getStatusCode());
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function regular_user_cannot_delete_amenity()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->deleteJson('/api/amenities/some-amenity');
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_dashboard_overview()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->getJson('/api/dashboard/home');
        $response->assertStatus(200);
    }

    /** @test */
    public function regular_user_cannot_list_all_users()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->getJson('/api/users');
        $response->assertStatus(403);
    }
}
