<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_cannot_self_register_as_admin()
    {
        $userData = [
            'first_name' => 'Attacker',
            'last_name' => 'User',
            'email' => 'attacker@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'admin',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422); // Validation error
        $response->assertJsonValidationErrors(['role']);
        $this->assertDatabaseMissing('users', [
            'email' => 'attacker@example.com',
            'role' => 'admin',
        ]);
    }

    /** @test */
    public function unauthorized_users_cannot_access_report_users_endpoint()
    {
        // Now protected by auth:sanctum
        $response = $this->getJson('/api/report-users');

        $response->assertStatus(401);
    }

    /** @test */
    public function non_admin_users_cannot_access_report_users_endpoint()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->getJson('/api/report-users');

        $response->assertStatus(403);
    }
}
