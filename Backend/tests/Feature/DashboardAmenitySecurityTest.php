<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardAmenitySecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_access_to_dashboard_returns_401(): void
    {
        $response = $this->getJson('/api/dashboard/home');

        $response->assertStatus(401);
    }

    public function test_non_admin_access_to_dashboard_returns_403(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard/home');

        $response->assertStatus(403);
    }

    public function test_admin_access_to_dashboard_returns_200(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/dashboard/home');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_amenity_mutations_return_401(): void
    {
        $this->postJson('/api/amenities', ['name' => 'WiFi'])->assertStatus(401);
        $this->putJson('/api/amenities/availability/1', ['status' => 'available'])->assertStatus(401);
        $this->putJson('/api/amenities/wifi', ['name' => 'WiFi Updated'])->assertStatus(401);
        $this->deleteJson('/api/amenities/wifi')->assertStatus(401);
    }

    public function test_non_admin_amenity_mutations_return_403(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $this->postJson('/api/amenities', ['name' => 'WiFi'])->assertStatus(403);
        $this->putJson('/api/amenities/availability/1', ['status' => 'available'])->assertStatus(403);
        $this->putJson('/api/amenities/wifi', ['name' => 'WiFi Updated'])->assertStatus(403);
        $this->deleteJson('/api/amenities/wifi')->assertStatus(403);
    }

    public function test_public_can_access_amenity_index(): void
    {
        $response = $this->getJson('/api/amenities');

        $response->assertStatus(200);
    }
}
