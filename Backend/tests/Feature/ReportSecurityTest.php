<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_report_users_index(): void
    {
        $response = $this->getJson('/api/report-users');
        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_access_report_users_index(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/report-users');
        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_report_users_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/report-users');
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_unauthenticated_user_cannot_delete_report_user(): void
    {
        $response = $this->deleteJson('/api/report-users/deleteReport/1');
        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_delete_report_user(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/report-users/deleteReport/1');
        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_report_properties_index(): void
    {
        $response = $this->getJson('/api/report-properties');
        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_access_report_properties_index(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/report-properties');
        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_report_properties_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/report-properties');
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_unauthenticated_user_cannot_store_report_user(): void
    {
        $response = $this->postJson('/api/report-users', []);
        $response->assertStatus(401);
    }
}
