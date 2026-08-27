<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_admin_report_users_routes(): void
    {
        $response = $this->getJson('/api/report-users');
        $response->assertStatus(401);

        $deleteReportResponse = $this->deleteJson('/api/report-users/deleteReport/1');
        $deleteReportResponse->assertStatus(401);

        $deleteLandlordResponse = $this->deleteJson('/api/report-users/deleteLandlord/1');
        $deleteLandlordResponse->assertStatus(401);
    }

    public function test_non_admin_user_cannot_access_admin_report_users_routes(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/report-users');
        $response->assertStatus(403);

        $deleteReportResponse = $this->actingAs($user, 'sanctum')->deleteJson('/api/report-users/deleteReport/1');
        $deleteReportResponse->assertStatus(403);

        $deleteLandlordResponse = $this->actingAs($user, 'sanctum')->deleteJson('/api/report-users/deleteLandlord/1');
        $deleteLandlordResponse->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_admin_report_properties_routes(): void
    {
        $response = $this->getJson('/api/report-properties');
        $response->assertStatus(401);

        $deleteReportResponse = $this->deleteJson('/api/report-properties/deleteReport/1');
        $deleteReportResponse->assertStatus(401);

        $deletePropertyResponse = $this->deleteJson('/api/report-properties/deleteProperty/1');
        $deletePropertyResponse->assertStatus(401);
    }

    public function test_non_admin_user_cannot_access_admin_report_properties_routes(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/report-properties');
        $response->assertStatus(403);

        $deleteReportResponse = $this->actingAs($user, 'sanctum')->deleteJson('/api/report-properties/deleteReport/1');
        $deleteReportResponse->assertStatus(403);

        $deletePropertyResponse = $this->actingAs($user, 'sanctum')->deleteJson('/api/report-properties/deleteProperty/1');
        $deletePropertyResponse->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_create_or_modify_reason_reports(): void
    {
        $storeResponse = $this->postJson('/api/reason-report', ['type' => 'user', 'reason' => 'Test reason']);
        $storeResponse->assertStatus(401);

        $updateResponse = $this->putJson('/api/reason-report/1', ['type' => 'user', 'reason' => 'Updated reason']);
        $updateResponse->assertStatus(401);

        $deleteResponse = $this->deleteJson('/api/reason-report/1');
        $deleteResponse->assertStatus(401);
    }

    public function test_non_admin_user_cannot_create_or_modify_reason_reports(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $storeResponse = $this->actingAs($user, 'sanctum')->postJson('/api/reason-report', ['type' => 'user', 'reason' => 'Test reason']);
        $storeResponse->assertStatus(403);

        $updateResponse = $this->actingAs($user, 'sanctum')->putJson('/api/reason-report/1', ['type' => 'user', 'reason' => 'Updated reason']);
        $updateResponse->assertStatus(403);

        $deleteResponse = $this->actingAs($user, 'sanctum')->deleteJson('/api/reason-report/1');
        $deleteResponse->assertStatus(403);
    }
}
