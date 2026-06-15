<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Mockery;

class SecurityFixTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_cannot_register_with_admin_role()
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'admin_attempt@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'admin',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role']);

        $this->assertDatabaseMissing('users', [
            'email' => 'admin_attempt@example.com',
        ]);
    }

    /** @test */
    public function a_user_can_register_with_user_role()
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'user_attempt@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'user',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'user_attempt@example.com',
            'role' => 'user',
        ]);
    }

    /** @test */
    public function auth_controller_does_not_leak_exception_details_on_register_failure()
    {
        $this->mock(User::class, function ($mock) {
            $mock->shouldReceive('create')->andThrow(new \Exception('Sensitive DB Error Details'));
        });

        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'error_test@example.com',
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

        $response->assertJsonMissing(['message' => 'Sensitive DB Error Details']);
    }

    /** @test */
    public function auth_controller_does_not_leak_exception_details_on_login_failure()
    {
        $this->mock(User::class, function ($mock) {
            $mock->shouldReceive('where')->andThrow(new \Exception('Sensitive Login Error Details'));
        });

        $response = $this->postJson('/api/login', [
            'email' => 'error_test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'An internal server error occurred.',
        ]);

        $response->assertJsonMissing(['message' => 'Sensitive Login Error Details']);
    }
}
