<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Amenity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAmenitySecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_overview_unauthenticated_returns_401()
    {
        $response = $this->getJson('/api/dashboard/home');
        $response->assertStatus(401);
    }

    public function test_dashboard_overview_non_admin_returns_403()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/dashboard/home');
        $response->assertStatus(403);
    }

    public function test_dashboard_overview_admin_returns_200()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/dashboard/home');
        $response->assertStatus(200);
    }

    public function test_amenity_store_unauthenticated_returns_401()
    {
        $response = $this->postJson('/api/amenities', [
            'name' => 'Test Amenity',
            'status' => 'available',
        ]);
        $response->assertStatus(401);
    }

    public function test_amenity_store_non_admin_returns_403()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/amenities', [
            'name' => 'Test Amenity',
            'status' => 'available',
        ]);
        $response->assertStatus(403);
    }

    public function test_amenity_store_admin_returns_201()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/amenities', [
            'name' => 'Test Amenity',
            'status' => 'available',
        ]);
        $response->assertStatus(201);
    }
}
