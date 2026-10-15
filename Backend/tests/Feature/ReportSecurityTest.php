<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_report_endpoints()
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

    public function test_non_admin_user_cannot_access_admin_report_endpoints()
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

    public function test_admin_user_can_access_admin_report_endpoints()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/report-users')
            ->assertStatus(400); // 400 'No reports found' when database empty, showing access granted

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/report-properties')
            ->assertStatus(400); // 400 'No reports found' when database empty, showing access granted
    }
}
