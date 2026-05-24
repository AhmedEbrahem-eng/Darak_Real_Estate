<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_register_as_admin()
    {
        $data = [
            'first_name' => 'Attacker',
            'last_name' => 'User',
            'email' => 'attacker@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01000000000',
            'role' => 'admin',
        ];

        $response = $this->postJson('/api/register', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role']);
        $this->assertDatabaseMissing('users', [
            'email' => 'attacker@example.com',
        ]);
    }

    public function test_user_can_register_with_valid_roles()
    {
        $roles = ['user', 'landlord'];

        foreach ($roles as $role) {
            $data = [
                'first_name' => 'Valid',
                'last_name' => 'User',
                'email' => "valid_{$role}@example.com",
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'phone_number' => '01000000000',
                'role' => $role,
            ];

            $response = $this->postJson('/api/register', $data);

            $response->assertStatus(201);
            $this->assertDatabaseHas('users', [
                'email' => "valid_{$role}@example.com",
                'role' => $role,
            ]);
        }
    }
}
