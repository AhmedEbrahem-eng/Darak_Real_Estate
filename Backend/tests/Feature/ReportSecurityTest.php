<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_cannot_access_report_endpoints(): void
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

    public function test_regular_users_cannot_access_admin_report_endpoints(): void
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

    public function test_admin_users_can_access_admin_report_endpoints(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/report-users');

        $this->assertNotEquals(401, $response->getStatusCode());
        $this->assertNotEquals(403, $response->getStatusCode());

        $responseProp = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/report-properties');

        $this->assertNotEquals(401, $responseProp->getStatusCode());
        $this->assertNotEquals(403, $responseProp->getStatusCode());
    }
}
