<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_prevents_admin_role()
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

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role']);
    }

    public function test_admin_routes_protected()
    {
        $adminRoutes = [
            ['GET', '/api/report-users'],
            ['DELETE', '/api/report-users/deleteReport/1'],
            ['GET', '/api/report-properties'],
            ['POST', '/api/reason-report'],
            ['GET', '/api/dashboard/home'],
            ['POST', '/api/amenities'],
            ['PUT', '/api/reviews/1'],
        ];

        foreach ($adminRoutes as [$method, $url]) {
            $response = $this->json($method, $url);
            // Should be 401 Unauthorized if not logged in
            $response->assertStatus(401);
        }
    }

    public function test_non_admin_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user, 'sanctum');

        $adminRoutes = [
            ['GET', '/api/report-users'],
            ['GET', '/api/dashboard/home'],
        ];

        foreach ($adminRoutes as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertStatus(403);
        }
    }
}
