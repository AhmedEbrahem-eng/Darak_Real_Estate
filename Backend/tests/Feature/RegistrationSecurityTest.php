<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_cannot_register_as_an_admin()
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

        // It should either fail validation or register as a default role,
        // but here it currently succeeds and grants admin role.
        // We want it to fail validation if 'admin' is provided.
        $response->assertStatus(422);
        $this->assertDatabaseMissing('users', ['email' => 'john@example.com', 'role' => 'admin']);
    }
}
