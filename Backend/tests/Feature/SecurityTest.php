<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function administrative_routes_should_be_protected_by_auth_middleware()
    {
        $adminRoutes = [
            ['GET', '/api/report-users'],
            ['DELETE', '/api/report-users/deleteReport/1'],
            ['DELETE', '/api/report-users/deleteLandlord/1'],
            ['GET', '/api/report-properties'],
            ['DELETE', '/api/report-properties/deleteReport/1'],
            ['DELETE', '/api/report-properties/deleteProperty/1'],
            ['POST', '/api/reason-report'],
            ['GET', '/api/reason-report'],
            ['GET', '/api/reason-report/property'],
            ['GET', '/api/reason-report/landlord'],
            ['DELETE', '/api/reason-report/1'],
            ['PUT', '/api/reason-report/1'],
            ['GET', '/api/dashboard/home'],
            ['POST', '/api/amenities'],
            ['PUT', '/api/amenities/availability/1'],
            ['PUT', '/api/amenities/some-slug'],
            ['DELETE', '/api/amenities/some-slug'],
            ['POST', '/api/property-types'],
            ['PUT', '/api/property-types/some-slug'],
            ['DELETE', '/api/property-types/some-slug'],
        ];

        foreach ($adminRoutes as [$method, $route]) {
            $response = $this->json($method, $route);
            // Since we applied auth:sanctum, if no token is provided it should return 401.
            $this->assertEquals(401, $response->getStatusCode(), "Route $method $route is not protected by auth middleware");
        }
    }

    /** @test */
    public function administrative_routes_should_be_protected_by_admin_middleware()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $adminRoutes = [
            ['GET', '/api/report-users'],
            ['DELETE', '/api/report-users/deleteReport/1'],
            ['DELETE', '/api/report-users/deleteLandlord/1'],
            ['GET', '/api/report-properties'],
            ['DELETE', '/api/report-properties/deleteReport/1'],
            ['DELETE', '/api/report-properties/deleteProperty/1'],
            ['POST', '/api/reason-report'],
            ['GET', '/api/reason-report'],
            ['GET', '/api/reason-report/property'],
            ['GET', '/api/reason-report/landlord'],
            ['DELETE', '/api/reason-report/1'],
            ['PUT', '/api/reason-report/1'],
            ['GET', '/api/dashboard/home'],
            ['POST', '/api/amenities'],
            ['PUT', '/api/amenities/availability/1'],
            ['PUT', '/api/amenities/some-slug'],
            ['DELETE', '/api/amenities/some-slug'],
            ['POST', '/api/property-types'],
            ['PUT', '/api/property-types/some-slug'],
            ['DELETE', '/api/property-types/some-slug'],
        ];

        foreach ($adminRoutes as [$method, $route]) {
            $response = $this->json($method, $route);
            // Since we are authenticated as a regular user, it should return 403.
            $this->assertEquals(403, $response->getStatusCode(), "Route $method $route is not protected by admin middleware");
        }
    }

    /** @test */
    public function public_routes_should_remain_accessible()
    {
        $publicRoutes = [
            ['GET', '/api/amenities/available-amenities'],
            ['GET', '/api/amenities/some-slug'],
            ['GET', '/api/property-types'],
            ['GET', '/api/property-types/some-slug'],
        ];

        foreach ($publicRoutes as [$method, $route]) {
            $response = $this->json($method, $route);
            // Should not be 401 or 403. Might be 404 if slug doesn't exist, which is fine for this test.
            $this->assertNotEquals(401, $response->getStatusCode(), "Route $method $route should be public");
            $this->assertNotEquals(403, $response->getStatusCode(), "Route $method $route should be public");
        }
    }
}
