<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthSecurityErrorLeakageTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_registration_does_not_leak_exception_message()
    {
        Event::listen('eloquent.creating: App\Models\User', function () {
            throw new \Exception('Database sensitive internal connection error details: secret_db_host:5432');
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
            'message' => 'An unexpected error occurred. Please try again later.',
        ]);
        $response->assertJsonMissing([
            'message' => 'Database sensitive internal connection error details: secret_db_host:5432',
        ]);
    }

    public function test_forgot_password_does_not_leak_exception_message()
    {
        User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
            'phone_number' => '+201012345678',
            'avatar' => 'https://example.com/avatar.png',
        ]);

        Mail::shouldReceive('send')->andThrow(new \Exception('Internal mail server credentials leak error'));

        $response = $this->postJson('/api/forget-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'An unexpected error occurred. Please try again later.',
        ]);
        $response->assertJsonMissing([
            'message' => 'Internal mail server credentials leak error',
        ]);
    }
}
