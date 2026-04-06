<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_cannot_register_as_an_admin()
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

        $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);
    }

    /** @test */
    public function a_regular_user_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'user']);

        \Laravel\Sanctum\Sanctum::actingAs($user);

        $response = $this->getJson('/api/report-users');
        $response->assertStatus(403);

        $response = $this->getJson('/api/dashboard/home');
        $response->assertStatus(403);

        $response = $this->getJson('/api/amenities');
        $response->assertStatus(403);
    }

    /** @test */
    public function an_unauthenticated_user_cannot_access_admin_routes()
    {
        $response = $this->getJson('/api/report-users');
        $response->assertStatus(401);

        $response = $this->getJson('/api/dashboard/home');
        $response->assertStatus(401);
    }
}
