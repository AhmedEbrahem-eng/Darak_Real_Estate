<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_requests_to_report_admin_routes_are_rejected(): void
    {
        $this->getJson('/api/report-users')->assertStatus(401);
        $this->deleteJson('/api/report-users/deleteReport/1')->assertStatus(401);
        $this->deleteJson('/api/report-users/deleteLandlord/1')->assertStatus(401);

        $this->getJson('/api/report-properties')->assertStatus(401);
        $this->deleteJson('/api/report-properties/deleteReport/1')->assertStatus(401);
        $this->deleteJson('/api/report-properties/deleteProperty/1')->assertStatus(401);
    }

    public function test_non_admin_requests_to_report_admin_routes_are_forbidden(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $this->getJson('/api/report-users')->assertStatus(403);
        $this->deleteJson('/api/report-users/deleteReport/1')->assertStatus(403);
        $this->deleteJson('/api/report-users/deleteLandlord/1')->assertStatus(403);

        $this->getJson('/api/report-properties')->assertStatus(403);
        $this->deleteJson('/api/report-properties/deleteReport/1')->assertStatus(403);
        $this->deleteJson('/api/report-properties/deleteProperty/1')->assertStatus(403);
    }

    public function test_admin_requests_to_report_admin_routes_are_authorized(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $resUsers = $this->getJson('/api/report-users');
        $this->assertNotEquals(401, $resUsers->status());
        $this->assertNotEquals(403, $resUsers->status());

        $resProp = $this->getJson('/api/report-properties');
        $this->assertNotEquals(401, $resProp->status());
        $this->assertNotEquals(403, $resProp->status());
    }
}
