<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_cannot_access_report_user_admin_endpoints()
    {
        $responseGet = $this->getJson('/api/report-users');
        $responseGet->assertStatus(401);

        $responseDeleteReport = $this->deleteJson('/api/report-users/deleteReport/1');
        $responseDeleteReport->assertStatus(401);

        $responseDeleteLandlord = $this->deleteJson('/api/report-users/deleteLandlord/1');
        $responseDeleteLandlord->assertStatus(401);
    }

    public function test_unauthenticated_users_cannot_access_report_property_admin_endpoints()
    {
        $responseGet = $this->getJson('/api/report-properties');
        $responseGet->assertStatus(401);

        $responseDeleteReport = $this->deleteJson('/api/report-properties/deleteReport/1');
        $responseDeleteReport->assertStatus(401);

        $responseDeleteProperty = $this->deleteJson('/api/report-properties/deleteProperty/1');
        $responseDeleteProperty->assertStatus(401);
    }

    public function test_non_admin_users_cannot_access_administrative_report_endpoints()
    {
        $user = User::factory()->create(['role' => 'user']);

        $responseReportUser = $this->actingAs($user, 'sanctum')->getJson('/api/report-users');
        $responseReportUser->assertStatus(403);

        $responseReportProperty = $this->actingAs($user, 'sanctum')->getJson('/api/report-properties');
        $responseReportProperty->assertStatus(403);

        $responseReasonReport = $this->actingAs($user, 'sanctum')->postJson('/api/reason-report', [
            'type' => 'user',
            'reason' => 'Test reason'
        ]);
        $responseReasonReport->assertStatus(403);
    }

    public function test_admin_users_can_access_administrative_report_endpoints()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $responseReportUser = $this->actingAs($admin, 'sanctum')->getJson('/api/report-users');
        // If there are no reports, index returns 400 with 'No reports found' as defined in controller, but not 401 or 403
        $this->assertNotEquals(401, $responseReportUser->getStatusCode());
        $this->assertNotEquals(403, $responseReportUser->getStatusCode());

        $responseReportProperty = $this->actingAs($admin, 'sanctum')->getJson('/api/report-properties');
        $this->assertNotEquals(401, $responseReportProperty->getStatusCode());
        $this->assertNotEquals(403, $responseReportProperty->getStatusCode());
    }
}
