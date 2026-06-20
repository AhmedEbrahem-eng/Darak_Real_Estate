<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoutesSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_overview_requires_admin_role()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->getJson('/api/dashboard/home');
        $response->assertStatus(403);
    }

    public function test_report_users_index_requires_admin_role()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->getJson('/api/report-users');
        $response->assertStatus(403);
    }

    public function test_report_properties_index_requires_admin_role()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->getJson('/api/report-properties');
        $response->assertStatus(403);
    }

    public function test_reason_report_modification_requires_admin_role()
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->postJson('/api/reason-report', ['reason' => 'test', 'type' => 'landlord'])->assertStatus(403);
        $this->actingAs($user)->putJson('/api/reason-report/1', ['reason' => 'test'])->assertStatus(403);
        $this->actingAs($user)->deleteJson('/api/reason-report/1')->assertStatus(403);
    }

    public function test_amenities_modification_requires_admin_role()
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->postJson('/api/amenities', ['name' => 'test'])->assertStatus(403);
        $this->actingAs($user)->putJson('/api/amenities/test-slug', ['name' => 'test'])->assertStatus(403);
        $this->actingAs($user)->deleteJson('/api/amenities/test-slug')->assertStatus(403);
    }

    public function test_property_types_modification_requires_admin_role()
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->postJson('/api/property-types', ['name' => 'test'])->assertStatus(403);
        $this->actingAs($user)->putJson('/api/property-types/1', ['name' => 'test'])->assertStatus(403);
        $this->actingAs($user)->deleteJson('/api/property-types/1')->assertStatus(403);
    }

    public function test_admin_routes_require_authentication()
    {
        $this->getJson('/api/dashboard/home')->assertStatus(401);
        $this->getJson('/api/report-users')->assertStatus(401);
        $this->getJson('/api/report-properties')->assertStatus(401);
    }

    public function test_report_store_accessible_by_authenticated_user()
    {
        $user = User::factory()->create(['role' => 'user']);

        // Mocking the repository might be needed if we want to avoid database issues,
        // but here we just check if it gets past the 'admin' middleware.
        // It should return 422 (validation error) instead of 403 if it gets past middleware.
        $this->actingAs($user)->postJson('/api/report-users', [])->assertStatus(422);
        $this->actingAs($user)->postJson('/api/report-properties', [])->assertStatus(422);
    }

    public function test_amenities_index_is_public()
    {
        $this->getJson('/api/amenities')->assertStatus(200);
        $this->getJson('/api/amenities/available-amenities')->assertStatus(200);
    }
}
