<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_cannot_access_user_reports_index(): void
    {
        $response = $this->getJson('/api/report-users');
        $response->assertStatus(401);
    }

    public function test_unauthenticated_users_cannot_delete_user_report(): void
    {
        $response = $this->deleteJson('/api/report-users/deleteReport/1');
        $response->assertStatus(401);
    }

    public function test_unauthenticated_users_cannot_delete_user_from_report(): void
    {
        $response = $this->deleteJson('/api/report-users/deleteLandlord/1');
        $response->assertStatus(401);
    }

    public function test_unauthenticated_users_cannot_access_property_reports_index(): void
    {
        $response = $this->getJson('/api/report-properties');
        $response->assertStatus(401);
    }

    public function test_unauthenticated_users_cannot_delete_property_report(): void
    {
        $response = $this->deleteJson('/api/report-properties/deleteReport/1');
        $response->assertStatus(401);
    }

    public function test_unauthenticated_users_cannot_delete_property_from_report(): void
    {
        $response = $this->deleteJson('/api/report-properties/deleteProperty/1');
        $response->assertStatus(401);
    }

    public function test_non_admin_users_cannot_access_report_endpoints(): void
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

    public function test_admin_users_can_access_report_endpoints(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        // Expect 400 'No reports found' as defined in controllers when report database is empty, rather than 401 or 403 authorization error
        $responseUsers = $this->getJson('/api/report-users');
        $responseUsers->assertStatus(400);

        $responseProperties = $this->getJson('/api/report-properties');
        $responseProperties->assertStatus(400);
    }

    public function test_unauthenticated_users_cannot_submit_reports(): void
    {
        $this->postJson('/api/report-users', [
            'reported_user_id' => 1,
            'reason_id' => 1,
        ])->assertStatus(401);

        $this->postJson('/api/report-properties', [
            'property_id' => 1,
            'reason_id' => 1,
        ])->assertStatus(401);
    }
}
