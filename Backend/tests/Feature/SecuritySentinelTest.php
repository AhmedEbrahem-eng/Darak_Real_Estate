<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecuritySentinelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that guests cannot access administrative routes.
     */
    public function test_guest_cannot_access_administrative_routes()
    {
        $adminRoutes = [
            ['GET', '/api/report-users'],
            ['DELETE', '/api/report-users/deleteReport/1'],
            ['GET', '/api/report-properties'],
            ['POST', '/api/reason-report'],
            ['GET', '/api/dashboard/home'],
            ['POST', '/api/amenities'],
        ];

        foreach ($adminRoutes as [$method, $uri]) {
            $response = $this->json($method, $uri);
            $response->assertStatus(401);
        }
    }

    /**
     * Test that regular users cannot access administrative routes.
     */
    public function test_regular_user_cannot_access_administrative_routes()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $adminRoutes = [
            ['GET', '/api/report-users'],
            ['DELETE', '/api/report-users/deleteReport/1'],
            ['GET', '/api/report-properties'],
            ['POST', '/api/reason-report'],
            ['GET', '/api/dashboard/home'],
            ['POST', '/api/amenities'],
        ];

        foreach ($adminRoutes as [$method, $uri]) {
            $response = $this->json($method, $uri);
            $response->assertStatus(403);
        }
    }

    /**
     * Test that registration with 'admin' role fails validation.
     */
    public function test_registration_with_admin_role_fails()
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

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role']);
    }

    /**
     * Test that public amenity routes are still accessible.
     */
    public function test_public_amenity_routes_are_accessible()
    {
        $response = $this->getJson('/api/amenities');
        $response->assertStatus(200);

        $response = $this->getJson('/api/amenities/available-amenities');
        $response->assertStatus(200);
    }

    /**
     * Test that public reason-report routes are still accessible.
     */
    public function test_public_reason_report_routes_are_accessible()
    {
        $response = $this->getJson('/api/reason-report/property');
        // It returns 201 in the controller... odd but let's check
        $response->assertStatus(201);

        $response = $this->getJson('/api/reason-report/landlord');
        $response->assertStatus(201);
    }
}
