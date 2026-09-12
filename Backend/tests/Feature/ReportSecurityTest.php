<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_cannot_access_report_user_admin_routes(): void
    {
        $response = $this->getJson('/api/report-users');
        $response->assertStatus(401);

        $deleteReportResponse = $this->deleteJson('/api/report-users/deleteReport/1');
        $deleteReportResponse->assertStatus(401);

        $deleteLandlordResponse = $this->deleteJson('/api/report-users/deleteLandlord/1');
        $deleteLandlordResponse->assertStatus(401);
    }

    public function test_non_admin_users_cannot_access_report_user_admin_routes(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/report-users');
        $response->assertStatus(403);

        $deleteReportResponse = $this->actingAs($user, 'sanctum')->deleteJson('/api/report-users/deleteReport/1');
        $deleteReportResponse->assertStatus(403);

        $deleteLandlordResponse = $this->actingAs($user, 'sanctum')->deleteJson('/api/report-users/deleteLandlord/1');
        $deleteLandlordResponse->assertStatus(403);
    }

    public function test_admin_users_can_access_report_user_admin_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/report-users');
        // When database is empty, index returns 400 'No reports found'
        $this->assertTrue(in_array($response->status(), [200, 400]));
    }

    public function test_unauthenticated_users_cannot_access_report_property_admin_routes(): void
    {
        $response = $this->getJson('/api/report-properties');
        $response->assertStatus(401);

        $deleteReportResponse = $this->deleteJson('/api/report-properties/deleteReport/1');
        $deleteReportResponse->assertStatus(401);

        $deletePropertyResponse = $this->deleteJson('/api/report-properties/deleteProperty/1');
        $deletePropertyResponse->assertStatus(401);
    }

    public function test_non_admin_users_cannot_access_report_property_admin_routes(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/report-properties');
        $response->assertStatus(403);

        $deleteReportResponse = $this->actingAs($user, 'sanctum')->deleteJson('/api/report-properties/deleteReport/1');
        $deleteReportResponse->assertStatus(403);

        $deletePropertyResponse = $this->actingAs($user, 'sanctum')->deleteJson('/api/report-properties/deleteProperty/1');
        $deletePropertyResponse->assertStatus(403);
    }

    public function test_admin_users_can_access_report_property_admin_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/report-properties');
        // When database is empty, index returns 400 'No reports found'
        $this->assertTrue(in_array($response->status(), [200, 400]));
    }

    public function test_unauthenticated_users_cannot_submit_reports(): void
    {
        $responseUserReport = $this->postJson('/api/report-users', []);
        $responseUserReport->assertStatus(401);

        $responsePropertyReport = $this->postJson('/api/report-properties', []);
        $responsePropertyReport->assertStatus(401);
    }
}
