<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Amenity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityAdminRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_dashboard_overview()
    {
        $response = $this->getJson('/api/dashboard/home');

        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_access_dashboard_overview()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard/home');

        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_dashboard_overview()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/dashboard/home');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_create_amenity()
    {
        $response = $this->postJson('/api/amenities', [
            'name' => 'Swimming Pool',
            'status' => 'available',
        ]);

        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_create_amenity()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/amenities', [
            'name' => 'Swimming Pool',
            'status' => 'available',
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_delete_amenity()
    {
        $response = $this->deleteJson('/api/amenities/swimming-pool');

        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_delete_amenity()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/amenities/swimming-pool');

        $response->assertStatus(403);
    }
}
