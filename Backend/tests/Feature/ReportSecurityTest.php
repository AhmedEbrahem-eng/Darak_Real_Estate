<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_report_endpoints(): void
    {
        $this->getJson('/api/report-users')->assertStatus(401);
        $this->deleteJson('/api/report-users/deleteReport/1')->assertStatus(401);
        $this->deleteJson('/api/report-users/deleteLandlord/1')->assertStatus(401);
        $this->postJson('/api/report-users', [])->assertStatus(401);

        $this->getJson('/api/report-properties')->assertStatus(401);
        $this->deleteJson('/api/report-properties/deleteReport/1')->assertStatus(401);
        $this->deleteJson('/api/report-properties/deleteProperty/1')->assertStatus(401);
        $this->postJson('/api/report-properties', [])->assertStatus(401);
    }

    public function test_regular_user_cannot_access_administrative_report_endpoints(): void
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

    public function test_admin_user_can_access_administrative_report_endpoints(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        // Expect non-401 and non-403 response (200 or 400 when empty)
        $responseUser = $this->getJson('/api/report-users');
        $this->assertContains($responseUser->status(), [200, 400]);

        $responseProperty = $this->getJson('/api/report-properties');
        $this->assertContains($responseProperty->status(), [200, 400]);
    }
}
