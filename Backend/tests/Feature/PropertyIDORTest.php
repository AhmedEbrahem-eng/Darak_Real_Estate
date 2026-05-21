<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class PropertyIDORTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_delete_property()
    {
        $property = Property::factory()->create();

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(401);
    }

    public function test_user_cannot_delete_others_property()
    {
        // Create two users
        $user1 = User::factory()->create(['role' => 'landlord']);
        $user2 = User::factory()->create(['role' => 'landlord']);

        // Create a property for user 1
        $property = Property::factory()->create(['user_id' => $user1->id]);

        // Authenticate as user 2
        Sanctum::actingAs($user2);

        // Try to delete user 1's property
        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('properties', ['id' => $property->id, 'deleted_at' => null]);
    }

    public function test_owner_can_delete_their_property()
    {
        $user = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }

    public function test_admin_can_delete_any_property()
    {
        $user = User::factory()->create(['role' => 'landlord']);
        $admin = User::factory()->create(['role' => 'admin']);
        $property = Property::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }
}
