<?php

namespace Tests\Feature;

use App\Models\ReasonReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReasonReportSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_create_reason_report(): void
    {
        $response = $this->postJson('/api/reason-report', [
            'type' => 'report-property',
            'reason' => 'Fraudulent listing',
        ]);

        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_create_reason_report(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/reason-report', [
            'type' => 'report-property',
            'reason' => 'Fraudulent listing',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_user_can_create_reason_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/reason-report', [
            'type' => 'report-property',
            'reason' => 'Fraudulent listing',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reason_reports', [
            'type' => 'report-property',
            'reason' => 'Fraudulent listing',
        ]);
    }

    public function test_unauthenticated_user_cannot_update_reason_report(): void
    {
        $reason = ReasonReport::create([
            'type' => 'report-property',
            'reason' => 'Initial reason',
        ]);

        $response = $this->putJson("/api/reason-report/{$reason->id}", [
            'type' => 'report-property',
            'reason' => 'Updated reason',
        ]);

        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_update_reason_report(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $reason = ReasonReport::create([
            'type' => 'report-property',
            'reason' => 'Initial reason',
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson("/api/reason-report/{$reason->id}", [
            'type' => 'report-property',
            'reason' => 'Updated reason',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_user_can_update_reason_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $reason = ReasonReport::create([
            'type' => 'report-property',
            'reason' => 'Initial reason',
        ]);

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/reason-report/{$reason->id}", [
            'type' => 'report-property',
            'reason' => 'Updated reason',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('reason_reports', [
            'id' => $reason->id,
            'reason' => 'Updated reason',
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_reason_report(): void
    {
        $reason = ReasonReport::create([
            'type' => 'report-property',
            'reason' => 'Initial reason',
        ]);

        $response = $this->deleteJson("/api/reason-report/{$reason->id}");

        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_delete_reason_report(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $reason = ReasonReport::create([
            'type' => 'report-property',
            'reason' => 'Initial reason',
        ]);

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/reason-report/{$reason->id}");

        $response->assertStatus(403);
    }

    public function test_admin_user_can_delete_reason_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $reason = ReasonReport::create([
            'type' => 'report-property',
            'reason' => 'Initial reason',
        ]);

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/reason-report/{$reason->id}");

        $response->assertStatus(201);
        $this->assertSoftDeleted('reason_reports', [
            'id' => $reason->id,
        ]);
    }
}
