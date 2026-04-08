<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_cannot_register_as_admin()
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
        $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);
    }

    /** @test */
    public function regular_users_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        // Test some admin-only routes
        $routes = [
            ['GET', '/api/report-users'],
            ['GET', '/api/report-properties'],
            ['POST', '/api/amenities'],
            ['GET', '/api/dashboard/home'],
        ];

        foreach ($routes as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertStatus(403);
        }
    }

    /** @test */
    public function unauthenticated_users_cannot_access_protected_routes()
    {
        $routes = [
            ['GET', '/api/report-users'],
            ['GET', '/api/profile'],
            ['GET', '/api/dashboard/home'],
        ];

        foreach ($routes as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertStatus(401);
        }
    }
}
