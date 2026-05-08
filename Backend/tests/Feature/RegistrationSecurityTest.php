<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a user cannot register with the 'admin' role.
     */
    public function test_user_cannot_register_as_admin()
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Bad',
            'last_name' => 'Actor',
            'email' => 'attacker@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'admin',
        ]);

        // If the vulnerability exists, the registration might succeed (201) or fail for other reasons.
        // We want to ensure it fails with a 422 Unprocessable Entity due to validation error.
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role']);
    }

    /**
     * Test that a user can register with a legitimate role.
     */
    public function test_user_can_register_with_legitimate_role()
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Good',
            'last_name' => 'User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01087654321',
            'role' => 'user',
        ]);

        $response->assertStatus(201);
    }
}
