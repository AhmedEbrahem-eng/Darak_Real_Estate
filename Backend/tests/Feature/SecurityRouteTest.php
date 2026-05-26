<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class SecurityRouteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that administrative routes are protected by admin middleware.
     *
     * @dataProvider adminRoutesProvider
     */
    public function test_admin_routes_are_protected($method, $uri)
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $response = $this->json($method, $uri);

        $response->assertStatus(403);
    }

    public function adminRoutesProvider()
    {
        return [
            ['GET', '/api/report-users'],
            ['DELETE', '/api/report-users/deleteReport/1'],
            ['DELETE', '/api/report-users/deleteLandlord/1'],
            ['GET', '/api/report-properties'],
            ['DELETE', '/api/report-properties/deleteReport/1'],
            ['DELETE', '/api/report-properties/deleteProperty/1'],
            ['POST', '/api/reason-report'],
            ['GET', '/api/reason-report'],
            ['DELETE', '/api/reason-report/1'],
            ['PUT', '/api/reason-report/1'],
            ['GET', '/api/dashboard/home'],
            ['POST', '/api/amenities'],
            ['PUT', '/api/amenities/availability/1'],
            ['DELETE', '/api/amenities/some-slug'],
            ['POST', '/api/property-types'],
            ['PUT', '/api/property-types/1'],
            ['DELETE', '/api/property-types/1'],
        ];
    }

    /**
     * Test that sensitive routes are protected by auth:sanctum middleware.
     *
     * @dataProvider authRoutesProvider
     */
    public function test_sensitive_routes_require_authentication($method, $uri)
    {
        $response = $this->json($method, $uri);

        $response->assertStatus(401);
    }

    public function authRoutesProvider()
    {
        return [
            ['DELETE', '/api/properties/1'],
            ['PUT', '/api/reviews/1'],
            ['DELETE', '/api/reviews/1'],
            ['DELETE', '/api/images/1'],
            ['POST', '/api/tours/1/approve'],
            ['POST', '/api/tours/1/decline'],
        ];
    }
}
