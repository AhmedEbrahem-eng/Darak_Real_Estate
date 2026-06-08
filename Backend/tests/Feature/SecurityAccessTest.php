<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class SecurityAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_cannot_register_as_an_admin()
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'admin',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422); // Validation error
        $this->assertDatabaseMissing('users', ['email' => 'john@example.com', 'role' => 'admin']);
    }

    /** @test */
    public function administrative_routes_are_protected_by_admin_middleware()
    {
        // Unauthenticated access
        $this->getJson('/api/dashboard/home')->assertStatus(401);
        $this->getJson('/api/report-users')->assertStatus(401);
        $this->getJson('/api/report-properties')->assertStatus(401);

        // Authenticated as regular user
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/dashboard/home')->assertStatus(403);
        $this->getJson('/api/report-users')->assertStatus(403);
        $this->getJson('/api/report-properties')->assertStatus(403);
    }
}
