<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_report_users_admin_endpoints(): void
    {
        $this->getJson('/api/report-users')->assertStatus(401);
        $this->deleteJson('/api/report-users/deleteReport/1')->assertStatus(401);
        $this->deleteJson('/api/report-users/deleteLandlord/1')->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_access_report_properties_admin_endpoints(): void
    {
        $this->getJson('/api/report-properties')->assertStatus(401);
        $this->deleteJson('/api/report-properties/deleteReport/1')->assertStatus(401);
        $this->deleteJson('/api/report-properties/deleteProperty/1')->assertStatus(401);
    }

    public function test_non_admin_user_cannot_access_report_users_admin_endpoints(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $this->getJson('/api/report-users')->assertStatus(403);
        $this->deleteJson('/api/report-users/deleteReport/1')->assertStatus(403);
        $this->deleteJson('/api/report-users/deleteLandlord/1')->assertStatus(403);
    }

    public function test_non_admin_user_cannot_access_report_properties_admin_endpoints(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $this->getJson('/api/report-properties')->assertStatus(403);
        $this->deleteJson('/api/report-properties/deleteReport/1')->assertStatus(403);
        $this->deleteJson('/api/report-properties/deleteProperty/1')->assertStatus(403);
    }

    public function test_admin_user_can_access_report_users_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/report-users');
        // Response will be 400 when empty or 200 with data, but not 401/403
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_admin_user_can_access_report_properties_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/report-properties');
        // Response will be 400 when empty or 200 with data, but not 401/403
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_unauthenticated_user_cannot_store_report(): void
    {
        $this->postJson('/api/report-users', ['reason_id' => 1, 'reported_user_id' => 2])->assertStatus(401);
        $this->postJson('/api/report-properties', ['reason_id' => 1, 'property_id' => 2])->assertStatus(401);
    }
}
