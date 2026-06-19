<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_register_as_admin()
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'admin',
        ];

        $response = $this->postJson('/api/register', $userData);

        // If it's fixed, it should return 422 Unprocessable Entity due to validation failure
        // or it should ignore the role and set it to 'user' or 'landlord'
        // Given the current code, it will return 201 and create an admin.

        if ($response->status() === 201) {
            $user = User::where('email', 'john@example.com')->first();
            $this->assertNotEquals('admin', $user->role, 'A user was able to register as an admin!');
        } else {
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['role']);
        }
    }
}
