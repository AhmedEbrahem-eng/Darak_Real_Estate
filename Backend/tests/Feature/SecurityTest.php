<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function registration_fails_if_role_is_admin()
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
        $response->assertJsonValidationErrors(['role']);
        $this->assertDatabaseMissing('users', ['email' => 'admin_attempt@example.com']);
    }

    /** @test */
    public function guest_cannot_access_admin_routes()
    {
        $adminRoutes = [
            ['GET', '/api/report-users'],
            ['GET', '/api/report-properties'],
            ['GET', '/api/dashboard/home'],
            ['GET', '/api/amenities'],
            ['GET', '/api/property-updates'],
        ];

        foreach ($adminRoutes as [$method, $uri]) {
            $response = $this->json($method, $uri);
            $response->assertStatus(401);
        }
    }

    /** @test */
    public function regular_user_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $adminRoutes = [
            ['GET', '/api/report-users'],
            ['GET', '/api/report-properties'],
            ['GET', '/api/dashboard/home'],
            ['GET', '/api/amenities'],
            ['GET', '/api/property-updates'],
        ];

        foreach ($adminRoutes as [$method, $uri]) {
            $response = $this->json($method, $uri);
            $response->assertStatus(403);
        }
    }

    /** @test */
    public function admin_can_access_admin_routes()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $adminRoutes = [
            ['GET', '/api/report-users'],
            ['GET', '/api/report-properties'],
            ['GET', '/api/dashboard/home'],
            ['GET', '/api/amenities'],
            ['GET', '/api/property-updates'],
        ];

        foreach ($adminRoutes as [$method, $uri]) {
            $response = $this->json($method, $uri);
            // We just care that it's not 401 or 403. It might be 200 or 400 (if no data), etc.
            $this->assertNotContains($response->getStatusCode(), [401, 403], "Failed access to $uri as admin");
        }
    }

    /** @test */
    public function regular_user_can_still_submit_reports()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        // These should NOT return 403
        $response1 = $this->postJson('/api/report-users', []);
        $response2 = $this->postJson('/api/report-properties', []);

        // They will probably return 422 because of empty data, but not 403
        $this->assertNotEquals(403, $response1->getStatusCode());
        $this->assertNotEquals(403, $response2->getStatusCode());
    }
}
