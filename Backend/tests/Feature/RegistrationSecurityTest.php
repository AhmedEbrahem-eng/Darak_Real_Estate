<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_register_as_admin(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01000000000',
            'role' => 'admin',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role']);
    }

    public function test_can_register_as_user(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01000000001',
            'role' => 'user',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'User registered successfully',
        ]);
    }

    public function test_can_register_as_landlord(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Bob',
            'last_name' => 'Smith',
            'email' => 'bob.smith@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01222222222',
            'role' => 'landlord',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'User registered successfully',
        ]);
    }
}
