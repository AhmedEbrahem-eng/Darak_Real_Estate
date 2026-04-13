<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that users cannot register with an 'admin' role.
     */
    public function test_user_cannot_register_as_admin()
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'admin',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role']);

        $this->assertDatabaseMissing('users', ['email' => 'john@example.com']);
    }

    /**
     * Test that administrative routes are protected from guest access.
     */
    public function test_admin_routes_protected_from_guests()
    {
        $adminRoutes = [
            ['GET', '/api/report-users'],
            ['GET', '/api/report-properties'],
            ['GET', '/api/dashboard/home'],
            ['GET', '/api/amenities'],
            ['GET', '/api/property-updates'],
        ];

        foreach ($adminRoutes as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertStatus(401);
        }
    }

    /**
     * Test that administrative routes are protected from regular user access.
     */
    public function test_admin_routes_protected_from_regular_users()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $adminRoutes = [
            ['GET', '/api/report-users'],
            ['GET', '/api/report-properties'],
            ['GET', '/api/dashboard/home'],
            ['GET', '/api/amenities'],
            ['GET', '/api/property-updates'],
        ];

        foreach ($adminRoutes as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertStatus(403);
        }
    }

    /**
     * Test that administrative routes are NOT 401 or 403 for admins.
     */
    public function test_admin_routes_accessible_by_admins()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $adminRoutes = [
            ['GET', '/api/report-users'],
            ['GET', '/api/report-properties'],
            ['GET', '/api/dashboard/home'],
            ['GET', '/api/amenities'],
            ['GET', '/api/property-updates'],
        ];

        foreach ($adminRoutes as [$method, $url]) {
            $response = $this->json($method, $url);
            $this->assertNotEquals(401, $response->status(), "Route $url returned 401 for admin");
            $this->assertNotEquals(403, $response->status(), "Route $url returned 403 for admin");
        }
    }
}
