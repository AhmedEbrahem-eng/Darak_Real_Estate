<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthSecurityErrorLeakageTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_exception_does_not_leak_details()
    {
        // Mock Hash::make to throw an exception
        Hash::shouldReceive('make')
            ->once()
            ->andThrow(new \Exception('Database connection failed or sensitive schema error!'));

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
            'message' => 'An error occurred.',
        ]);

        $response->assertJsonMissing(['message' => 'Database connection failed or sensitive schema error!']);
    }

    public function test_forgot_password_exception_does_not_leak_details()
    {
        // Seed user with required avatar
        $user = User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'phone_number' => '+21012345678',
            'avatar' => 'https://upload.wikimedia.org/wikipedia/commons/2/2c/Default_pfp.svg',
        ]);

        // Mock Mail to throw an exception
        Mail::shouldReceive('send')
            ->once()
            ->andThrow(new \Exception('SMTP connection timed out!'));

        $response = $this->postJson('/api/forget-password', [
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'An error occurred.',
        ]);

        $response->assertJsonMissing(['message' => 'SMTP connection timed out!']);
    }

    public function test_reset_password_exception_does_not_leak_details()
    {
        // Mock Hash::make to throw an exception
        Hash::shouldReceive('make')
            ->andThrow(new \Exception('Password hashing service failed!'));

        // Seed a token in DB first
        \DB::table('password_reset_tokens')->insert([
            'email' => 'john@example.com',
            'token' => 'valid-reset-token',
            'created_at' => now(),
        ]);

        // Seed user with required avatar
        User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'oldpassword',
            'role' => 'user',
            'phone_number' => '+21012345678',
            'avatar' => 'https://upload.wikimedia.org/wikipedia/commons/2/2c/Default_pfp.svg',
        ]);

        $response = $this->postJson('/api/reset-password', [
            'token' => 'valid-reset-token',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'An error occurred.',
        ]);

        $response->assertJsonMissing(['message' => 'Password hashing service failed!']);
    }

    public function test_social_login_exception_does_not_leak_details()
    {
        // Mock Socialite to throw an exception
        Socialite::shouldReceive('driver')
            ->once()
            ->andThrow(new \Exception('Social provider API limit reached!'));

        $response = $this->getJson('/api/auth/google/callback');

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'An error occurred.',
        ]);

        $response->assertJsonMissing(['message' => 'Social provider API limit reached!']);
    }
}
