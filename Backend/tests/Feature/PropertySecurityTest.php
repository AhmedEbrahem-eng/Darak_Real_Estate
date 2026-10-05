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

    public function test_unauthenticated_user_cannot_delete_property(): void
    {
        $property = Property::factory()->create();

        $response = $this->deleteJson('/api/properties/' . $property->id);

        $response->assertStatus(401);
        $this->assertDatabaseHas('properties', ['id' => $property->id, 'deleted_at' => null]);
    }

    public function test_non_owner_cannot_delete_property(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson('/api/properties/' . $property->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('properties', ['id' => $property->id, 'deleted_at' => null]);
    }

    public function test_property_owner_can_delete_property(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($owner);

        $response = $this->deleteJson('/api/properties/' . $property->id);

        $response->assertStatus(200);
        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }

    public function test_admin_can_delete_any_property(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $property = Property::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson('/api/properties/' . $property->id);

        $response->assertStatus(200);
        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }

    public function test_deleting_non_existent_property_returns_404(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/properties/99999');

        $response->assertStatus(404);
    }
}
