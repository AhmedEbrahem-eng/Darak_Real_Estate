<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_requests_to_report_endpoints_are_rejected(): void
    {
        $this->getJson('/api/report-users')
            ->assertStatus(401);

        $this->deleteJson('/api/report-users/deleteReport/1')
            ->assertStatus(401);

        $this->deleteJson('/api/report-users/deleteLandlord/1')
            ->assertStatus(401);

        $this->postJson('/api/report-users', ['landlord_id' => 1, 'content' => 'Test'])
            ->assertStatus(401);

        $this->getJson('/api/report-properties')
            ->assertStatus(401);

        $this->deleteJson('/api/report-properties/deleteReport/1')
            ->assertStatus(401);

        $this->deleteJson('/api/report-properties/deleteProperty/1')
            ->assertStatus(401);

        $this->postJson('/api/report-properties', ['property_id' => 1, 'content' => 'Test'])
            ->assertStatus(401);
    }

    public function test_non_admin_users_cannot_access_administrative_report_endpoints(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $this->getJson('/api/report-users')
            ->assertStatus(403);

        $this->deleteJson('/api/report-users/deleteReport/1')
            ->assertStatus(403);

        $this->deleteJson('/api/report-users/deleteLandlord/1')
            ->assertStatus(403);

        $this->getJson('/api/report-properties')
            ->assertStatus(403);

        $this->deleteJson('/api/report-properties/deleteReport/1')
            ->assertStatus(403);

        $this->deleteJson('/api/report-properties/deleteProperty/1')
            ->assertStatus(403);
    }

    public function test_admin_users_can_access_administrative_report_endpoints(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/report-users');
        $this->assertNotEquals(401, $response->status());
        $this->assertNotEquals(403, $response->status());

        $responseProps = $this->getJson('/api/report-properties');
        $this->assertNotEquals(401, $responseProps->status());
        $this->assertNotEquals(403, $responseProps->status());
    }
}
