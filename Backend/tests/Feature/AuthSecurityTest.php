<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_failure_returns_generic_error_message()
    {
        $this->partialMock(User::class, function ($mock) {
            $mock->shouldReceive('create')->andThrow(new \Exception('Sensitive Database Error Details'));
        });

        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'phone_number' => '01012345678',
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'An internal server error occurred.',
        ]);
        $response->assertJsonMissing(['message' => 'Sensitive Database Error Details']);
    }

    public function test_login_failure_returns_generic_error_message()
    {
        $this->partialMock(User::class, function ($mock) {
            $mock->shouldReceive('where')->andThrow(new \Exception('Sensitive Login Error Details'));
        });

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'An internal server error occurred.',
        ]);
        $response->assertJsonMissing(['message' => 'Sensitive Login Error Details']);
    }
}
