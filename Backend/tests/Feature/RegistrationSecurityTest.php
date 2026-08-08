<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that user registration rejects 'admin' role with 422 validation error.
     */
    public function test_registration_rejects_admin_role(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'admin',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role']);
    }

    /**
     * Test that user registration accepts 'user' role with 201 status.
     */
    public function test_registration_accepts_user_role(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01112345678',
            'role' => 'user',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'jane.doe@example.com',
            'role' => 'user',
        ]);
    }

    /**
     * Test that user registration accepts 'landlord' role with 201 status.
     */
    public function test_registration_accepts_landlord_role(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Bob',
            'last_name' => 'Smith',
            'email' => 'bob.smith@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01212345678',
            'role' => 'landlord',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'bob.smith@example.com',
            'role' => 'landlord',
        ]);
    }
}
