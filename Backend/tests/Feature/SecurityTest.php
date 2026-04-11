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
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'admin',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role']);
    }
}
