<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PropertySecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_delete_property()
    {
        $property = Property::factory()->create();

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(401);
    }

    public function test_authenticated_user_cannot_delete_other_users_property()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(403);
    }

    public function test_owner_can_delete_their_own_property()
    {
        $owner = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($owner);

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }

    public function test_admin_can_delete_any_property()
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $property = Property::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }
}
