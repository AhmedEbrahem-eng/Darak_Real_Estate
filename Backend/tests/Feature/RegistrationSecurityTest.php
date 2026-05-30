<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a user cannot register with an admin role.
     */
    public function test_user_cannot_register_as_admin(): void
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

        // This should fail validation once fixed.
        // Currently it might return 201 because 'admin' is in the whitelist.
        $response->assertStatus(422);
        $this->assertDatabaseMissing('users', [
            'email' => 'john@example.com',
            'role' => 'admin',
        ]);
    }

    /**
     * Test that a user can register with a valid role.
     */
    public function test_user_can_register_with_valid_role(): void
    {
        $userData = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01112345678',
            'role' => 'user',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'role' => 'user',
        ]);
    }
}
