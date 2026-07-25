<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that user registration with the role 'admin' is rejected.
     */
    public function test_registration_rejects_admin_role(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'admin',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role']);
    }

    /**
     * Test that user registration with the role 'user' succeeds.
     */
    public function test_registration_accepts_user_role(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'user',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'john.user@example.com',
            'role' => 'user',
        ]);
    }

    /**
     * Test that user registration with the role 'landlord' succeeds.
     */
    public function test_registration_accepts_landlord_role(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.landlord@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01212345678',
            'role' => 'landlord',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'jane.landlord@example.com',
            'role' => 'landlord',
        ]);
    }
}
