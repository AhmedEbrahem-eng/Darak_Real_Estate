<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_overview_is_protected_from_guest()
    {
        $response = $this->getJson('/api/dashboard/home');
        $response->assertStatus(401);
    }

    public function test_dashboard_overview_is_protected_from_non_admin()
    {
        $user = User::factory()->create(['role' => 'renter']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard/home');
        $response->assertStatus(403);
    }

    public function test_reason_report_is_protected_from_guest()
    {
        $response = $this->getJson('/api/reason-report');
        $response->assertStatus(401);
    }

    public function test_reason_report_is_protected_from_non_admin()
    {
        $user = User::factory()->create(['role' => 'renter']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/reason-report');
        $response->assertStatus(403);
    }

    public function test_report_users_is_protected_from_guest()
    {
        $response = $this->getJson('/api/report-users');
        $response->assertStatus(401);
    }

    public function test_report_users_is_protected_from_non_admin()
    {
        $user = User::factory()->create(['role' => 'renter']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/report-users');
        $response->assertStatus(403);
    }

    public function test_report_properties_is_protected_from_guest()
    {
        $response = $this->getJson('/api/report-properties');
        $response->assertStatus(401);
    }

    public function test_report_properties_is_protected_from_non_admin()
    {
        $user = User::factory()->create(['role' => 'renter']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/report-properties');
        $response->assertStatus(403);
    }

    public function test_tour_approve_is_protected()
    {
        $response = $this->postJson('/api/tours/1/approve');
        $response->assertStatus(401);
    }

    public function test_tour_decline_is_protected()
    {
        $response = $this->postJson('/api/tours/1/decline');
        $response->assertStatus(401);
    }

    public function test_amenities_store_is_protected_from_guest()
    {
        $response = $this->postJson('/api/amenities');
        $response->assertStatus(401);
    }

    public function test_amenities_store_is_protected_from_non_admin()
    {
        $user = User::factory()->create(['role' => 'renter']);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/amenities');
        $response->assertStatus(403);
    }
}
