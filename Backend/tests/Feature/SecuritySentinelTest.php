<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecuritySentinelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_cannot_register_as_admin()
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

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role']);
    }

    /** @test */
    public function a_user_can_register_as_user_or_landlord()
    {
        $userData = [
            'first_name' => 'Legit',
            'last_name' => 'User',
            'email' => 'legit@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
        ];

        $userResponse = $this->postJson('/api/register', array_merge($userData, ['role' => 'user', 'email' => 'user@example.com']));
        $userResponse->assertStatus(201);

        $landlordResponse = $this->postJson('/api/register', array_merge($userData, ['role' => 'landlord', 'email' => 'landlord@example.com']));
        $landlordResponse->assertStatus(201);
    }
}
