<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that registering as an admin is rejected with a validation error (422).
     */
    public function test_registration_as_admin_is_rejected(): void
    {
        $payload = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'admin',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role']);

        $this->assertDatabaseMissing('users', [
            'email' => 'john.admin@example.com',
        ]);
    }

    /**
     * Test that registering as a regular user succeeds (201).
     */
    public function test_registration_as_user_succeeds(): void
    {
        $payload = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'user',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'User registered successfully',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane.user@example.com',
            'role' => 'user',
        ]);
    }

    /**
     * Test that registering as a landlord succeeds (201).
     */
    public function test_registration_as_landlord_succeeds(): void
    {
        $payload = [
            'first_name' => 'Bob',
            'last_name' => 'Doe',
            'email' => 'bob.landlord@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'landlord',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'User registered successfully',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'bob.landlord@example.com',
            'role' => 'landlord',
        ]);
    }
}
