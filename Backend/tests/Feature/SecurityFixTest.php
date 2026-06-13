<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SecurityFixTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a user cannot register with the 'admin' role.
     */
    public function test_user_cannot_register_as_admin()
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

        // This should fail validation once fixed.
        // Currently it will probably pass and return 201.
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role']);
    }

    /**
     * Test that internal server errors do not leak sensitive information.
     */
    public function test_auth_controller_does_not_leak_exception_messages()
    {
        // We need to trigger an exception in the AuthController.
        // One way is to mock the User model if it was injected,
        // but since it's not, we might try to trigger a DB error if possible,
        // or just accept that we will fix the code and see it works.

        // Let's try to register a user with an email that is too long or something
        // that passes validation but fails in DB?
        // Actually, the email validation 'email|max:255' matches DB.

        // Let's just use a mock for the User model to throw an exception.
        $this->mock(User::class, function ($mock) {
            $mock->shouldReceive('create')->andThrow(new \Exception('Sensitive DB Error Details'));
        });

        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'user',
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'An internal server error occurred.',
        ]);
        $response->assertJsonMissing(['Sensitive DB Error Details']);
    }
}
