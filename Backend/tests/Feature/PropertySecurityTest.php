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

    private function createPropertyForUser($user)
    {
        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create();

        return Property::create([
            'title' => 'Sample Property Title',
            'slug' => 'sample-property-title-' . rand(1000, 9999),
            'description' => 'A detailed description of the property.',
            'price' => 1500,
            'area' => 120,
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'listing_type' => 'rent',
            'availability' => 'available',
            'user_id' => $user->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
            'status' => 'accepted'
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_property(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $property = $this->createPropertyForUser($owner);

        $response = $this->deleteJson('/api/properties/' . $property->id);

        $response->assertStatus(401);
        $this->assertDatabaseHas('properties', ['id' => $property->id]);
    }

    public function test_non_owner_user_cannot_delete_property(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $property = $this->createPropertyForUser($owner);

        $response = $this->actingAs($otherUser)->deleteJson('/api/properties/' . $property->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('properties', ['id' => $property->id]);
    }

    public function test_deleting_non_existent_property_returns_404(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->deleteJson('/api/properties/999999');

        $response->assertStatus(404);
    }

    public function test_property_owner_can_delete_property(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $property = $this->createPropertyForUser($owner);

        $response = $this->actingAs($owner)->deleteJson('/api/properties/' . $property->id);

        $response->assertStatus(200);
        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }

    public function test_admin_can_delete_any_property(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $property = $this->createPropertyForUser($owner);

        $response = $this->actingAs($admin)->deleteJson('/api/properties/' . $property->id);

        $response->assertStatus(200);
        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }
}
