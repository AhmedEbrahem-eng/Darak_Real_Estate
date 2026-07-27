<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ReasonReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test report-users administrative endpoints are protected by admin middleware.
     */
    public function test_report_users_admin_endpoints_require_admin(): void
    {
        // 1. Unauthenticated user
        $this->getJson('/api/report-users')->assertStatus(401);
        $this->deleteJson('/api/report-users/deleteReport/1')->assertStatus(401);
        $this->deleteJson('/api/report-users/deleteLandlord/1')->assertStatus(401);

        // 2. Regular authenticated user
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $this->getJson('/api/report-users')->assertStatus(403);
        $this->deleteJson('/api/report-users/deleteReport/1')->assertStatus(403);
        $this->deleteJson('/api/report-users/deleteLandlord/1')->assertStatus(403);

        // 3. Admin user
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        // Should not be 401 or 403. Depending on DB seeding/existence, we expect 400 (if no reports found/invalid ID) or 200, but NOT 403 or 401.
        $this->getJson('/api/report-users')->assertStatus(400); // "No reports found" response
    }

    /**
     * Test report-properties administrative endpoints are protected by admin middleware.
     */
    public function test_report_properties_admin_endpoints_require_admin(): void
    {
        // 1. Unauthenticated user
        $this->getJson('/api/report-properties')->assertStatus(401);
        $this->deleteJson('/api/report-properties/deleteReport/1')->assertStatus(401);
        $this->deleteJson('/api/report-properties/deleteProperty/1')->assertStatus(401);

        // 2. Regular authenticated user
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $this->getJson('/api/report-properties')->assertStatus(403);
        $this->deleteJson('/api/report-properties/deleteReport/1')->assertStatus(403);
        $this->deleteJson('/api/report-properties/deleteProperty/1')->assertStatus(403);

        // 3. Admin user
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        // Should not be 401 or 403.
        $this->getJson('/api/report-properties')->assertStatus(400); // "No reports found" response
    }

    /**
     * Test reason-report administrative endpoints are protected by admin middleware.
     */
    public function test_reason_report_admin_endpoints_require_admin(): void
    {
        // 1. Unauthenticated user
        $this->postJson('/api/reason-report', [])->assertStatus(401);
        $this->putJson('/api/reason-report/1', [])->assertStatus(401);
        $this->deleteJson('/api/reason-report/1')->assertStatus(401);

        // 2. Regular authenticated user
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $this->postJson('/api/reason-report', [])->assertStatus(403);
        $this->putJson('/api/reason-report/1', [])->assertStatus(403);
        $this->deleteJson('/api/reason-report/1')->assertStatus(403);

        // 3. Admin user (unauthorized/invalid data)
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        // When sending valid admin request with invalid body, should trigger validation error (422) instead of auth/admin error (401/403)
        $this->postJson('/api/reason-report', [])->assertStatus(422);
    }

    /**
     * Test public GET endpoints for reason-report do not require authentication or admin.
     */
    public function test_reason_report_public_endpoints(): void
    {
        // Unauthenticated request should be successful or handled without 401/403
        $this->getJson('/api/reason-report')->assertStatus(201); // Reasons empty collection or list
        $this->getJson('/api/reason-report/property')->assertStatus(201); // Reasons empty collection or list
        $this->getJson('/api/reason-report/landlord')->assertStatus(201); // Reasons empty collection or list
    }
}
