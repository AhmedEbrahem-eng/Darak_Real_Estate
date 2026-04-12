<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a user cannot register with the 'admin' role.
     */
    public function test_user_cannot_register_as_admin(): void
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

        // This should fail with 422 Unprocessable Entity
        // But currently it will likely succeed with 201 because 'admin' is allowed
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role']);
    }

    /**
     * Test that a user can register with valid roles.
     */
    public function test_user_can_register_with_valid_roles(): void
    {
        $roles = ['user', 'landlord'];

        foreach ($roles as $role) {
            $userData = [
                'first_name' => 'Valid',
                'last_name' => 'User',
                'email' => "valid_{$role}@example.com",
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'phone_number' => '01012345678',
                'role' => $role,
            ];

            $response = $this->postJson('/api/register', $userData);

            $response->assertStatus(201);
            $this->assertDatabaseHas('users', [
                'email' => "valid_{$role}@example.com",
                'role' => $role,
            ]);
        }
    }
}
