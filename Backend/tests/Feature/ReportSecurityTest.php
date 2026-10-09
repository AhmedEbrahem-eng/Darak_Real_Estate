<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ReportUser;
use App\Models\ReportProperty;
use App\Models\Property;
use App\Models\Location;
use App\Models\PropertyType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_cannot_access_report_user_endpoints(): void
    {
        $response = $this->getJson('/api/report-users');
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/report-users/deleteReport/1');
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/report-users/deleteLandlord/1');
        $response->assertStatus(401);

        $response = $this->postJson('/api/report-users', []);
        $response->assertStatus(401);
    }

    public function test_non_admin_users_cannot_access_report_user_admin_endpoints(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/report-users');
        $response->assertStatus(403);

        $response = $this->deleteJson('/api/report-users/deleteReport/1');
        $response->assertStatus(403);

        $response = $this->deleteJson('/api/report-users/deleteLandlord/1');
        $response->assertStatus(403);
    }

    public function test_admin_users_can_access_report_user_admin_endpoints(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/report-users');
        // When empty, it returns 400 with 'No reports found' as defined in controller
        $response->assertStatus(400);
        $response->assertJson(['message' => 'No reports found']);
    }

    public function test_unauthenticated_users_cannot_access_report_property_endpoints(): void
    {
        $response = $this->getJson('/api/report-properties');
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/report-properties/deleteReport/1');
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/report-properties/deleteProperty/1');
        $response->assertStatus(401);

        $response = $this->postJson('/api/report-properties', []);
        $response->assertStatus(401);
    }

    public function test_non_admin_users_cannot_access_report_property_admin_endpoints(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/report-properties');
        $response->assertStatus(403);

        $response = $this->deleteJson('/api/report-properties/deleteReport/1');
        $response->assertStatus(403);

        $response = $this->deleteJson('/api/report-properties/deleteProperty/1');
        $response->assertStatus(403);
    }

    public function test_admin_users_can_access_report_property_admin_endpoints(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/report-properties');
        // When empty, it returns 400 with 'No reports found' as defined in controller
        $response->assertStatus(400);
        $response->assertJson(['message' => 'No reports found']);
    }
}
