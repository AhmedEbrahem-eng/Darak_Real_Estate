<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/report-users');

        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_report_users_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/report-users');

        // Returns 400 when no reports are found in DB, but passed security checks (not 401 or 403)
        $this->assertContains($response->status(), [200, 400]);
    }

    public function test_unauthenticated_user_cannot_access_report_properties_index(): void
    {
        $response = $this->getJson('/api/report-properties');
        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_access_report_properties_index(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/report-properties');

        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_report_properties_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/report-properties');

        $this->assertContains($response->status(), [200, 400]);
    }

    public function test_unauthenticated_user_cannot_store_report_user(): void
    {
        $response = $this->postJson('/api/report-users', [
            'reason' => 'Spam',
            'reported_user_id' => 1,
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_store_report_property(): void
    {
        $response = $this->postJson('/api/report-properties', [
            'reason' => 'Fake listing',
            'property_id' => 1,
        ]);

        $response->assertStatus(401);
    }
}
