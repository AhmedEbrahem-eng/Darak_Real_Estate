<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportSecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthorized_users_cannot_access_report_users_index()
    {
        $response = $this->getJson('/api/report-users');
        $response->assertStatus(401);
    }

    /** @test */
    public function non_admin_users_cannot_access_report_users_index()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/report-users');
        $response->assertStatus(403);
    }

    /** @test */
    public function unauthorized_users_cannot_access_report_properties_index()
    {
        $response = $this->getJson('/api/report-properties');
        $response->assertStatus(401);
    }

    /** @test */
    public function non_admin_users_cannot_access_report_properties_index()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/report-properties');
        $response->assertStatus(403);
    }

    /** @test */
    public function unauthorized_users_cannot_access_reason_report_index()
    {
        $response = $this->getJson('/api/reason-report');
        $response->assertStatus(401);
    }

    /** @test */
    public function non_admin_users_cannot_access_reason_report_index()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/reason-report');
        $response->assertStatus(403);
    }

    /** @test */
    public function non_admin_users_can_access_reason_report_property()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/reason-report/property');
        // Expecting 201 based on controller implementation (which is weird but that's how it's currently coded)
        // or 400 if no reasons found
        $this->assertContains($response->status(), [201, 400]);
    }

    /** @test */
    public function non_admin_users_can_access_reason_report_landlord()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/reason-report/landlord');
        $this->assertContains($response->status(), [201, 400]);
    }

    /** @test */
    public function admin_users_can_access_report_users_index()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson('/api/report-users');
        // Expecting 200 or 400 (if no reports)
        $this->assertContains($response->status(), [200, 400]);
    }
}
