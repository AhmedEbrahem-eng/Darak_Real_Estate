<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ReportSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_user_reports_index()
    {
        $user = User::factory()->create(['role' => 'renter']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/report-users');

        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_property_reports_index()
    {
        $user = User::factory()->create(['role' => 'landlord']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/report-properties');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_user_reports_index()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/report-users');

        // It might be 200 or 400 (if no reports), but not 403
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_unauthenticated_user_cannot_access_reports()
    {
        $response = $this->getJson('/api/report-users');
        $response->assertStatus(401);

        $response = $this->getJson('/api/report-properties');
        $response->assertStatus(401);
    }
}
