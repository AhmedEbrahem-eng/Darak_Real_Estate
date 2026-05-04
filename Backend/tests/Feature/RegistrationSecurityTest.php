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
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'admin_attempt@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'admin',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('users', ['email' => 'admin_attempt@example.com', 'role' => 'admin']);
    }
}
