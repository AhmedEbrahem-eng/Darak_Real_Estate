<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_cannot_register_as_an_admin()
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

        $response->assertStatus(422); // Validation should fail
        $this->assertDatabaseMissing('users', [
            'email' => 'attacker@example.com',
            'role' => 'admin',
        ]);
    }

    /** @test */
    public function a_user_can_register_as_a_regular_user()
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Regular',
            'last_name' => 'User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'user',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'user@example.com',
            'role' => 'user',
        ]);
    }

    /** @test */
    public function a_user_can_register_as_a_landlord()
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Landlord',
            'last_name' => 'User',
            'email' => 'landlord@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'landlord',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'landlord@example.com',
            'role' => 'landlord',
        ]);
    }
}
