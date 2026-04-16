<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_register_as_admin()
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin_test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'admin',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('users', [
            'email' => 'admin_test@example.com',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_admin_routes()
    {
        $adminRoutes = [
            ['get', '/api/report-users'],
            ['get', '/api/report-properties'],
            ['get', '/api/dashboard/home'],
            ['get', '/api/amenities'],
            ['get', '/api/property-updates'],
            ['get', '/api/reason-report'],
        ];

        foreach ($adminRoutes as [$method, $route]) {
            $response = $this->json($method, $route);
            $response->assertStatus(401);
        }
    }

    public function test_regular_user_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $adminRoutes = [
            ['get', '/api/report-users'],
            ['get', '/api/report-properties'],
            ['get', '/api/dashboard/home'],
            ['get', '/api/amenities'],
            ['get', '/api/property-updates'],
            ['get', '/api/reason-report'],
        ];

        foreach ($adminRoutes as [$method, $route]) {
            $response = $this->json($method, $route);
            $response->assertStatus(403);
        }
    }

    public function test_admin_can_access_admin_routes()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $adminRoutes = [
            ['get', '/api/report-users'],
            ['get', '/api/report-properties'],
            ['get', '/api/dashboard/home'],
            ['get', '/api/amenities'],
            ['get', '/api/property-updates'],
            ['get', '/api/reason-report'],
        ];

        foreach ($adminRoutes as [$method, $route]) {
            $response = $this->json($method, $route);
            // Assuming these routes exist and return something other than 401/403
            $this->assertNotEquals(401, $response->status(), "Route $route failed for admin");
            $this->assertNotEquals(403, $response->status(), "Route $route failed for admin");
        }
    }
}
