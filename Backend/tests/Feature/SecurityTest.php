<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a user cannot register as an admin.
     */
    public function test_user_cannot_register_as_admin(): void
    {
        $payload = [
            'first_name' => 'Attacker',
            'last_name' => 'User',
            'email' => 'attacker@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'admin',
        ];

        $response = $this->postJson('/api/register', $payload);

        // We expect this to fail validation (422) once fixed.
        // Currently it returns 201 (Created).
        $response->assertStatus(422);

        $this->assertDatabaseMissing('users', [
            'email' => 'attacker@example.com',
            'role' => 'admin',
        ]);
    }

    /**
     * Test that administrative routes are protected.
     */
    public function test_administrative_routes_are_protected(): void
    {
        $routes = [
            ['GET', '/api/report-users'],
            ['GET', '/api/report-properties'],
            ['GET', '/api/dashboard/home'],
            ['GET', '/api/amenities'],
            ['GET', '/api/property-updates'],
        ];

        foreach ($routes as [$method, $url]) {
            $response = $this->json($method, $url);
            // We expect 401 (Unauthenticated) or 403 (Forbidden).
            $this->assertTrue(
                in_array($response->status(), [401, 403]),
                "Route $url is not protected. Status: " . $response->status()
            );
        }
    }
}
