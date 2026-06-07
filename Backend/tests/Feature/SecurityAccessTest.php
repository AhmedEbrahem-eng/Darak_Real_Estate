<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_cannot_register_as_an_admin()
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

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role']);
    }

    /** @test */
    public function unauthenticated_users_cannot_access_admin_routes()
    {
        $adminRoutes = [
            ['get', '/api/report-users'],
            ['delete', '/api/report-users/deleteReport/1'],
            ['get', '/api/report-properties'],
            ['get', '/api/reason-report'],
            ['get', '/api/dashboard/home'],
            ['get', '/api/amenities'],
        ];

        foreach ($adminRoutes as [$method, $route]) {
            $response = $this->json($method, $route);
            $response->assertStatus(401);
        }
    }

    /** @test */
    public function regular_users_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'user']);

        $adminRoutes = [
            ['get', '/api/report-users'],
            ['get', '/api/report-properties'],
            ['get', '/api/reason-report'],
            ['get', '/api/dashboard/home'],
            ['get', '/api/amenities'],
        ];

        foreach ($adminRoutes as [$method, $route]) {
            $response = $this->actingAs($user)->json($method, $route);
            $response->assertStatus(403);
        }
    }
}
