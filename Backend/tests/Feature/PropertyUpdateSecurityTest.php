<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PropertyUpdateSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_property_updates()
    {
        $response = $this->getJson('/api/property-updates');
        $response->assertStatus(401);

        $responseNew = $this->getJson('/api/property-updates/1/new');
        $responseNew->assertStatus(401);

        $responseOld = $this->getJson('/api/property-updates/1/old');
        $responseOld->assertStatus(401);

        $responseApprove = $this->postJson('/api/property-updates/accepted/1');
        $responseApprove->assertStatus(401);

        $responseReject = $this->postJson('/api/property-updates/rejected/1');
        $responseReject->assertStatus(401);
    }

    public function test_regular_user_cannot_access_property_updates()
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/property-updates');
        $response->assertStatus(403);

        $responseNew = $this->getJson('/api/property-updates/1/new');
        $responseNew->assertStatus(403);

        $responseOld = $this->getJson('/api/property-updates/1/old');
        $responseOld->assertStatus(403);

        $responseApprove = $this->postJson('/api/property-updates/accepted/1');
        $responseApprove->assertStatus(403);

        $responseReject = $this->postJson('/api/property-updates/rejected/1');
        $responseReject->assertStatus(403);
    }

    public function test_admin_user_can_access_property_updates_endpoints()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Sanctum::actingAs($admin);

        // Accessing index as admin should pass authorization middleware (will return 404 when no updates exist)
        $response = $this->getJson('/api/property-updates');
        $response->assertStatus(404);
        $response->assertJson(['message' => 'No pending property updates found.']);
    }
}
