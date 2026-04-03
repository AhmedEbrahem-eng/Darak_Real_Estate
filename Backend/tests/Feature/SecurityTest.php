<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test registration with 'admin' role is forbidden.
     */
    public function test_registration_with_admin_role_is_forbidden(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'admin',
        ]);

        // This should fail validation once fixed.
        // Currently it might pass or fail based on current state.
        // We want it to fail with 422.
        $response->assertStatus(422);
    }

    /**
     * Test administrative routes are protected.
     */
    public function test_administrative_routes_require_admin_role(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $protectedRoutes = [
            ['GET', '/api/report-users'],
            ['GET', '/api/report-properties'],
            ['GET', '/api/dashboard/home'],
            ['GET', '/api/amenities'],
            ['POST', '/api/amenities'],
            ['POST', '/api/reason-report'],
        ];

        foreach ($protectedRoutes as [$method, $url]) {
            $response = $this->actingAs($user)->json($method, $url);
            $response->assertStatus(403);
        }
    }

    /**
     * Test unauthenticated access to sensitive routes is forbidden.
     */
    public function test_unauthenticated_access_is_forbidden(): void
    {
        $protectedRoutes = [
            ['GET', '/api/report-users'],
            ['GET', '/api/report-properties'],
            ['GET', '/api/dashboard/home'],
            ['GET', '/api/amenities'],
            ['POST', '/api/reason-report'],
        ];

        foreach ($protectedRoutes as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertStatus(401);
        }
    }
}
