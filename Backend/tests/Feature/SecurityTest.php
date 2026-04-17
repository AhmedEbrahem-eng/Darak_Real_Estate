<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_should_not_allow_registering_as_admin()
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
        $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);
    }

    /** @test */
    public function it_should_allow_registering_as_user()
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
        $this->assertDatabaseHas('users', ['email' => 'user@example.com', 'role' => 'user']);
    }
}
