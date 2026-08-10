<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that user registration with 'admin' role is rejected.
     */
    public function test_registration_with_admin_role_is_rejected(): void
    {
        $payload = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'admin_attacker@example.com',
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
            'phone_number' => '01012345678', // valid Egyptian phone number pattern
            'role' => 'admin',
        ];

        // Ensure the API returns 422 Unprocessable Entity
        // We set 'Accept' header to 'application/json' to get json validation response
        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role']);

        // Assert no user was created with this email
        $this->assertDatabaseMissing('users', [
            'email' => 'admin_attacker@example.com',
        ]);
    }

    /**
     * Test that user registration with a valid role is accepted.
     */
    public function test_registration_with_user_role_is_accepted(): void
    {
        $payload = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane_user@example.com',
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
            'phone_number' => '01012345678',
            'role' => 'user',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'jane_user@example.com',
            'role' => 'user',
        ]);
    }
}
