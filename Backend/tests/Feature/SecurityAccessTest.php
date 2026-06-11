<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a user cannot self-assign the 'admin' role during registration.
     */
    public function test_user_cannot_register_as_admin()
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Attacker',
            'last_name' => 'User',
            'email' => 'attacker@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'admin',
        ]);

        // After fix, we expect 422.
        // Currently it succeeds (201), so we fail the test if it succeeds and grants admin role.
        if ($response->status() === 201) {
            $user = User::where('email', 'attacker@example.com')->first();
            $this->assertNotEquals('admin', $user->role, 'VULNERABILITY: User was able to register as admin!');
        } else {
            $response->assertStatus(422);
        }
    }

    /**
     * Test that administrative routes are protected by auth and admin middleware.
     */
    public function test_administrative_routes_are_protected()
    {
        $adminRoutes = [
            ['GET', '/api/dashboard/home'],
            ['GET', '/api/report-users'],
            ['GET', '/api/report-properties'],
            ['GET', '/api/reason-report'],
            ['GET', '/api/amenities'],
            ['POST', '/api/tours/1/approve'],
            ['POST', '/api/tours/1/decline'],
        ];

        foreach ($adminRoutes as [$method, $url]) {
            $response = $this->json($method, $url);
            // Should be 401 (Unauthenticated) or 403 (Forbidden)
            $this->assertTrue(
                in_array($response->status(), [401, 403]),
                "VULNERABILITY: Route $method $url is not protected! Status: " . $response->status()
            );
        }
    }

    /**
     * Test that a regular user cannot access administrative routes.
     */
    public function test_regular_user_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'user']);

        $adminRoutes = [
            ['GET', '/api/dashboard/home'],
            ['GET', '/api/report-users'],
            ['GET', '/api/report-properties'],
            ['GET', '/api/amenities'],
        ];

        foreach ($adminRoutes as [$method, $url]) {
            $response = $this->actingAs($user)->json($method, $url);
            $response->assertStatus(403);
        }
    }
}
