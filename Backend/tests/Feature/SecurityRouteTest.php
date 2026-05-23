<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityRouteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function administrative_routes_are_protected_from_unauthenticated_access()
    {
        $this->getJson('/api/report-users')->assertStatus(401);
        $this->getJson('/api/report-properties')->assertStatus(401);
        $this->getJson('/api/reason-report')->assertStatus(401);
        $this->getJson('/api/dashboard/home')->assertStatus(401);
        $this->postJson('/api/amenities')->assertStatus(401);
        $this->postJson('/api/property-types')->assertStatus(401);
    }

    /** @test */
    public function administrative_routes_are_protected_from_non_admin_users()
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'user']));

        $this->getJson('/api/report-users')->assertStatus(403);
        $this->getJson('/api/report-properties')->assertStatus(403);
        $this->getJson('/api/reason-report')->assertStatus(403);
        $this->getJson('/api/dashboard/home')->assertStatus(403);
        $this->postJson('/api/amenities')->assertStatus(403);
        $this->postJson('/api/property-types')->assertStatus(403);
    }

    /** @test */
    public function sensitive_actions_require_authentication()
    {
        $this->deleteJson('/api/properties/1')->assertStatus(401);
        $this->putJson('/api/reviews/1')->assertStatus(401);
        $this->deleteJson('/api/reviews/1')->assertStatus(401);
        $this->postJson('/api/tours/1/approve')->assertStatus(401);
        $this->postJson('/api/tours/1/decline')->assertStatus(401);
    }
}
