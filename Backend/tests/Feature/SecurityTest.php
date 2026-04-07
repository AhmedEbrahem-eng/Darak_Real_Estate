<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Hash;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_should_not_be_able_to_register_as_admin()
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Bad',
            'last_name' => 'Actor',
            'email' => 'hacker@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'admin',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('users', ['email' => 'hacker@example.com', 'role' => 'admin']);
    }

    /** @test */
    public function admin_routes_should_require_admin_middleware()
    {
        $user = User::create([
            'first_name' => 'Regular',
            'last_name' => 'User',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'phone_number' => '01012345678',
            'role' => 'user',
            'avatar' => 'default.png'
        ]);
        Sanctum::actingAs($user);

        // Test a few administrative routes
        $adminRoutes = [
            ['get', '/api/users'],
            ['get', '/api/report-users'],
            ['get', '/api/report-properties'],
            ['get', '/api/dashboard/home'],
            ['get', '/api/amenities'],
            ['post', '/api/reason-report'],
        ];

        foreach ($adminRoutes as [$method, $route]) {
            $response = $this->json($method, $route);
            $response->assertStatus(403);
        }
    }

    /** @test */
    public function auth_errors_should_not_leak_internal_messages()
    {
        // Mock the User model class to throw an exception during creation
        $this->partialMock(User::class, function ($mock) {
            $mock->shouldReceive('create')->andThrow(new \Exception("Sensitive SQL Error Details"));
        });

        \Illuminate\Support\Facades\Log::shouldReceive('error')->once();

        $response = $this->postJson('/api/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'error@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'user',
        ]);

        $response->assertStatus(500);
        $response->assertJsonMissing(['message' => 'Sensitive SQL Error Details']);
        $response->assertJsonFragment(['message' => 'Internal Server Error']);
    }
}
