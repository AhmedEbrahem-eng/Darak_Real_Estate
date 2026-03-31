<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that registering as an admin is not allowed.
     */
    public function test_cannot_register_as_admin(): void
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

    /**
     * Test that registering as a user is allowed.
     */
    public function test_can_register_as_user(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'user',
        ]);

        $response->assertStatus(201);
    }
}
