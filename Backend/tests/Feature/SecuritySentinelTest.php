<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class SecuritySentinelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that guests cannot access administrative routes.
     */
    public function test_guests_cannot_access_admin_routes()
    {
        $adminRoutes = [
            'get' => [
                '/api/report-users',
                '/api/report-properties',
                '/api/reason-report',
                '/api/dashboard/home',
            ],
            'post' => [
                '/api/reason-report',
                '/api/amenities',
                '/api/property-types',
            ],
        ];

        foreach ($adminRoutes['get'] as $route) {
            $response = $this->getJson($route);
            $response->assertStatus(401);
        }

        foreach ($adminRoutes['post'] as $route) {
            $response = $this->postJson($route);
            $response->assertStatus(401);
        }
    }

    /**
     * Test that regular users cannot access administrative routes.
     */
    public function test_regular_users_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $adminRoutes = [
            'get' => [
                '/api/report-users',
                '/api/report-properties',
                '/api/reason-report',
                '/api/dashboard/home',
            ],
            'post' => [
                '/api/reason-report',
                '/api/amenities',
                '/api/property-types',
            ],
        ];

        foreach ($adminRoutes['get'] as $route) {
            $response = $this->getJson($route);
            $response->assertStatus(403);
        }

        foreach ($adminRoutes['post'] as $route) {
            $response = $this->postJson($route);
            $response->assertStatus(403);
        }
    }

    /**
     * Test that public reason-report routes are still accessible.
     */
    public function test_public_reason_report_routes_are_accessible()
    {
        $response = $this->getJson('/api/reason-report/property');
        // It should not be 401 or 403. Depending on DB state, might be 200 or 201 as per controller.
        $this->assertNotContains($response->getStatusCode(), [401, 403]);

        $response = $this->getJson('/api/reason-report/landlord');
        $this->assertNotContains($response->getStatusCode(), [401, 403]);
    }
}
