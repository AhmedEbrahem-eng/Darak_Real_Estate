<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Property;
use App\Models\ReportUser;
use App\Models\ReportProperty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_cannot_access_report_endpoints()
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

    public function test_non_admin_users_cannot_perform_admin_report_actions()
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

    public function test_admin_users_can_access_administrative_report_endpoints()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/report-users')
            ->assertStatus(400); // 400 when empty collection per ReportUserController logic

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/report-properties')
            ->assertStatus(400); // 400 when empty collection per ReportPropertyController logic
    }
}
