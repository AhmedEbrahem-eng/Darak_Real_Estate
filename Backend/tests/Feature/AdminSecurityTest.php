<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function report_users_index_is_protected()
    {
        $this->getJson('/api/report-users')->assertStatus(401);
    }

    /** @test */
    public function report_properties_index_is_protected()
    {
        $this->getJson('/api/report-properties')->assertStatus(401);
    }

    /** @test */
    public function reason_report_index_is_protected()
    {
        $this->getJson('/api/reason-report')->assertStatus(401);
    }

    /** @test */
    public function dashboard_home_is_protected()
    {
        $this->getJson('/api/dashboard/home')->assertStatus(401);
    }

    /** @test */
    public function amenities_store_is_protected()
    {
        $this->postJson('/api/amenities', [])->assertStatus(401);
    }

    /** @test */
    public function property_types_store_is_protected()
    {
        $this->postJson('/api/property-types', [])->assertStatus(401);
    }

    /** @test */
    public function property_delete_is_protected()
    {
        // DELETE /api/properties/{id} should be admin only now
        $this->deleteJson('/api/properties/1')->assertStatus(401);
    }

    /** @test */
    public function tours_approve_is_protected()
    {
        $this->postJson('/api/tours/1/approve', [])->assertStatus(401);
    }

    /** @test */
    public function reviews_update_is_protected()
    {
        $this->putJson('/api/reviews/1', [])->assertStatus(401);
    }

    /** @test */
    public function admin_can_access_protected_routes()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        // Verify some routes
        $response = $this->getJson('/api/report-users');
        $this->assertNotEquals(401, $response->getStatusCode());
        $this->assertNotEquals(403, $response->getStatusCode());

        $response = $this->getJson('/api/dashboard/home');
        $this->assertNotEquals(401, $response->getStatusCode());
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function regular_user_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/report-users')->assertStatus(403);
    }
}
