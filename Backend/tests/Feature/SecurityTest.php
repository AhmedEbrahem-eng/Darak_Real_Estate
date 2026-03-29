<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_does_not_allow_admin_role()
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'admin',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role']);
    }

    public function test_admin_routes_are_protected()
    {
        $adminRoutes = [
            ['GET', '/api/report-users'],
            ['DELETE', '/api/report-users/deleteReport/1'],
            ['DELETE', '/api/report-users/deleteLandlord/1'],
            ['GET', '/api/report-properties'],
            ['DELETE', '/api/report-properties/deleteReport/1'],
            ['DELETE', '/api/report-properties/deleteProperty/1'],
            ['POST', '/api/reason-report'],
            ['GET', '/api/property-types'],
            ['GET', '/api/properties/pending'],
            ['DELETE', '/api/properties/1'],
            ['GET', '/api/property-updates'],
            ['GET', '/api/dashboard/home'],
            ['GET', '/api/amenities'],
        ];

        foreach ($adminRoutes as $route) {
            list($method, $url) = $route;
            $response = $this->json($method, $url);
            // Should be 401 because we are not authenticated
            $response->assertStatus(401);
        }
    }
}
