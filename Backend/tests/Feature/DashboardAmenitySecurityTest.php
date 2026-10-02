<?php

namespace Tests\Feature;

use App\Models\Amenity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAmenitySecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_dashboard_overview(): void
    {
        $response = $this->getJson('/api/dashboard/home');

        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_access_dashboard_overview(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/dashboard/home');

        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_dashboard_overview(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/dashboard/home');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_create_amenity(): void
    {
        $response = $this->postJson('/api/amenities', [
            'name' => 'WiFi',
            'status' => 'available',
        ]);

        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_create_amenity(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/amenities', [
            'name' => 'WiFi',
            'status' => 'available',
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_can_view_amenities(): void
    {
        $response = $this->getJson('/api/amenities');

        $response->assertStatus(200);
    }
}
