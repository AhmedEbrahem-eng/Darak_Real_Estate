<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertySecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_delete_property(): void
    {
        $user = User::factory()->create();
        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create();
        $property = Property::factory()->create([
            'user_id' => $user->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(401);
        $this->assertDatabaseHas('properties', ['id' => $property->id]);
    }

    public function test_non_owner_and_non_admin_cannot_delete_property(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create();
        $property = Property::factory()->create([
            'user_id' => $owner->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('properties', ['id' => $property->id]);
    }

    public function test_property_owner_can_delete_their_property(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create();
        $property = Property::factory()->create([
            'user_id' => $owner->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);

        $response = $this->actingAs($owner, 'sanctum')
            ->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }

    public function test_admin_can_delete_any_property(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create();
        $property = Property::factory()->create([
            'user_id' => $owner->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }

    public function test_delete_non_existent_property_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/properties/99999');

        $response->assertStatus(404);
    }
}
