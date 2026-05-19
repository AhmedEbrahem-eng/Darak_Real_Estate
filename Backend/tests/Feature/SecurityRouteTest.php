<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrative_routes_require_authentication()
    {
        $routes = [
            ['GET', '/api/report-users'],
            ['GET', '/api/report-properties'],
            ['GET', '/api/reason-report'],
            ['GET', '/api/dashboard/home'],
            ['GET', '/api/amenities'],
        ];

        foreach ($routes as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertStatus(401);
        }
    }

    public function test_administrative_routes_require_admin_role()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user, 'sanctum');

        $routes = [
            ['GET', '/api/report-users'],
            ['GET', '/api/report-properties'],
            ['GET', '/api/reason-report'],
            ['GET', '/api/dashboard/home'],
            ['GET', '/api/amenities'],
        ];

        foreach ($routes as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertStatus(403);
        }
    }

    public function test_sensitive_user_routes_require_authentication()
    {
        $routes = [
            ['PUT', '/api/reviews/1'],
            ['DELETE', '/api/reviews/1'],
            ['POST', '/api/tours/1/approve'],
            ['POST', '/api/tours/1/decline'],
            ['DELETE', '/api/properties/1'],
        ];

        foreach ($routes as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertStatus(401);
        }
    }
}
