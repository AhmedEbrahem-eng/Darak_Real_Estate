<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a user cannot register with the 'admin' role.
     */
    public function test_cannot_register_as_admin()
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Attacker',
            'last_name' => 'User',
            'email' => 'attacker@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'admin',
        ]);

        // If it currently succeeds, it will return 201.
        // We want it to fail with 422 Unprocessable Entity.
        $response->assertStatus(422);
        $this->assertDatabaseMissing('users', ['email' => 'attacker@example.com']);
    }

    /**
     * Test that registration fails for invalid phone number.
     */
    public function test_registration_fails_invalid_phone()
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'User',
            'last_name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '123', // Invalid format
            'role' => 'user',
        ]);

        $response->assertStatus(422);
    }
}
