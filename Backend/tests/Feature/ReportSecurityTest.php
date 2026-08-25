<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_report_user_admin_endpoints(): void
    {
        $this->getJson('/api/report-users')->assertStatus(401);
        $this->deleteJson('/api/report-users/deleteReport/1')->assertStatus(401);
        $this->deleteJson('/api/report-users/deleteLandlord/1')->assertStatus(401);
    }

    public function test_non_admin_user_cannot_access_report_user_admin_endpoints(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/report-users')
            ->assertStatus(403);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/report-users/deleteReport/1')
            ->assertStatus(403);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/report-users/deleteLandlord/1')
            ->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_report_property_admin_endpoints(): void
    {
        $this->getJson('/api/report-properties')->assertStatus(401);
        $this->deleteJson('/api/report-properties/deleteReport/1')->assertStatus(401);
        $this->deleteJson('/api/report-properties/deleteProperty/1')->assertStatus(401);
    }

    public function test_non_admin_user_cannot_access_report_property_admin_endpoints(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/report-properties')
            ->assertStatus(403);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/report-properties/deleteReport/1')
            ->assertStatus(403);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/report-properties/deleteProperty/1')
            ->assertStatus(403);
    }

    public function test_admin_user_is_authorized_to_access_report_admin_endpoints(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Since no reports exist, repository returns empty collection resulting in 400 (or 200/400 business response), but NOT 401/403 security rejection.
        $responseUser = $this->actingAs($admin, 'sanctum')->getJson('/api/report-users');
        $this->assertNotEquals(401, $responseUser->getStatusCode());
        $this->assertNotEquals(403, $responseUser->getStatusCode());

        $responseProperty = $this->actingAs($admin, 'sanctum')->getJson('/api/report-properties');
        $this->assertNotEquals(401, $responseProperty->getStatusCode());
        $this->assertNotEquals(403, $responseProperty->getStatusCode());
    }

    public function test_unauthenticated_user_cannot_store_user_or_property_report(): void
    {
        $this->postJson('/api/report-users', [])->assertStatus(401);
        $this->postJson('/api/report-properties', [])->assertStatus(401);
    }
}
